<?php

namespace OOIO\Test;

use OOIO;

class StreamContextTest extends \PHPUnit_Framework_TestCase
{
    protected
        $context,
        $filename;

    function setup()
    {
        $this->filename = tempnam(sys_get_temp_dir(), __CLASS__);
        $this->context = new OOIO\StreamContext;
    }

    function testWrite()
    {
        $this->context->write($this->filename, "Foo Bar");

        $this->assertEquals("Foo Bar", $this->context->read($this->filename));
    }

    function testAppend()
    {
        $this->context->write($this->filename, "Foo\n");
        $this->context->append($this->filename, "Bar\n");

        $this->assertEquals("Foo\nBar\n", $this->context->read($this->filename));
    }

    function testOpenReturnsStream()
    {
        $s = $this->context->open($this->filename, 'rb');
        $this->assertInstanceOf("\\OOIO\\Stream", $s);
    }

    function testOpenPassesStreamToClosure()
    {
        $this->context->write($this->filename, "Foo\nBar\n");

        $ret = $this->context->open($this->filename, 'rb', function($file) {
            return $file->gets();
        });

        $this->assertEquals("Foo\n", $ret);
    }

    function tearDown()
    {
        unlink($this->filename);
    }
}
