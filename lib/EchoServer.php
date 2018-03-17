<?php

namespace Reamp\Server;

use Amp\Promise;
use Reamp\ConnectionInterface;
use Reamp\MessageComponentInterface;

/**
 * A simple ReAmp/Ratchet application that will reply to all messages with the message it received.
 */
class EchoServer implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
    }

    public function onMessage(ConnectionInterface $from, $msg): Promise {
        return $from->send($msg);
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Throwable $e): Promise {
        return $conn->close();
    }
}
