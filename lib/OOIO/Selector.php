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
        $except = array();

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
        $getFd = function($stream) { return $stream->getFileDescriptor(); };

        # Collect file descriptors for stream_select().
        $r = array_map($getFd, $this->read);
        $w = array_map($getFd, $this->write);
        $e = array_map($getFd, $this->except);

        if ($timeout !== null) {
            # Convert timeout to microseconds.
            $timeout *= 1e6;
        }

        $readyCount = stream_select($r, $w, $e, $timeout === null ? null : 0, $timeout);

        # Response is a list of read, write, except lists of streams (in that order).
        $resp = array(array(), array(), array());

        if ($readyCount === 0) {
            return $resp;
        }

        foreach ($r as $fd) {
            $resp[0][] = $this->read[(int) $fd];
        }

        foreach ($w as $fd) {
            $resp[1][] = $this->write[(int) $fd];
        }

        foreach ($e as $fd) {
            $resp[2][] = $this->except[(int) $fd];
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
                $this->read[(int) $fd] = $stream;
                break;
            case self::WATCH_WRITE:
                $this->write[(int) $fd] = $stream;
                break;
            case self::WATCH_EXCEPT:
                $this->except[(int) $fd] = $stream;
                break;
            default:
                throw new \InvalidArgumentException("Invalid Mode '$mode'.");
            }
        }

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
