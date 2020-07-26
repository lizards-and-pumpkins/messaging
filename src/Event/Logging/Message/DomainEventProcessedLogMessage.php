<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event\Logging\Message;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

class DomainEventProcessedLogMessage implements LogMessage
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var DomainEventHandler
     */
    private $domainEventHandler;

    public function __construct(string $message, DomainEventHandler $domainEventHandler)
    {
        $this->message = $message;
        $this->domainEventHandler = $domainEventHandler;
    }

    public function __toString(): string
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
    public function getContext(): array
    {
        return ['domain_event_handler' => $this->domainEventHandler];
    }

    public function getContextSynopsis(): string
    {
        return sprintf('DomainEventHandler Class: %s', get_class($this->domainEventHandler));
    }
}
