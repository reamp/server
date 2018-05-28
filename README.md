# ReAmp
What is ReAmp ? It is port of [Ratchet](http://socketo.me) to [amphp](https://amphp.org/). It was created as fork of original project but add some new features:
 * Use different namespace
 * Use amphp by default 
 * Php7 support
 * Phpunit 6
 * Code style
 
There still lack of features but will be done in future:
 * http request body support (post/put/create) 
 * http request pipeline support 
 * psr logging support 
 * better parsing similar to amp/aerys style
 * support async and promises in components 

## Reamp server
This is base component of reamp. It used for creating socket server

## Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require reamp/server
```

## Documentation

Documentation can be found on [Ratchet's website](http://socketo.me): with some exceptions as well as in the [`./docs`](./docs) directory.

## Requirements

- PHP 7.0+
- Shell access is required and root access is recommended.

## Examples

This example create simple chat server on port 8080
```php
<?php
use Reamp\Server\IoServer;
use Reamp\Server\IoServerInterface;
use Reamp\Server\ConnectionInterface;

// Make sure composer dependencies have been installed
require __DIR__ . '/vendor/autoload.php';

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class MyChat implements IoServerInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from != $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Throwable $e) {
        $conn->close();
    }
}

// Run the server application through the WebSocket protocol on port 8080
$app = IoServer::factory(new MyChat(), 8080, 'localhost');
IoServer::run();
```

    $ php chat.php

Further examples can be found in the [`./examples`](./examples) directory of this repository as well as in the [Ratchet](https://github.com/cboden/Ratchet-examples) repository.

