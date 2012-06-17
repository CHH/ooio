<?php

require __DIR__ . "/../vendor/autoload.php";

use OOIO\IO,
    OOIO\Socket;

$stderr = IO::stderr();
$ln = Socket::listenTCP("127.0.0.1", "0");

$stderr->printf("Time server started on '%s'\n", array($ln->getName()));

for (;;) {
    $conn = $ln->accept();

    if ($conn) {
        $peer = $conn->getPeer();
        $stderr->printf("[info] Peer '%s' connected.\n", array($peer));

        $time = new DateTime;

        $conn->printf("%s\n", array($time->format(DateTime::RFC1123)));
        $conn->close();

        if ($conn->isDisconnected()) {
            $stderr->printf("[info] Peer '%s' disconnected.\n", array($peer));
        }
    }
}

