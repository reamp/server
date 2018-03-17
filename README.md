# ReAmp
What is ReAmp ? It is port of RatchEt to amphp/amp. It was created as fork of original project but add some new features:
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

Because code base is similar to Ratchet documentation can be found on original site.

# Ratchet

[![Build Status](https://secure.travis-ci.org/ratchetphp/Ratchet.png?branch=master)](http://travis-ci.org/ratchetphp/Ratchet)
[![Autobahn Testsuite](https://img.shields.io/badge/Autobahn-passing-brightgreen.svg)](http://socketo.me/reports/ab/index.html)
[![Latest Stable Version](https://poser.pugx.org/cboden/ratchet/v/stable.png)](https://packagist.org/packages/cboden/ratchet)

A PHP library for asynchronously serving WebSockets.
Build up your application through simple interfaces and re-use your application without changing any of its code just by combining different components.

## Requirements

Shell access is required and root access is recommended.
To avoid proxy/firewall blockage it's recommended WebSockets are requested on port 80 or 443 (SSL), which requires root access.
In order to do this, along with your sync web stack, you can either use a reverse proxy or two separate machines.
You can find more details in the [server conf docs](http://socketo.me/docs/deploy#serverconfiguration).

### Documentation

User and API documentation is available on Ratchet's website: http://socketo.me

See https://github.com/cboden/Ratchet-examples for some out-of-the-box working demos using Ratchet.

Need help?  Have a question?  Want to provide feedback?  Write a message on the [Google Groups Mailing List](https://groups.google.com/forum/#!forum/ratchet-php).

---

### A quick example

```php
<?php
use Reamp\MessageComponentInterface;
use Reamp\ConnectionInterface;

    // Make sure composer dependencies have been installed
    require __DIR__ . '/vendor/autoload.php';

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class MyChat implements MessageComponentInterface {
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
    $app = new Reamp\App('localhost', 8080);
    $app->route('/chat', new MyChat);
    $app->route('/echo', new Reamp\Server\EchoServer, array('*'));
    $app->run();
```

    $ php chat.php

```javascript
    // Then some JavaScript in the browser:
    var conn = new WebSocket('ws://localhost:8080/echo');
    conn.onmessage = function(e) { console.log(e.data); };
    conn.onopen = function(e) { conn.send('Hello Me!'); };
```
