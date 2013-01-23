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

    /**
     * Initializes the stream with a resource created by one of
     * PHP's stream functions.
     *
     * @param $stream Stream resource.
     */
    function __construct($stream)
    {
        if ($stream instanceof FileDescriptor) $stream = $stream->toFileDescriptor();
        
        if (!is_resource($stream)) {
            throw new InvalidArgumentException(
                "Constructor expects a valid resource as first argument."
            );
        }

        $this->stream = $stream;
    }

    /** 
     * Ensures the Stream wrapper on the object/descriptor
     * @return Stream
     */
    public static function ensure($stream) 
    {
        if ($stream instanceof Stream) return $stream;
        return new Stream($stream);
    }    
    
    function toFileDescriptor()
    {
        return $this->stream;
    }

    /**
     * Copies the contents of this stream over to the
     * other stream.
     *
     * @param $destination Copy destination object implementing the FileDescriptor interface.
     *
     * @return the total count of bytes copied.
     */
    function copy(FileDescriptor $destination, $maxLength = -1, $offset = 0)
    {
        if ($maxLength === null) $maxLength = -1;
        return stream_copy_to_stream($this->stream, $destination->toFileDescriptor(), $maxLength, $offset);
    }
    
    function copyFrom(FileDescriptor $source, $maxLength = -1, $offset = 0)
    {
        if ($maxLength === null) $maxLength = -1;
        return stream_copy_to_stream($source->toFileDescriptor(), $this->stream, $maxLength, $offset);
    }    

    /**
     * Writes the bytes to the stream.
     *
     * @param $dataa Bytes to write.
     * @param $length Length to write (default: all).
     *
     * @return the number of bytes written, or False on error.
     */
    function write($data, $length = null)
    {
        $this->assertNotClosed();

        if ($data instanceof FileDescriptor) return $this->copyFrom($data, $length);
        if ($length < 0) $length = null;
        
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

    /**
     * Writes the string to the stream, delimited by a line separator.
     *
     * @param $data Data as String.
     * @param $separator Separator which should get appended to data (default: PHP_EOL).
     *
     * @return the number of bytes written or False on error.
     */
    function puts($data, $separator = PHP_EOL)
    {
        $this->assertNotClosed();
        return $this->write($data . $separator);
    }

    /**
     * Writes a formatted string to the stream.
     *
     * @param $format Format String, see the printf() function.
     * @param $args Array of variables.
     *
     * @return the length of the content.
     */
    function printf($format, $args = array())
    {
        return vfprintf($this->stream, $format, $args);
    }

    /**
     * Parses input from the stream according to the format.
     *
     * @param $format String of format specifiers, see the printf() function.
     *
     * @return array the parsed variables as Array.
     */
    function scanf($format)
    {
        return fscanf($this->stream, $format);
    }

    /**
     * Reads from the stream.
     *
     * @param $length Optional, reads everything up to EOF when Null,
     *          reads {n} bytes when an positive integer.
     *
     * @return a String or False on error.
     */
    function read($length = null)
    {
        $this->assertNotClosed();

        if ($length === null || $length < 0) {
            return stream_get_contents($this->stream);
        }
        return fread($this->stream, $length);
    }

    /** Reads from the stream until a separator (newline) occurs.
     * @param $length - Optional, number of max bytes to read.
     * 
     * @return string a String or False on error.
     */
    function gets($length = null)
    {
        $this->assertNotClosed();
        return $length === null ? fgets($this->stream) : fgets($this->stream, $length);
    }

    /**
     * Checks if two stream instances are equal by comparing the
     * wrapped file descriptors.
     *
     * @return True or False.
     */
    function equalTo($stream)
    {
        return $this->stream === $stream->stream;
    }

    function isEof()
    {
        $this->assertNotClosed();
        return feof($this->stream);
    }

    /**
     * Seeks to the given offset within the stream.
     *
     * @param $offset Offset in Bytes.
     * @param $whence How the offset is handled:
     *          SEEK_SET (default):
     *            Set position equal to `offset` bytes.
     *          SEEK_CUR:
     *            Set the position to the current location plus `offset`.
     *          SEEK_END:
     *            Set position to End-of-file plus `offset`.
     *
     * @return 0 on success, -1 on failure.
     */
    function seek($offset, $whence = SEEK_SET)
    {
        $this->assertNotClosed();
        return fseek($this->stream, $offset, $whence);
    }

    function rewind()
    {
        $this->assertNotClosed();
        return rewind($this->stream);
    }

    public function tell() 
    {
        $this->assertNotClosed();
        return ftell($this->stream);
    }
    
    /**
     * Flushs the write buffer.
     *
     * @return a Bool.
     */
    function flush()
    {
        $this->assertNotClosed();
        return fflush($this->stream);
    }

    /**
     * Get information about the stream.
     *
     * @return Stat an instance of Jack\IO\Stat.
     */
    function stat()
    {
        $stats = fstat($this->stream);
        return new Stat($stats);
    }

    /**
     * Closes the stream.
     *
     */
    function close()
    {
        if (fclose($this->stream)) {
            $this->closed = true;
        }
    }

    /**
     * Checks if the closed flag is set.
     *
     * @return True when the Stream is already closed, False otherwise.
     */
    function isClosed()
    {
        return $this->closed;
    }

    /**
     * Checks if the file descriptor is a Terminal Type Device. Needs the "posix" extension.
     *
     * @return True or False.
     */
    function isTty()
    {
        return posix_isatty($this->stream);
    }

    function setBlocking($mode = true)
    {
        stream_set_blocking($this->stream, (int) $mode);
    }

    /**
     * Throws an ClosedException when the Stream was closed
     * by `close()`.
     *
     */
    protected function assertNotClosed()
    {
        if ($this->closed) throw new ClosedException("Stream is already closed.");
    }
    
    
    public function filterAdd($append, $filterName, $readWrite = null, $params = null) 
    {
        $this->assertNotClosed();
        if ($append) {
            return stream_filter_append($this->stream, $filterName, $readWrite, $params);
        } else {
            return stream_filter_prepend($this->stream, $filterName, $readWrite, $params);
        }
    }

    public function filterAppend($filterName, $readWrite = null, $params = null) 
    {
        return $this->filterAdd(true, $filterName, $readWrite, $params);
    }    

    public function filterPrepend($filterName, $readWrite = null, $params = null) 
    {
        return $this->filterAdd(false, $filterName, $readWrite, $params);
    }    
}
