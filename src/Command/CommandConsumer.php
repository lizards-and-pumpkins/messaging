<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\Logging\Message\CommandHandlerFailedMessage;
use LizardsAndPumpkins\Messaging\Command\Logging\Message\FailedToReadFromCommandQueueMessage;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use LizardsAndPumpkins\Messaging\Queue\QueueMessageConsumer;

class CommandConsumer implements QueueMessageConsumer, MessageReceiver
{
    private $maxNumberOfMessagesToProcess = 200;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var CommandHandlerLocator
     */
    private $commandHandlerLocator;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $commandQueue, CommandHandlerLocator $commandHandlerLocator, Logger $logger)
    {
        $this->commandQueue = $commandQueue;
        $this->commandHandlerLocator = $commandHandlerLocator;
        $this->logger = $logger;
    }

    public function process(): void
    {
        $this->processNumberOfMessages($this->maxNumberOfMessagesToProcess);
    }

    public function processAll(): void
    {
        while (($n = $this->commandQueue->count()) > 0) {
            $this->processNumberOfMessages($n);
        }
    }
    
    private function processNumberOfMessages(int $numberOfMessagesToProcess)
    {
        try {
            $messageReceiver = $this;
            $this->commandQueue->consume($messageReceiver, $numberOfMessagesToProcess);
        } catch (\Exception $e) {
            $this->logger->log(new FailedToReadFromCommandQueueMessage($e));
        }
    }

    public function receive(Message $message): void
    {
        try {
            $commandHandler = $this->commandHandlerLocator->getHandlerFor($message);
            $commandHandler->process($message);
        } catch (\Exception $e) {
            $this->logger->log(new CommandHandlerFailedMessage($message, $e));
        }
    }
}
