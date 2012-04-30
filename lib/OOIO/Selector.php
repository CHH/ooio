<?php

namespace OOIO;

# Public: Watches stream instances for stream events.
#
# Examples
#
#   <?php
#   use OOIO\Selector,
#       OOIO\IO;
#
#   $select = new Selector;
#   $select->register(IO::open("php://stdin"), 'r');
#
#   list($r) = $selector->select();
#
#   if ($r) {
#       echo "You entered ".$r[0]->gets()."\n";
#   }
#
class Selector
{
    const WATCH_READ = 'r';
    const WATCH_WRITE = 'w';
    const WATCH_EXCEPT = 'e';

    # Use this timeout value to return immediately from
    # the select() call, even when no stream is ready.
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
    # timeout - Timeout in microseconds.
    #
    # Returns the readable, writeable and error'd streams as three lists.
    function select($timeout = null)
    {
        $read = $this->read;
        $write = $this->write;
        $except = $this->except;

        # Response is a list of read, write, except lists of streams (in that order).
        $resp = array(array(), array(), array());

        @stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);

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
        $fd = $stream->toFileDescriptor();

        foreach (str_split((string) $modes) as $mode) {
            switch ($mode) {
            case self::WATCH_READ:
                $this->read[(int) $fd] = $fd;
                break;
            case self::WATCH_WRITE:
                $this->write[(int) $fd] = $fd;
                break;
            case self::WATCH_EXCEPT:
                $this->except[(int) $fd] = $fd;
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
        $fd = $stream->toFileDescriptor();

        unset($this->read[(int) $fd]);
        unset($this->write[(int) $fd]);
        unset($this->except[(int) $fd]);
        unset($this->streams[(int) $fd]);
    }
}
