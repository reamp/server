<?php

namespace tests\Server;

use Amp\Loop;
use Amp\Socket\Server;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\Exception;
use PHPUnit\Framework\TestCase;
use Reamp\Server\ConnectionInterface;
use Reamp\Server\IoServer;
use Reamp\Server\IoServerInterface;

/**
 * @covers \Reamp\Server\IoServer
 */
class IoServerTest extends TestCase {

    /**
     * @var  IoServer
     */
    protected $server;

    /**
     * @var MockObject|IoServerInterface
     */
    protected $app;

    protected $port;

    /**
     * @var Server
     */
    protected $reactor;

    public function setUp() {
        $this->app = $this->createMock(IoServerInterface::class);

        $this->reactor = \Amp\Socket\listen('0.0.0.0:0');

        $uri = $this->reactor->getAddress();
        $this->port = \parse_url((\strpos($uri, '://') === false ? 'tcp://' : '') . $uri, PHP_URL_PORT);
        $this->server = new IoServer($this->app, $this->reactor);
    }

    public function testOnOpen() {
        $this->app->expects($this->once())->method('onOpen')->with($this->isInstanceOf(ConnectionInterface::class));


        //$this->reactor->close();
        Loop::delay(100, [$this->reactor, 'close']);
        Loop::defer(function () {
            $client = \stream_socket_client("tcp://localhost:{$this->port}");
        });
        IoServer::run();
        //$this->server->loop->tick();

        //self::assertTrue(is_string($this->app->last['onOpen'][0]->remoteAddress));
        //self::assertTrue(is_int($this->app->last['onOpen'][0]->resourceId));
    }

    public function testOnData() {
        $msg = 'Hello World!';

        $this->app->expects($this->once())->method('onMessage')->with(
            $this->isInstanceOf(ConnectionInterface::class),
            $msg
        );

        $client = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        \socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        \socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        \socket_set_block($client);

        Loop::delay(100, [$this->reactor, 'close']);
        Loop::defer(function () use ($client, $msg) {
            \socket_connect($client, 'localhost', $this->port);

            //$this->server->loop->tick();

            \socket_write($client, $msg);
            //$this->server->loop->tick();

            \socket_shutdown($client, 1);
            \socket_shutdown($client, 0);
            \socket_close($client);
        });
        IoServer::run();
    }

    public function testOnClose() {
        $this->app->expects($this->once())->method('onClose')->with($this->isInstanceOf(ConnectionInterface::class));

        $client = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        \socket_set_option($client, SOL_SOCKET, SO_REUSEADDR, 1);
        \socket_set_option($client, SOL_SOCKET, SO_SNDBUF, 4096);
        \socket_set_block($client);

        Loop::delay(100, [$this->reactor, 'close']);
        Loop::defer(function () use ($client) {
            \socket_connect($client, 'localhost', $this->port);


            \socket_shutdown($client, 1);
            \socket_shutdown($client, 0);
            \socket_close($client);
        });
        IoServer::run();
    }

    public function testFactory() {
        self::assertInstanceOf(IoServer::class, IoServer::factory($this->app, 0));
    }

    public function testOnErrorPassesException() {
        $decor = $this->createMock(ConnectionInterface::class);
        $err = new \Exception("Nope");

        $this->app->expects($this->once())->method('onError')->with($decor, $err);

        $this->server->handleError($err, $decor);
    }

    public function testOnFatalErrorPassesException() {
        $decor = $this->createMock(ConnectionInterface::class);
        $err = new \Error(\Throwable::class);

        $this->app->expects($this->once())->method('onError')->with($decor, $err);

        $this->server->handleError($err, $decor);
    }


    public function testOnErrorCalledWhenExceptionThrown() {
        $conn = $this->createMock(ConnectionInterface::class);
        $this->server->handleConnect($conn);

        $e = new \Exception;
        $this->app->expects($this->once())->method('onMessage')->with($this->isInstanceOf(ConnectionInterface::class), 'f')->willThrowException($e);
        $this->app->expects($this->once())->method('onError')->with($this->isInstanceOf(ConnectionInterface::class), $e);

        $this->server->handleData('f', $conn);
    }

    public function testOnErrorCalledWhenErrorThrown() {
        $conn = $this->createMock(ConnectionInterface::class);
        $this->server->handleConnect($conn);

        $e = new \Error();
        $this->app->expects($this->once())->method('onMessage')->with($this->isInstanceOf(ConnectionInterface::class), 'f')->will(new Exception($e));
        $this->app->expects($this->once())->method('onError')->with($this->isInstanceOf(ConnectionInterface::class), $e);

        $this->server->handleData('f', $conn);
    }
}
