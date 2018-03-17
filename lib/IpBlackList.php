<?php

namespace Reamp\Server;

class IpBlackList implements IoServerInterface {
    /**
     * @var array
     */
    protected $_blacklist = [];

    /**
     * @var IoServerInterface
     */
    protected $_decorating;

    /**
     * @param \Reamp\MessageComponentInterface $component
     */
    public function __construct(IoServerInterface $component) {
        $this->_decorating = $component;
    }

    /**
     * Add an address to the blacklist that will not be allowed to connect to your application.
     * @param  string $ip IP address to block from connecting to your application
     * @return IpBlackList
     */
    public function blockAddress($ip) {
        $this->_blacklist[$ip] = true;

        return $this;
    }

    /**
     * Unblock an address so they can access your application again.
     * @param string $ip IP address to unblock from connecting to your application
     * @return IpBlackList
     */
    public function unblockAddress($ip) {
        if (isset($this->_blacklist[$this->filterAddress($ip)])) {
            unset($this->_blacklist[$this->filterAddress($ip)]);
        }

        return $this;
    }

    /**
     * @param  string $address
     * @return bool
     */
    public function isBlocked($address) {
        return (isset($this->_blacklist[$this->filterAddress($address)]));
    }

    /**
     * Get an array of all the addresses blocked.
     * @return array
     */
    public function getBlockedAddresses() {
        return \array_keys($this->_blacklist);
    }

    /**
     * @param  string $address
     * @return string
     */
    public function filterAddress($address) {
        if (\strstr($address, ':') && \substr_count($address, '.') == 3) {
            list($address, $port) = \explode(':', $address);
        }

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn) {
        if ($this->isBlocked($conn->getRemoteAddress())) {
            return $conn->close();
        }
        // simply proxy result of handler
        return $this->_decorating->onOpen($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        return $this->_decorating->onMessage($from, $msg);
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn) {
        if (!$this->isBlocked($conn->getRemoteAddress())) {
            // simply proxy result of handler
            return $this->_decorating->onClose($conn);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Throwable $e) {
        if (!$this->isBlocked($conn->getRemoteAddress())) {
            // simply proxy result of handler
            return $this->_decorating->onError($conn, $e);
        }
    }
}
