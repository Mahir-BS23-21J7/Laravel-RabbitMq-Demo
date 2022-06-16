<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'rabbit', 'rabbit');
$channel = $connection->channel();

$channel->exchange_declare(
    $exchangeName ?? 'laravelJob',
    'topic',
    false,
    false,
    false
);

$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';

$data = implode(' ', array_slice($argv, 2));

if (empty($data)) {
    $data = "Hello World!";
}

$msg = new AMQPMessage($data);

try {
    $channel->basic_publish($msg, 'laravelJob', $routing_key);
    echo ' [x] Sent ', $routing_key, ':', $data, "\n";
} catch (AMQPProtocolChannelException $e) {
    var_dump($e->getMessage());
}


$channel->close();
$connection->close();