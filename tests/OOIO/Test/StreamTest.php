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

    function testIsTty()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            return $this->markTestSkipped();
        }

        $stdin = new Stream(STDIN);
        $this->assertTrue($stdin->isTty());

        $f = new Stream(tmpfile());
        $this->assertFalse($f->isTty());
    }
}
