<?php

namespace OOIO;

use InvalidArgumentException,
    Evenement\EventEmitter;

class Stream
    implements Reader, Writer, Closeable, Seekable, Rewindable, FileDescriptor
{
    protected
        $stream,
        $closed = false;

    # Initializes the stream with a resource created by one of
    # PHP's stream functions.
    #
    # stream - Stream resource.
    function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException(
                "Constructor expects a valid resource as first argument."
            );
        }

        $this->stream = $stream;
    }

    function getFileDescriptor()
    {
        return $this->stream;
    }

    # Writes the string to the stream.
    #
    # data
    # length
    #
    # Returns the number of bytes written, or False on error.
    function write($data, $length = null)
    {
        $this->assertNotClosed();

        if ($length === null) {
            $bytes = fwrite($this->stream, (string) $data);
        } else {
            $bytes = fwrite($this->stream, (string) $data, $length);
        }

        if (false === $bytes) {
            throw new WriteException(printf("Failed writing %d bytes.", $length ?: strlen($data)));
        }

        return $bytes;
    }

    # Writes the string to the stream, delimited by a line separator.
    #
    # data      - Data as String.
    # separator - Separator which should get appended to data, defaults to PHP_EOL.
    #
    # Returns the number of bytes written or False on error.
    function puts($data, $separator = PHP_EOL)
    {
        $this->assertNotClosed();
        return $this->write($data . $separator);
    }

    # Writes a formatted string to the stream.
    #
    # format - Format String, see the printf() function.
    # args   - Array of variables.
    #
    # Returns the length of the content.
    function printf($format, $args = array())
    {
        return vfprintf($this->stream, $format, $args);
    }

    # Parses input from the stream according to the format.
    #
    # format - String of format specifiers, see the printf() function.
    #
    # Returns the parsed variables as Array.
    function scanf($format)
    {
        return fscanf($this->stream, $format);
    }

    # Reads from the stream.
    #
    # length - Optional, reads everything up to EOF when Null,
    #          reads {n} bytes when an positive integer.
    #
    # Returns a String or False on error.
    function read($length = null)
    {
        $this->assertNotClosed();

        if ($length === null) {
            return stream_get_contents($this->stream);
        }
        return fread($this->stream, $length);
    }

    # Reads from the stream until a separator (newline) occurs.
    #
    # length - Optional, number of max bytes to read.
    #
    # Returns a String or False on error.
    function gets($length = null)
    {
        $this->assertNotClosed();
        return $length === null ? fgets($this->stream) : fgets($this->stream, $length);
    }

    # Checks if two stream instances are equal by comparing the
    # wrapped file descriptors.
    #
    # Returns True or False.
    function equalTo($stream)
    {
        return $this->stream === $stream->stream;
    }

    function isEof()
    {
        $this->assertNotClosed();
        return feof($this->stream);
    }

    # Seeks to the given offset within the stream.
    #
    # offset - Offset in Bytes.
    # whence - How the offset is handled:
    #          SEEK_SET (default):
    #            Set position equal to `offset` bytes.
    #          SEEK_CUR:
    #            Set the position to the current location plus `offset`.
    #          SEEK_END:
    #            Set position to End-of-file plus `offset`.
    #
    # Returns 0 on success, -1 on failure.
    function seek($offset, $whence = SEEK_SET)
    {
        $this->assertNotClosed();
        return fseek($this->stream, $offset, $whence);
    }

    function setBlocking($mode = true)
    {
        stream_set_blocking($this->stream, $mode);
    }

    function rewind()
    {
        $this->assertNotClosed();
        return rewind($this->stream);
    }

    # Flushs the write buffer.
    #
    # Returns a Bool.
    function flush()
    {
        $this->assertNotClosed();
        return fflush($this->stream);
    }

    # Get information about the stream.
    #
    # Returns an instance of Jack\IO\Stat.
    function stat()
    {
        $stats = fstat($this->stream);
        return new Stat($stats);
    }

    # Closes the stream.
    #
    # Returns Nothing.
    function close()
    {
        if (fclose($this->stream)) {
            $this->closed = true;
        }
    }

    # Checks if the closed flag is set.
    #
    # Returns True when the Stream is already closed, False otherwise.
    function isClosed()
    {
        return $this->closed;
    }

    # Checks if the file descriptor is a Terminal Type Device. Needs the "posix" extension.
    #
    # Returns True or False.
    function isTty()
    {
        return posix_isatty($this->stream);
    }

    # Throws an ClosedException when the Stream was closed
    # by `close()`.
    #
    # Returns Nothing.
    protected function assertNotClosed()
    {
        if ($this->closed) throw new ClosedException("Stream is already closed.");
    }
}
