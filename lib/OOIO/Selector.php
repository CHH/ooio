<?php

namespace OOIO;

class Selector
{
    const WATCH_READ = 'r';
    const WATCH_WRITE = 'w';
    const WATCH_EXCEPT = 'e';

    # Use this timeout value to select without timeout.
    const TV_NOWAIT = 0;

    protected
        # List of streams to watch for becoming readable.
        $read = array(),

        # List of strreams to watch for becoming writable.
        $write = array(),

        # List of streams to watch for errors.
        $except = array(),

        # Maps FDs to stream instances.
        $streams = array();

    # Checks which registered streams are ready.
    #
    # timeout - Timeout in seconds, returns immediately when given '0'.
    #           Waits until one registered stream becomes ready when
    #           Null is given as timeout.
    #
    # Todo
    #
    #   - More efficient mapping of file descriptors to stream instances.
    #
    # Returns the readable, writeable and error'd streams as three lists.
    function select($timeout = null)
    {
        $read = $this->read;
        $write = $this->write;
        $except = $this->except;

        if ($timeout !== null) {
            # Convert timeout to microseconds.
            $timeout *= 1e6;
        }

        $readyCount = stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);

        # Response is a list of read, write, except lists of streams (in that order).
        $resp = array(array(), array(), array());

        if ($readyCount === 0) {
            return $resp;
        }

        foreach ($read as $fd) {
            $resp[0][] = $this->streams[(int) $fd];
        }

        foreach ($write as $fd) {
            $resp[1][] = $this->streams[(int) $fd];
        }

        foreach ($except as $fd) {
            $resp[2][] = $this->streams[(int) $fd];
        }

        return $resp;
    }

    # Public: Registers the stream.
    #
    # stream - Stream instance.
    # modes  - A string of mode(s). Valid modes include:
    #            r: Monitor for data available.
    #            w: Monitor for data writeable.
    #            e: Monitor for errors.
    #
    # Examples
    #
    #   $s = IO::select();
    #   $p = IO::pipe();
    #
    #   $s->register($p[1], 'r');
    #
    #   $p[0]->puts("Hello World!");
    #
    #   list($r) = $s->select(0);
    #
    #   echo count($r);
    #   # Output:
    #   # 1
    #
    function register(FileDescriptor $stream, $modes)
    {
        $fd = $stream->getFileDescriptor();

        foreach (str_split($modes) as $mode) {
            switch ($mode) {
            case self::WATCH_READ:
                $this->read[] = $fd;
                break;
            case self::WATCH_WRITE:
                $this->write[] = $fd;
                break;
            case self::WATCH_EXCEPT:
                $this->except[] = $fd;
                break;
            default:
                throw new \InvalidArgumentException("Invalid Mode '$mode'.");
            }
        }

        # Store the stream instance, so we can return it in select()
        # instead of the FD.
        $this->streams[(int) $fd] = $stream;

        return $this;
    }

    # Public: Unregister the stream instance.
    #
    # stream - Stream to unregister.
    #
    # Returns Nothing.
    function unregister(FileDescriptor $stream)
    {
        $fd = $stream->getFileDescriptor();

        unset($this->read[$fd]);
        unset($this->write[$fd]);
        unset($this->except[$fd]);
    }
}
