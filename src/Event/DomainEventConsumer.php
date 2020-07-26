<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\Logging\Message\DomainEventHandlerFailedMessage;
use LizardsAndPumpkins\Messaging\Event\Logging\Message\FailedToReadFromDomainEventQueueMessage;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use LizardsAndPumpkins\Messaging\Queue\QueueMessageConsumer;

class DomainEventConsumer implements QueueMessageConsumer, MessageReceiver
{
    private $maxNumberOfMessagesToProcess = 200;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var DomainEventHandlerLocator
     */
    private $handlerLocator;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $queue, DomainEventHandlerLocator $locator, Logger $logger)
    {
        $this->queue = $queue;
        $this->handlerLocator = $locator;
        $this->logger = $logger;
    }

    public function process(): void
    {
        $this->processNumberOfMessages($this->maxNumberOfMessagesToProcess);
    }

    public function processAll(): void
    {
        if (($n = $this->queue->count()) > 0) {
            $this->processNumberOfMessages($n);
        }
    }

    private function processNumberOfMessages(int $numberOfMessagesToProcess): void
    {
        try {
            $messageReceiver = $this;
            $this->queue->consume($messageReceiver, $numberOfMessagesToProcess);
        } catch (\Exception $e) {
            $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
        }
    }

    public function receive(Message $message): void
    {
        try {
            $domainEventHandler = $this->handlerLocator->getHandlerFor($message);
            $domainEventHandler->process($message);
        } catch (\Exception $e) {
            $this->logger->log(new DomainEventHandlerFailedMessage($message, $e));
        }
    }
}
