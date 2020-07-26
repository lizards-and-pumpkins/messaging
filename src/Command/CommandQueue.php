<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Queue;

class CommandQueue
{
    /**
     * @var Queue
     */
    private $messageQueue;

    public function __construct(Queue $messageQueue)
    {
        $this->messageQueue = $messageQueue;
    }

    public function add(Command $command)
    {
        $this->messageQueue->add($command->toMessage());
    }
}
