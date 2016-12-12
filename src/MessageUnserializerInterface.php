<?php

namespace Ccovey\SymfonyRabbitMQBundle;

use Ccovey\RabbitMQ\QueuedMessageInterface;

interface MessageUnserializerInterface
{
    public function unserialize(QueuedMessageInterface $queuedMessage) : QueuedEvent;
}
