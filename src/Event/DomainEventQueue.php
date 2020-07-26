<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Queue;

class DomainEventQueue
{
    /**
     * @var Queue
     */
    private $messageQueue;

    public function __construct(Queue $messageQueue)
    {
        $this->messageQueue = $messageQueue;
    }

    public function add(DomainEvent $event): void
    {
        $this->messageQueue->add($event->toMessage());
    }
}
