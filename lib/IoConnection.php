<?php

namespace Reamp\Server;

use Amp\Promise;
use Amp\Socket\ServerSocket;

/**
 * @inheritdoc
 */
class IoConnection implements ConnectionInterface {
    /**
     * @var ServerSocket
     */
    private $conn;

	/**
	 * @var int connection id
	 */
    private $id;

	/**
	 * @var string Remote address
	 */
    private $remoteAddress;

	/**
	 * @var string Local address
	 */
    private $localAddress;

    /**
     * @param ServerSocket $conn
     */
    public function __construct(ServerSocket $conn) {
        $this->conn = $conn;
        $this->id = (int) $this->conn->getResource();
        $this->remoteAddress = $this->conn->getRemoteAddress();
        $this->localAddress = $this->conn->getLocalAddress();
    }

    /**
     * @inheritdoc
     */
    public function send($data): Promise {
        return $this->conn->write($data);
    }

    /**
     * @inheritdoc
     */
    public function close($data = null): Promise {
        return $this->conn->end($data ?? '');
    }

	/**
	 * @inheritdoc
	 */
    public function id() {
        return (int) $this->id;
    }

	/**
	 * @inheritdoc
	 */
    public function getRemoteAddress() {
        return $this->remoteAddress;
    }

	/**
	 * @inheritdoc
	 */
    public function getLocalAddress() {
        return $this->localAddress;
    }
}
