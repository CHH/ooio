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
}
