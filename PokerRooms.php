<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
require 'Poker.php';
class PokerRooms implements MessageComponentInterface {
    protected $clients;
    protected $rooms = [];
    protected $roomTimers = [];
    private $loop;

    public function __construct(LoopInterface $loop) {
        $this->clients = new \SplObjectStorage;
        $this->roomTimers = [];
        $this->loop = $loop;
        echo "WebSocket Server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        // Expect the room to be provided as a query param: ?room=room1
        $queryParams = [];
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);
        $room = isset($queryParams['room']) ? $queryParams['room'] : null;
        $token = $queryParams['token'] ?? null;

        if (!$room) {
            $conn->send(json_encode(['error' => 'Missing room parameter']));
            $conn->close();
            return;
        }

        $userId = $this->verifyToken($token, '599104454');
        if (!$userId) {
            $conn->send(json_encode(['error' => 'Invalid token']));
            $conn->close();
            return;
        }

        // Create the room if not exists
        if (!isset($this->rooms[$room])) {
            $this->rooms[$room] = [];
        }

        // Limit to 9 users
        if (count($this->rooms[$room]) >= 9) {
            $conn->send(json_encode(['error' => 'Room full']));
            $conn->close();
            return;
        }

        // Store room info
        $conn->room = $room;

        // Register connection
        $this->clients->attach($conn);
        $this->rooms[$room][$conn->resourceId] = $conn;

        echo "User $userId connected to room '$room' (resId {$conn->resourceId})\n";

        $joinMessage = [
            'type' => 'join',
            'id' => $conn->resourceId,
            'message' => "User {$conn->resourceId} joined the room",
            'users_inside' => array()
        ];

        foreach ($this->rooms[$room] as $client) {
            //if($client === $conn){
                $joinMessage['users_inside'][] = $client->resourceId;
            //}
        }

        foreach ($this->rooms[$room] as $client) {
            if ($client !== $conn) {
                $client->send(json_encode($joinMessage));
            }
            else{
                $joinMessage['type'] = "main_join";
                $client->send(json_encode($joinMessage));
            }
        }

        if (count($this->rooms[$room]) >= 2 && !isset($this->roomTimers[$room])) {
            $this->startRoomTimer($room);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $room = $from->room ?? null;

        if (!$room || !isset($this->rooms[$room])) {
            return;
        }

        foreach ($this->rooms[$room] as $client) {
            if ($client !== $from) {
                $client->send($msg);
            }
        }

        echo "Room '$room': {$from->resourceId} said: $msg\n";
    }

    public function onClose(ConnectionInterface $conn) {
        $room = $conn->room ?? null;

        $this->clients->detach($conn);

        if ($room && isset($this->rooms[$room][$conn->resourceId])) {
            unset($this->rooms[$room][$conn->resourceId]);
            if (empty($this->rooms[$room])) {
                unset($this->rooms[$room]);
            }
        }

        echo "Connection {$conn->resourceId} has disconnected from room '$room'\n";

        $leaveMessage = json_encode([
            'type' => 'leave',
            'id' => $conn->resourceId,
            'message' => "User {$conn->resourceId} left the room"
        ]);

        foreach ($this->rooms[$room] as $client) {
            $client->send($leaveMessage);
        }

        if ($room && count($this->rooms[$room]) < 2) {
            $this->stopRoomTimer($room);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    private function verifyToken($token, $secret) {
        $decoded = base64_decode($token, true);
        if (!$decoded) return false;

        [$userId, $signature] = explode(':', $decoded, 2) + [null, null];
        if (!$userId || !$signature) return false;

        $expectedSig = hash_hmac('sha256', $userId, $secret);
        if (!hash_equals($expectedSig, $signature)) return false;

        return $userId;
    }
    private function startRoomTimer($room) {
        echo "starting timer";
        $countdown = 10;

        $this->roomTimers[$room] = $this->loop->addPeriodicTimer(1, function() use (&$countdown, $room) {
            if (!isset($this->rooms[$room]) || count($this->rooms[$room]) < 2) {
                // Stop if users dropped
                echo "timer stopped 1";
                $this->stopRoomTimer($room);
                return;
            }

            foreach ($this->rooms[$room] as $client) {
                $client->send(json_encode([
                    'type' => 'timer',
                    'value' => $countdown
                ]));
            }

            if ($countdown <= 0) {
                echo "timer stopped 2";
                $this->stopRoomTimer($room);
                return;
            }

            $countdown--;
        });
    }
    private function stopRoomTimer($room) {
        if (isset($this->roomTimers[$room])) {
            $this->loop->cancelTimer($this->roomTimers[$room]);
            unset($this->roomTimers[$room]);
        }
    }
    public function getLoop() {
        return $this->loop;
    }

}
