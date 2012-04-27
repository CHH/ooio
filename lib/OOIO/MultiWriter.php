<?php

namespace OOIO;

class MultiWriter implements Writer
{
    protected
        $childs = array();

    function __construct($childs = array())
    {
        $this->childs = $childs;
    }

    function write($data, $length = null)
    {
        foreach ($this->childs as $child) {
            $child->write($data, $length);
        }
    }
}
