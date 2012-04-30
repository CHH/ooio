<?php

namespace OOIO\Socket;

use OOIO\Stream,
    OOIO\Exception;

class Server implements FileDescriptor
{
    protected
        $socket;

    function __construct($spec)
    {
        $socket = @stream_socket_server($spec, $errorCode, $errorMsg);

        if (!$socket) {
            throw new Exception("Could not bind to Socket '$spec'. $errorMsg");
        }

        $this->socket = $socket;
    }

    function toFileDescriptor()
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
