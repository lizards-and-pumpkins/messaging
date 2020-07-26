<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Logging;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Queue\Logging\Message\QueueAddLogMessage;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Queue;

class LoggingQueueDecorator implements Queue
{
    /**
     * @var Queue
     */
    private $decoratedQueue;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $queueToDecorate, Logger $logger)
    {
        $this->decoratedQueue = $queueToDecorate;
        $this->logger = $logger;
    }

    public function count() : int
    {
        return $this->decoratedQueue->count();
    }

    public function add(Message $message): void
    {
        $this->logger->log(new QueueAddLogMessage($message->getName(), $this->decoratedQueue));
        $this->decoratedQueue->add($message);
    }

    public function consume(MessageReceiver $messageReceiver, int $numberOfMessagesToConsume): void
    {
        $this->decoratedQueue->consume($messageReceiver, $numberOfMessagesToConsume);
    }
}
