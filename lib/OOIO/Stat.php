<?php

namespace OOIO;

class Stat
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

    function __construct($stream)
    {
        if (is_string($stream)) {
            if (!realpath($stream)) {
                throw new FileNotFoundException("File '$stream' not found.");
            }

            $this->load(\stat($stream));

        } else if (is_resource($stream)) {
            $this->load(\fstat($stream));
        } else if ($stream instanceof FileDescriptor) {
            $this->load(\fstat($stream->toFileDescriptor()));
        } else if (is_array($stream)) {
            $this->load($stream);
        } else {
            throw new \InvalidArgumentException(sprintf(
                "Constructor expects either a file name or a resource."
            ));
        }
    }

    protected function load($stat)
    {
        foreach ($stat as $key => $val) {
            $this->{$key} = $val;
        }
    }
}
