# IoServer
## Purpose of this Component
The IoServer should be the base of your application. This is the core of the events driven from client actions. It handles receiving new connections, reading/writing to those connections, closing the connections, and handles all errors from your application.


##Events triggered by this Component
* `onOpen (ConnectionInterface $conn)` - A new client connection has been opened
* `onClose (ConnectionInterface $conn)` - A client connection is about to, or has closed
* `onMessage (ConnectionInterface $from, string $message)` - A data message has been received
* `onError (ConnectionInterface $from, Throwable $error)` - An error has occurred with a Connection

## Configuration methods
* `__construct(IoServerInterface $app, ServerInterface $socket)` - create new server for app and socket. You can use this method for handling secure connections 
* `static void run()` - Enter the event loop of your application and listen for incoming connections and data
* `static factory(IoServerInterface $component, $port = 80, $address = '0.0.0.0'):IoServer` - factory for creating server

##Functions callable on Connections
* `send(string $message):Promise` - Send a message (string) to the client and return promise
* `close(string $message = null):Promose` - Gracefully close the connection to the client and return promise
* `id()` - Return incremental number assigned when the connection is made
* `getRemoteAddress()` - Return address (IP Address and port) the user connected with
* `getLocalAddress()` - Return address (IP Address and port) the user connected to

## Wraps other components nicely
Your app class (for testing, or making a telnet application) implementing `IoServerInterface`
* `IpBlackList`
* `FlashPolicy`
* `EchoServer`

Wrapped by other components nicely
Typically, none. This should be the base of your application as it handles the direct communication and transport with clients.

Usage
```php
<?php
// Your shell script
use Reamp\Server\IoServer;

$server = IoServer::factory(new MyApp, 8080); // Run your app on port 8080
$server->run();
```