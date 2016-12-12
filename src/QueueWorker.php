<?php

namespace Ccovey\SymfonyRabbitMQBundle;

use Ccovey\RabbitMQ\Consumer\Consumable;
use Ccovey\RabbitMQ\Consumer\ConsumableParameters;
use Ccovey\RabbitMQ\Consumer\Consumer;
use Ccovey\RabbitMQ\Consumer\ConsumerInterface;
use Ccovey\RabbitMQ\QueuedMessage;
use Ccovey\RabbitMQ\QueuedMessageInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Throwable;

class QueueWorker
{
    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var ConsumableFactory
     */
    private $consumableFactory;

    /**
     * @var QueueHandlerInterface
     */
    private $handler;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QueuedMessageInterface|null
     */
    private $currentQueuedMessage;

    /**
     * @var bool
     */
    private $exitBeforeNextEvent = false;

    public function __construct(
        ConsumerInterface $consumer,
        ConsumableFactory $consumableFactory,
        QueueHandlerInterface $handler,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger)
    {
        $this->consumer = $consumer;
        $this->consumableFactory = $consumableFactory;
        $this->handler = $handler;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    public function run(string $queue, int $memoryLimit = 256)
    {
        $this->consumer->setCallback(function (QueuedMessageInterface $queuedMessage) use ($memoryLimit) {
            $this->processMessage($queuedMessage, $memoryLimit);
        });

        $consumable = $this->consumableFactory->getConsumable($queue);
        $this->consumer->consume($consumable);
    }

    public function processMessage(QueuedMessageInterface $queuedMessage, int $memoryLimit)
    {
        try {
            $this->currentQueuedMessage = $queuedMessage;
            $this->handler->handle($queuedMessage, $memoryLimit);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    '%s::%s caught exception %s with message: %s',
                    __Class__,
                    __METHOD__,
                    $e->getMessage()
                )
            );
            $this->dispatcher->dispatch('ccovey.queue.exception', new GenericEvent($e, ['currentQueuedMessage' => $this->currentQueuedMessage]));
            $this->exitBeforeNextEvent();
        } finally {
            $this->consumer->complete($this->currentQueuedMessage);

            if ($this->shouldExitBeforeNextEvent()) {
                exit;
            }
            $this->currentQueuedMessage = null;
        }

    }

    private function exitBeforeNextEvent()
    {
        $this->exitBeforeNextEvent = true;
    }

    private function shouldExitBeforeNextEvent() : bool
    {
        return $this->exitBeforeNextEvent;
    }
}
