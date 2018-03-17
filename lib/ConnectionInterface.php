<?php
namespace Reamp\Server;

use Amp\Promise;


/**
 * A proxy object representing a connection to the application
 * This acts as a container to store data (in memory) about the connection.
 */
interface ConnectionInterface {
	/**
	 * Send data to the connection.
	 * @param  string $data
	 * @return Promise
	 */
	public function send($data): Promise;

	/**
	 * Close the connection.
	 * @param string $data
	 * @return Promise
	 */
	public function close($data = null): Promise;

	/**
	 * @return int Connection id
	 */
	public function id();

	/**
	 * @return string Remote address
	 */
	public function getRemoteAddress();

	public function getLocalAddress();
}
