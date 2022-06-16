<?php

namespace App\Console\Commands;

use App\Services\RabbitMqConsumerService;
use Illuminate\Console\Command;

class consumeRabbitMqQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume-topic-exchange';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consumes rabbitmq topic exchange queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $exchangeName = 'laravelJob';
        $topics = [
            'MyBlCms.emailQueue',
            'MyBlCms.csvQueue'
        ];

        try {
            $rabbitMqConsumer = new RabbitMqConsumerService();
            $rabbitMqConsumer->consume($exchangeName, $topics);
        } catch (\Exception $e) {
            $this->info('Exception: ');
            $this->info($e->getMessage());
        }

    }
}
