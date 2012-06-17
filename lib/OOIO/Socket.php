<?php

namespace OOIO;

use OOIO\Socket\Listener,
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

    static function listen($proto, $spec)
    {
        $uri = "$proto://$spec";
        return new Listener($uri);
    }

    static function listenTCP($host, $port)
    {
        return static::listen("tcp", "$host:$port");
    }

    static function listenUDP($host, $port)
    {
        return static::listen("udp", "$host:$port");
    }

    static function listenUnix($filename)
    {
        return static::listen("unix", $filename);
    }
}

