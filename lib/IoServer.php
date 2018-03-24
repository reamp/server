<?php

namespace Reamp\Server;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use Amp\Socket\Server;
use Amp\Socket\ServerSocket;
use Amp\Socket\SocketException;

/**
 * The IoServer should be the base of your application. This is the core of the events driven from client actions.
 * It handles receiving new connections, reading/writing to those connections, closing the connections,
 * and handles all errors from your application.
 * 
 * Creates an open-ended socket to listen on a port for incoming connections.
 * Events are delegated through this to attached applications.
 *
 * Example: {@see EchoServer}
 * @see IoServerInterface
 */
class IoServer {

    /**
     * @var IoServerInterface
     */
    public $app;

    /**
     * The socket server the ReAmp Application is run off of.
     * @var Server
     */
    public $socket;

    /**
     * @param IoServerInterface $app The ReAmp application stack to host
     * @param Server $socket The amp socket server to run the ReAmp application
     */
    public function __construct(IoServerInterface $app, Server $socket) {
        if (false === \strpos(PHP_VERSION, "hiphop")) {
            \gc_enable();
        }

        \set_time_limit(0);
        \ob_implicit_flush();

        $this->app = $app;
        $this->socket = $socket;

        \Amp\asyncCall(function () {
            while ($connection = yield $this->socket->accept()) {
                $this->handleConnect($connection);
            }
        });
    }

    /**
     * @param  IoServerInterface $component The application that I/O will call when events are received
     * @param  int $port The port to server sockets on
     * @param  string $address The address to receive sockets on (0.0.0.0 means receive connections from any)
     * @return IoServer
	 *
	 * @throws SocketException If binding to the specified URI failed.
	 * @throws \Error If an invalid scheme is given.
     */
    public static function factory(IoServerInterface $component, $port = 80, $address = '0.0.0.0') {
        $socket = \Amp\Socket\listen($address . ':' . $port);

        return new static($component, $socket);
    }

    /**
     * Run all application instances.
     */
    public static function run() {

        // @codeCoverageIgnoreStart
        Loop::run();
        // @codeCoverageIgnoreEnd
    }

    /**
     * Triggered when a new connection is received from Amp.
     * @param ServerSocket $conn
     * @return Promise
     */
    public function handleConnect($conn): Promise {
        return new Coroutine($this->onConnect($conn));
    }

    /**
     * Triggered when a new connection is received from Amp.
     * @param ServerSocket $conn
     * @return \Generator
     */
    protected function onConnect($conn): \Generator {
        $decorated = new IoConnection($conn);

        // message component can use promises or amp style coroutines.
        yield \Amp\call([$this->app, 'onOpen'], $decorated);

        try {
            while ($data = yield $conn->read()) {
                yield $this->handleData($data, $decorated);
            }
            yield $this->handleEnd($decorated);
        } catch (\Throwable $e) {
            yield $this->handleError($e, $decorated);
        }
    }

    /**
     * Data has been received from Amp.
     * @param string $data
     * @param ConnectionInterface $conn
     * @return Promise
     */
    public function handleData($data, $conn): Promise {
        return new Coroutine($this->onData($data, $conn));
    }

    /**
     * Data has been received from Amp.
     * @param string $data
     * @param ConnectionInterface $conn
     * @return  \Generator
     */
    protected function onData($data, $conn): \Generator {
        try {
            // message component can use promises or amp style coroutines.
            yield \Amp\call([$this->app, 'onMessage'], $conn, $data);
        } catch (\Throwable $e) {
            yield $this->handleError($e, $conn);
        }
    }

    /**
     * An error has occurred, let the listening application know.
     * @param \Throwable $e
     * @param ConnectionInterface $conn
     * @return Promise
     *
     */
    public function handleError(\Throwable $e, $conn): Promise {
        // message component can use promises or amp style coroutines.
        return new Coroutine($this->onError($e, $conn));
    }

	/**
	 * An error has occurred, let the listening application know.
	 * @param \Throwable $e
	 * @param $conn
	 * @return \Generator
	 */
    protected function onError(\Throwable $e, $conn) {
        try {
            yield \Amp\call([$this->app, 'onError'], $conn, $e);
        } catch (\Throwable $e) {
            // nothing to do
        }
    }

    /**
     * A connection has been closed by React.
     * @param ConnectionInterface $conn
     * @return Promise
     */
    public function handleEnd($conn): Promise {
        return new Coroutine($this->onClose($conn));
    }

    /**
     * A connection has been closed by React.
     * @param ConnectionInterface $conn
     * @return \Generator
     */
    protected function onClose($conn): \Generator {
        try {
            // message component can use promises or amp style coroutines.
            yield \Amp\call([$this->app, 'onClose'], $conn);
        } catch (\Throwable $e) {
            yield $this->handleError($e, $conn);
        }

        unset($conn);
    }
}
