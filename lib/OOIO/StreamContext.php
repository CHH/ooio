<?php

namespace OOIO;

class StreamContext
{
    protected
        # Context Resource created with `stream_context_create()`.
        $context;

    # Initialized the instance with a stream context resource.
    #
    # context - Stream context resource, created by `stream_context_create`.
    function __construct($context = null)
    {
        if (null === $context) {
            $context = stream_context_create();
        }
        $this->context = $context;
    }

    function setOptions(array $options)
    {
        stream_context_set_option($this->context, $options);
    }

    function setParams(array $params)
    {
        stream_context_set_params($this->context, $params);
    }

    # Reads content from the filename.
    #
    # filename  - String.
    # maxLength - Number of bytes to read.
    # offset    - Where to start.
    #
    # Returns the content as String.
    function read($filename, $maxLength = null, $offset = -1)
    {
        if ($maxLength === null) {
            $data = file_get_contents($filename, false, $this->context, $offset);
        } else {
            $data = file_get_contents($filename, false, $this->context, $offset, $maxLength);
        }

        return $data;
    }

    # Replaces the file's contents with the provided data.
    #
    # filename - File Name as String.
    # data     - String, the new content for the file.
    # flags    - Flags:
    #            FILE_USE_INCLUDE_PATH: Search for the filename in the 
    #            include path.
    #            FILE_APPEND: If the filename already exists, append the 
    #            data to the file.
    #            LOCK_EX: Aquire an exclusive lock.
    #
    # Returns Number of Bytes written or False.
    function write($filename, $data, $flags = 0)
    {
        return file_put_contents($filename, $data, $flags, $this->context);
    }

    # Appends the data to the file's contents.
    #
    # filename - File Name as String.
    # data     - String, gets written to the file.
    #
    # Returns Number of Bytes written or False.
    function append($filename, $data)
    {
        return $this->write($filename, $data, FILE_APPEND);
    }

    function open($filename, $mode = "", $callback = null)
    {
        $res    = fopen($filename, $mode, false, $this->context);
        $stream = new Stream($res);

        if (is_callable($callback)) {
            $result = call_user_func($callback, $stream);
            $stream->close();

            return $result;
        }

        return $stream;
    }
}
