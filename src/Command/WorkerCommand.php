<?php

namespace Ccovey\SymfonyRabbitMQBundle\Command;

use Ccovey\SymfonyRabbitMQBundle\QueueWorker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class WorkerCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var QueueWorker
     */
    private $worker;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var int
     */
    private $memoryLimit;

    public function configure()
    {
        $this->setName('ccovey:queue:worker')
            ->addArgument('queue', InputArgument::REQUIRED, 'Routing Key the worker will use.')
            ->addArgument('memory-limit', InputArgument::OPTIONAL, 'Memory Limit after which the worker will be killed', 256);
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->worker = $this->getContainer()->get('ccovey.rabbitmq.worker');
        $this->queue = $input->getArgument('queue');
        $this->memoryLimit = $input->getArgument('memory-limit');
        $this->dispatcher = $this->getContainer()->get('event_dispatcher');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dispatcher->dispatch('ccovey.queue.pre_worker.run', new GenericEvent($this->queue));
        $this->worker->run($this->queue, $this->memoryLimit);
    }

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }
}
