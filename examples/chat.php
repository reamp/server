<?php

require_once __DIR__.'/../vendor/autoload.php';

use Reamp\Server\ConnectionInterface;
use Reamp\Server\IoServer;
use Reamp\Server\IoServerInterface;

/**
 * Class Chat.
 * @package examples
 * Simple chat that broadcast all messages
 */
class chat implements IoServerInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->id()})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = \count($this->clients) - 1;
        echo \sprintf(
            "Connection %d sending message \"%s\" to %d other connection%s\n",
            $from->id(),
            $msg,
            $numRecv,
            $numRecv == 1 ? '' : 's'
        );

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->id()} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Throwable $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

$server = IoServer::factory(
    new Chat(),
    8080
);

IoServer::run();
