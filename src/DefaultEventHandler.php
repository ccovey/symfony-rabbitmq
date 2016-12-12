<?php

namespace Ccovey\SymfonyRabbitMQBundle;

use Ccovey\RabbitMQ\QueuedMessageInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Stopwatch\Stopwatch;

class DefaultEventHandler implements QueueHandlerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MessageUnserializerInterface
     */
    private $messageUnserializer;

    public function __construct(EventDispatcherInterface $dispatcher, MessageUnserializerInterface $messageUnserializer)
    {
        $this->dispatcher = $dispatcher;
        $this->messageUnserializer = $messageUnserializer;
    }

    public function handle(QueuedMessageInterface $message, int $memoryLimit)
    {
        $this->dispatcher->dispatch('ccovey.queue.pre_message_handle', new GenericEvent($message));
        $queuedEvent = $this->messageUnserializer->unserialize($message);
        $stopwatch = new Stopwatch();
        $stopwatch->start($queuedEvent->getName());

        try {
            $this->dispatcher->dispatch($queuedEvent->getName(), new GenericEvent($queuedEvent->getSubject(), $queuedEvent->getArguments()));
        } catch (Exception $e) {
            $message->fail($e);
            $this->dispatcher->dispatch('ccovey.queue.message_failed', new GenericEvent($message));
        }
        $stopwatchEvent = $stopwatch->stop($queuedEvent->getName());
        $message->setStopWatchEvent($stopwatchEvent);
        $this->dispatcher->dispatch('ccovey.queue.post_message_handle', new GenericEvent($message));
    }
}
