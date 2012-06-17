<?php

namespace OOIO\Socket;

use OOIO\Exception,
    OOIO\FileDescriptor;

class Listener implements FileDescriptor
{
    protected
        $socket;

    # Constructor
    #
    # spec - String in the format "<protocol>://<host>:<port>" for INET protocols (TCP, UDP)
    #        or "unix://<path>" for Unix Sockets.
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

    function getName()
    {
        return stream_socket_get_name($this->socket, false);
    }

    # Accepts an incoming connection.
    #
    # timeout - Timeout in seconds (default: PHP's default socket timeout).
    #
    # Example
    #
    #   <?php
    #
    #   use OOIO\Socket;
    #
    #   $ln = Socket::listen("tcp", "127.0.0.1:0");
    #
    #   for (;;) {
    #       $conn = $ln->accept();
    #
    #       # No connection within timeout.
    #       if (!$conn) continue;
    #
    #       $conn->puts("Hello World");
    #       $conn->close();
    #   }
    #
    # Returns a connection or False if no connection was made within
    # the Timeout duration.
    function accept($timeout = null)
    {
        if (null === $timeout) $timeout = ini_get("default_socket_timeout");

        $client = @stream_socket_accept($this->socket, $timeout, $peerName);

        if (false === $client) {
            return false;
        }

        return new Connection($client, $peerName);
    }
}
