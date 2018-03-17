<?php

namespace Reamp\Server;

/**
 * This is the interface to build a ReAmp application with.
 * It implements the decorator pattern to build an application stack.
 */
interface IoServerInterface {
    /**
     * When a new connection is opened it will be passed to this method.
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Throwable
     */
    public function onOpen(ConnectionInterface $conn);

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Throwable
     */
    public function onClose(ConnectionInterface $conn);

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method.
     * @param  ConnectionInterface $conn
     * @param  \Throwable          $e
     * @throws \Throwable
     */
    public function onError(ConnectionInterface $conn, \Throwable $e);

    /**
     * Triggered when a client sends data through the socket.
     * @param  \Reamp\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string                       $msg  The message received
     * @throws \Throwable
     */
    public function onMessage(ConnectionInterface $from, $msg);
}
