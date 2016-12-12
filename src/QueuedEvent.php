<?php

namespace Ccovey\SymfonyRabbitMQBundle;

class QueuedEvent
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $subject;

    public function __construct(string $name, $subject, array $arguments = [])
    {
        $this->name = $name;
        $this->subject = $subject;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    public function getArguments() : array
    {
        return $this->arguments;
    }
}
