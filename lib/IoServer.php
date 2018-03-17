<?php

namespace Reamp\Server;

use Amp\Coroutine;
use Amp\Loop;
use Amp\Promise;
use Amp\Socket\Server as ServerInterface;
use Amp\Socket\ServerSocket as Connection;
use Reamp\ConnectionInterface;

/**
 * Creates an open-ended socket to listen on a port for incoming connections.
 * Events are delegated through this to attached applications.
 */
class IoServer {

    /**
     * @var IoServerInterface
     */
    public $app;

    /**
     * The socket server the ReAmp Application is run off of.
     * @var ServerInterface
     */
    public $socket;

    /**
     * @param \Reamp\MessageComponentInterface $app The ReAmp/Ratchet application stack to host
     * @param ServerInterface $socket The amp socket server to run the ReAmp/Ratchet application off of
     */
    public function __construct(IoServerInterface $app, ServerInterface $socket) {
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
     * @param  \Reamp\MessageComponentInterface $component The application that I/O will call when events are received
     * @param  int $port The port to server sockets on
     * @param  string $address The address to receive sockets on (0.0.0.0 means receive connections from any)
     * @return IoServer
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
     * @param Connection $conn
     * @return Promise
     */
    public function handleConnect($conn): Promise {
        return new Coroutine($this->onConnect($conn));
    }

    /**
     * Triggered when a new connection is received from Amp.
     * @param Connection $conn
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
