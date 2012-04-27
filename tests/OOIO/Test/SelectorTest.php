<?php

namespace OOIO\Test;

use OOIO\IO,
    OOIO\Selector;

class SelectorTest extends \PHPUnit_Framework_TestCase
{
    function testSelect()
    {
        $pipe = IO::pipe();
        $selector = new Selector;
        $selector->register($pipe[1], 'r');

        $pipe[0]->puts("Hello World");

        list($r) = $selector->select(0);

        $this->assertEquals(1, count($r));
    }
}
