<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use LizardsAndPumpkins\Messaging\Queue\QueueMessageConsumer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandConsumer
 * @uses   \LizardsAndPumpkins\Messaging\Command\Logging\Message\CommandHandlerFailedMessage
 * @uses   \LizardsAndPumpkins\Messaging\Command\Logging\Message\FailedToReadFromCommandQueueMessage
 */
class CommandConsumerTest extends TestCase
{
    /**
     * @var Queue
     */
    private $mockQueue;

    /**
     * @var CommandHandlerLocator
     */
    private $mockLocator;

    /**
     * @var Logger
     */
    private $mockLogger;

    /**
     * @var CommandConsumer
     */
    private $commandConsumer;

    final protected function setUp(): void
    {
        $this->mockQueue = $this->createMock(Queue::class);
        $this->mockLocator = $this->createMock(CommandHandlerLocator::class);
        $this->mockLogger = $this->createMock(Logger::class);

        $this->commandConsumer = new CommandConsumer($this->mockQueue, $this->mockLocator, $this->mockLogger);
    }

    public function testItIsAQueueMessageConsumer(): void
    {
        $this->assertInstanceOf(QueueMessageConsumer::class, $this->commandConsumer);
    }

    public function testConsumesMessagesFromQueue(): void
    {
        $this->mockQueue->expects($this->once())->method('consume')->with($this->commandConsumer);

        $this->commandConsumer->process();
    }

    public function testCallsConsumeWithTheNumberOdMessagesOnTheQueue()
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(2, 0);
        $this->mockQueue->expects($this->once())->method('consume')->with($this->commandConsumer, 2);
        $this->commandConsumer->processAll();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure(): void
    {
        $this->mockQueue->expects($this->once())->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->process();
    }

    public function testLogEntryIsWrittenOnQueueReadFailureDuringProcessAll(): void
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockQueue->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->processAll();
    }

    public function testDelegatesProcessingToLocatedCommandHandler(): void
    {
        $mockCommandHandler = $this->createMock(CommandHandler::class);
        $mockCommandHandler->expects($this->once())->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($mockCommandHandler);

        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->commandConsumer->process();
    }

    public function testLogsExceptionIfCommandHandlerIsNotFound(): void
    {
        $this->mockLogger->expects($this->once())->method('log');

        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindCommandHandlerException);

        $this->commandConsumer->process();
    }
}
