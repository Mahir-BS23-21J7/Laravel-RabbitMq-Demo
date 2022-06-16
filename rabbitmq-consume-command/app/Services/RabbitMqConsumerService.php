<?php

namespace App\Services;
use PhpAmqpLib\Connection\AMQPStreamConnection as RabbitMqConnect;

class RabbitMqConsumerService {

    private $connection;
    private $channel;

    public function __construct(string $host = null, int $port = null, string $user = null, string $pass = null)
    {
        $host = $host ?? 'rabbitmq';
        $port = $port ?? 5672;
        $user = $user ?? 'rabbit';
        $pass = $pass ?? 'rabbit';

        $this->connection = new RabbitMqConnect($host, $port, $user, $pass);
        $this->channel = $this->connection->channel();
    }

    public function consume(string $exchangeName = '', array $bindingKeys)
    {
        $this->channel->exchange_declare(
            $exchangeName ?? 'laravelJob',
            'topic',
            false,
            false,
            false,
        );

        list($queueName, ,) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            false
        );

        foreach ($bindingKeys as $bindingKey) {
            $this->channel->queue_bind(
                $queueName,
                $exchangeName ?? 'laravelJob',
                $bindingKey
            );
        }

        dump(" [*] Waiting for logs. To exit press CTRL+C");

        $callback = function ($msg)
        {
            $infoArr    = explode('.', $msg->delivery_info['routing_key']);
            $appName    = $infoArr[0];
            $queueName  = $infoArr[1];
            dump(' [x] App: ' . $appName . ', Queue: ' . $queueName . ', Message: ' . $msg->body);
            $msg->ack();
        };

        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }
}
