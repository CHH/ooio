<?php

namespace OOIO;

interface Reader
{
    function read($length = null);
}

interface Writer
{
    function write($data, $length = null);
}

interface Seekable
{
    function seek($offset, $whence = SEEK_SET);
}

interface Rewindable
{
    function rewind();
}

interface Closeable
{
    function close();
    function isClosed();
}

interface FileDescriptor
{
    # Returns the wrapped system file descriptor for the stream.
    function getFileDescriptor();
}
