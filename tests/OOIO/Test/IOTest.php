<?php

namespace OOIO\Test;

use OOIO\IO;

class IOTest extends \PHPUnit_Framework_TestCase
{
    function testStdinReturnsStream()
    {
        $this->assertInstanceOf("\\OOIO\Stream", IO::stdin());
    }

    function testStdoutReturnsStream()
    {
        $this->assertInstanceOf("\\OOIO\Stream", IO::stdout());
    }

    function testStderrReturnsStream()
    {
        $this->assertInstanceOf("\\OOIO\Stream", IO::stderr());
    }

    function testSelectReturnsSelector()
    {
        $this->assertInstanceOf("\\OOIO\\Selector", IO::select());
    }
}
