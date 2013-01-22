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
    
    function testMemory()
    {
        $string = 'fooBAR';
        $stream = \OOIO\IO::memory(false, $string);
        
        $this->assertEquals($string, $stream->read());
        $this->assertEquals(strlen($string), $stream->stat()->size);
        $this->assertEquals(strlen($string), $stream->tell());
        
        $stream->seek(3);
        $this->assertEquals(substr($string, 3), $stream->read());
        
        $stream->seek(3);
        $stream2 = \OOIO\IO::memory(false, $stream);
        $this->assertEquals(substr($string, 3), $stream2->read());
    }
    
    function testCopy() 
    {
        $this->stream->write("Hello World");
        $this->stream->rewind();
        
        $stream2 = \OOIO\IO::memory();
        
        $this->stream->copy($stream2, 5);
        
        $stream2->rewind();
        $this->assertEquals('Hello', $stream2->read());
        
        $stream2->copyFrom($this->stream);
        
        $stream2->rewind();
        $this->assertEquals('Hello World', $stream2->read());
    }
    
}
