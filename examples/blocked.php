<?php

require_once __DIR__.'/../vendor/autoload.php';

use Reamp\Server\EchoServer;
use Reamp\Server\IoServer;
use Reamp\Server\IpBlackList;

/**
 * Class Chat.
 * @package examples
 * Simple server that send received data and blocks all connection from localhost
 */
$blocked = new IpBlackList(new EchoServer());
$blocked->blockAddress('127.0.0.1');

$server = IoServer::factory(
    $blocked,
    8080
);
IoServer::run();
