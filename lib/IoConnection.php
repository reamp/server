<?php

namespace Reamp\Server;

use Amp\Promise;
use Amp\Socket\ServerSocket as AmpConn;
use Reamp\ConnectionInterface;

/**
 * {@inheritdoc}
 */
class IoConnection implements ConnectionInterface {
    /**
     * @var AmpConn
     */
    protected $conn;

    private $id;

    private $remoteAddress;

    private $localAddress;

    /**
     * @param AmpConn $conn
     */
    public function __construct(AmpConn $conn) {
        $this->conn = $conn;
        $this->id = (int) $this->conn->getResource();
        $this->remoteAddress = $this->conn->getRemoteAddress();
        $this->localAddress = $this->conn->getLocalAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function send($data): Promise {
        return $this->conn->write($data);
    }

    /**
     * {@inheritdoc}
     */
    public function close($data = null): Promise {
        return $this->conn->end($data ?? '');
    }

    public function id() {
        return (int) $this->id;
    }

    public function getRemoteAddress() {
        return $this->remoteAddress;
    }

    public function getLocalAddress() {
        return $this->localAddress;
    }
}
