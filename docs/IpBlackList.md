#IpBlackList
## Purpose of this Component
Is someone doing something malicious to your server? Keep them out!

The IpBlackList component let's you configured IP addresses to block. It should be placed as close to the IoServer as possible as it will kick bad connections immediately.

## Events triggered by this Component
* `onOpen (ConnectionInterface $conn)` - A new client connection has been opened
* `onClose (ConnectionInterface $conn)` - A client connection is about to, or has closed
* `onMessage (ConnectionInterface $from, string $message)` - A data message has been received
* `onError (ConnectionInterface $from, Exception $error)` - An error has occurred with a Connection

## Configuration methods
* `IpBlackList blockAddress (string $address)` - Specify an address to be blocked. This can be an IP4, IP6, or named address
* `IpBlackList unblockAddress (string $address)` - Unblock an address that was previously blocked
* `isBlocked (string $address):boolean` - Check to see if an address is being blocked
* `getBlockedAddresses ():array` - Return an indexed array of all the addresses being blocked

## Functions callable on Connections
* `send (string $message):Promise` - Send a message (string) to the client
* `close (string $message = null):Promise` - Gracefully close the connection to the client

## Parameters added to each Connection
None.

## Wraps other components nicely
Your app class (for testing, or making a telnet application) implementing `IoServerInterface`
* FlashPolicy
* EchoServer

## Wrapped by other components nicely
IoServer

## Usage
```php
<?php
// Your shell script
use Reamp\Server\IpBlackList;
use Reamp\Server\IoServer;

$blackList = new IpBlackList(new MyChat);
$blackList->blockAddress('74.125.226.46'); // Stop Google from connecting to our server

$server = IoServer::factory($blackList, 8080);
$server->run();
```