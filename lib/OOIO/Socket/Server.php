<?php

namespace OOIO\Socket;

use OOIO\Stream;

class Server implements FileDescriptor
{
    protected
        $socket;

    function __construct($spec)
    {
        $socket = stream_socket_server($spec, $errorCode, $errorMsg);

        if (!$socket) {
            # Raise error.
        }

        $this->socket = $socket;
    }

    function getFileDescriptor()
    {
        return $this->socket;
    }

    function accept($timeout = null)
    {
        if (null === $timeout) $timeout = ini_get("default_socket_timeout");

        $client = stream_socket_accept($this->socket, $timeout);
        return new Stream($client);
    }
}
