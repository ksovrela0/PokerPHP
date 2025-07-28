<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require 'PokerRooms.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new PokerRooms()
        )
    ),
    8080 // Port
);

$server->run();
