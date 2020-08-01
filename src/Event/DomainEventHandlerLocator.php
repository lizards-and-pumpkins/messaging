<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;

class DomainEventHandlerLocator
{
    /**
     * @var DomainEventHandler
     */
    private $handlers = [];

    public function register(string $eventCode, DomainEventHandler $handler): void
    {
        $this->handlers[$eventCode] = $handler;
    }

    public function getHandlerFor(Message $event): DomainEventHandler
    {
        if (! array_key_exists($event->getName(), $this->handlers)) {
            throw new UnableToFindDomainEventHandlerException(
                sprintf('Unable to find a handler for "%s" event', $event->getName())
            );
        }

        return $this->handlers[$event->getName()];
    }
}
