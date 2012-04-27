<?php

namespace OOIO;

class Stat extends \Jack\Struct
{
    public
        $dev,
        $ino,
        $mode,
        $nlink,
        $uid,
        $gid,
        $rdev,
        $size,
        $atime,
        $mtime,
        $ctime,
        $blksize,
        $blocks;

    function __construct($stats)
    {
        parent::__construct($stats, true);
    }
}
