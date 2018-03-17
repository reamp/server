<?php

namespace tests\Server;

use Amp\Socket\ServerSocket;
use PHPUnit\Framework\TestCase;
use Reamp\Server\IoConnection;

/**
 * @covers \Reamp\Server\IoConnection
 */
class IoConnectionTest extends TestCase {
    protected $sock;
    protected $conn;

    public function setUp() {
        $this->sock = $this->createMock(ServerSocket::class);
        $this->conn = new IoConnection($this->sock);
    }

    public function testCloseBubbles() {
        $this->sock->expects($this->once())->method('end');
        $this->conn->close();
    }

    public function testSendBubbles() {
        $msg = '6 hour rides are productive';

        $this->sock->expects($this->once())->method('write')->with($msg);
        $this->conn->send($msg);
    }
}
