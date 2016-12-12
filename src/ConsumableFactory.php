<?php

namespace Ccovey\SymfonyRabbitMQBundle;

use Ccovey\RabbitMQ\Consumer\ConsumableParameters;

class ConsumableFactory
{
    public function __construct()
    {

    }

    /**
     * We pass in the queue name and grab everything else from configs.
     */
    public function getConsumable(string $queueName) : ConsumableParameters
    {
        return new ConsumableParameters($queueName);
    }
}
