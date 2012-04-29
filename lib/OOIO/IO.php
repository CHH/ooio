<?php

namespace OOIO;

class IO
{
    # Returns the default stream context.
    static function getDefaultContext()
    {
        static $context;
        return $context ?: $context = new StreamContext(stream_context_get_default());
    }

    # Opens the given filename (URI) with the mode and
    # returns a new Stream instance.
    #
    # filename
    # mode
    #
    # Returns a Stream.
    static function open($filename, $mode = "", $callback = null)
    {
        return static::getDefaultContext()->open($filename, $mode, $callback);
    }

    # Read the contents of the file into a String (binary safe). See
    # StreamContext::read().
    static function read($filename, $maxLength = -1, $offset = -1)
    {
        return static::getDefaultContext()->read($filename, $maxLength, $offset);
    }

    # Writes the data to the filename. See StreamContext::write().
    static function write($filename, $data, $flags = 0)
    {
        return static::getDefaultContext()->write($filename, $data, $flags);
    }

    # Appends the content to the filename
    #
    # filename - Path to the file.
    # data     - Data as String.
    #
    # Returns Number of Bytes written.
    static function append($filename, $data)
    {
        return static::getDefaultContext()->append($filename, $data);
    }

    # Creates a temporary file and returns a Stream instance.
    #
    # Returns a new Stream.
    static function tempfile()
    {
        return new Stream(tmpfile());
    }

    # Returns the script's Error Stream.
    static function stderr()
    {
        static $stderr;
        return $stderr ?: $stderr = new Stream(STDERR);
    }

    # Returns the script's Standard Output Stream.
    static function stdout()
    {
        static $stdout;
        return $stdout ?: $stdout = new Stream(STDOUT);
    }

    # Returns the script's Input Stream.
    static function stdin()
    {
        static $stdin;
        return $stdin ?: $stdin = new Stream(STDIN);
    }

    # Returns information about the filename.
    #
    # filename - Filename as String.
    #
    # Returns a Stat instance containing information about the filename.
    static function stat($filename)
    {
        $stats = \stat($filename);
        return new Stat($stats);
    }

    # Returns a pair of interconnected UNIX sockets.
    static function pipe()
    {
        return Socket::pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    }

    # Factory for Selectors.
    #
    # Returns a new Selector.
    static function select()
    {
        return new Selector;
    }
}
