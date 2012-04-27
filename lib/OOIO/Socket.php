<?php

namespace OOIO;

use OOIO\Socket\Server,
    OOIO\Stream;

class Socket
{
    static function pair($domain, $type, $protocol)
    {
        list($r, $w) = stream_socket_pair($domain, $type, $protocol);
        return array(new Stream($r), new Stream($w));
    }

    static function client()
    {
    }

    static function tcpServer($host, $port)
    {
        $uri = "tcp://$host:$port";
        return new Server($uri);
    }

    static function udpServer($host, $port)
    {
        $uri = "udp://$host:$port";
        return new Server($uri);
    }

    static function unixServer($filename)
    {
        $uri = "unix://$filename";
        return new Server($uri);
    }
}

