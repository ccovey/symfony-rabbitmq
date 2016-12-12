<?php

namespace Ccovey\SymfonyRabbitMQBundle;

use Ccovey\RabbitMQ\QueuedMessageInterface;

interface QueueHandlerInterface
{
    public function handle(QueuedMessageInterface $message, int $memoryLimit);
}
