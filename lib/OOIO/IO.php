<?php

namespace OOIO;

class IO
{
    /**
     * @return the default stream context.
     */
    static function getDefaultContext()
    {
        static $context;
        return $context ?: $context = new StreamContext(stream_context_get_default());
    }

    static function withContext($callable)
    {
        $context = new StreamContext;
        return call_user_func($context);
    }

    /** Opens the given filename (URI) with the mode and returns a new Stream instance.
     * @param type $filename
     * @param type $mode
     * @param type $callback
     * @return Stream
     */
    static function open($filename, $mode = "", $callback = null)
    {
        return static::getDefaultContext()->open($filename, $mode, $callback);
    }

    
    /**
     * Read the contents of the file into a String (binary safe). See
     * StreamContext::read().
     */
    static function read($filename, $maxLength = -1, $offset = -1)
    {
        return static::getDefaultContext()->read($filename, $maxLength, $offset);
    }

    /**
     * Writes the data to the filename. See StreamContext::write().
     */
    static function write($filename, $data, $flags = 0)
    {
        return static::getDefaultContext()->write($filename, $data, $flags);
    }

    /**
     * Appends the content to the filename
     *
     * @param $filename Path to the file.
     * @param $data Data as String.
     *
     * @return Number of Bytes written.
     */
    static function append($filename, $data)
    {
        return static::getDefaultContext()->append($filename, $data);
    }

    /**
     * Creates a temporary file and returns a Stream instance.
     *
     * @return Stream a new Stream.
     */
    static function tempfile()
    {
        return new Stream(tmpfile());
    }

    /**
     * @return Stream the script's Error Stream.
     */
    static function stderr()
    {
        static $stderr;
        return $stderr ?: $stderr = static::open("php://stderr");
    }

    /**
     * @return Stream the script's Standard Output Stream.
     */
    static function stdout()
    {
        static $stdout;
        return $stdout ?: $stdout = static::open("php://stdout");
    }

    /**
     * @return Stream the script's Input Stream.
     */
    static function stdin()
    {
        static $stdin;
        return $stdin ?: $stdin = static::open("php://stdin");
    }

    /**
     * Memory or temp backed stream
     * 
     * @param $useTemp - TRUE to use temp backed stream php://temp
     * @param $initWith - writes this string or stream to the newly created stream and rewinds it
     * 
     * @return Stream
     */
    public static function memory($useTemp = true, $initWith = null) {
        $stream = static::open($useTemp ? "php://temp" : "php://memory", "w+b");
        if ($initWith) {
            $stream->write($initWith);
            $stream->rewind();
        }
        return $stream;
    }

    /**
     * Returns information about the filename.
     *
     * @param $filename Filename as String.
     *
     * @return Stat a Stat instance containing information about the filename.
     */
    static function stat($filename)
    {
        $stats = \stat($filename);
        return new Stat($stats);
    }

    /**
     * @return a pair of interconnected UNIX sockets.
     */
    static function pipe()
    {
        return Socket::pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    }

    /**
     * Factory for Selectors.
     *
     * @return Selector a new Selector.
     */
    static function select()
    {
        return new Selector;
    }
}
