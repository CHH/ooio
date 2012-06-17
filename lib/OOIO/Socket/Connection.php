<?php

namespace OOIO\Socket;

use OOIO\Stream;

class Connection extends Stream
{
    protected $peer;

    function __construct($stream, $peer)
    {
        parent::__construct($stream);
        $this->peer = $peer;
    }

    # Returns the Remote Peer's IP Address or False if the peer has already closed
    # its connection.
    function getPeer()
    {
        return $this->peer;
    }

    # Checks if the peer has disconnected.
    function isDisconnected()
    {
        return false === @stream_socket_get_name($this->stream, true);
    }
}
