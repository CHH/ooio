<?php

namespace OOIO\Test;

use OOIO\Stream;

class StreamTest extends \PHPUnit_Framework_TestCase
{
    protected
        $stream;

    function setUp()
    {
        $this->stream = new Stream(tmpfile());
    }

    function testGets()
    {
        $this->stream->write("Foo Bar\nBar Baz\n");
        $this->stream->rewind();

        $this->assertEquals("Foo Bar\n", $this->stream->gets());
    }

    /**
     * @expectedException \OOIO\ClosedException
     */
    function testWriteThrowsExceptionIfClosed()
    {
        $this->stream->close();
        $this->stream->write("Hello World");
    }

    function testReadWithoutArgumentsReadsToEOF()
    {
        $this->stream->write("Hello World");
        $this->stream->rewind();

        $this->assertEquals("Hello World", $this->stream->read());
    }
}
