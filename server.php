<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

require 'PokerRooms.php';

$loop = Loop::get();

$app = new PokerRooms($loop);
$socket = new SocketServer('0.0.0.0:8080', [], $loop);
$server = new IoServer(
    new HttpServer(
        new WsServer($app)
    ),
    $socket,
    $loop
);

// Start loop (this keeps the server running)
$loop->run();
