<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use LizardsAndPumpkins\Messaging\Queue\QueueMessageConsumer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventConsumer
 * @uses   \LizardsAndPumpkins\Messaging\Event\Logging\Message\DomainEventHandlerFailedMessage
 * @uses   \LizardsAndPumpkins\Messaging\Event\Logging\Message\FailedToReadFromDomainEventQueueMessage
 */
class DomainEventConsumerTest extends TestCase
{
    /**
     * @var DomainEventConsumer
     */
    private $domainEventConsumer;

    /**
     * @var Logger|MockObject
     */
    private $mockLogger;

    /**
     * @var Queue|MockObject
     */
    private $mockQueue;

    /**
     * @var DomainEventHandlerLocator|MockObject
     */
    private $mockLocator;

    final protected function setUp(): void
    {
        $this->mockQueue = $this->createMock(Queue::class);
        $this->mockLocator = $this->createMock(DomainEventHandlerLocator::class);
        $this->mockLogger = $this->createMock(Logger::class);

        $this->domainEventConsumer = new DomainEventConsumer($this->mockQueue, $this->mockLocator, $this->mockLogger);
    }

    public function testItIsAQueueMessageConsumer(): void
    {
        $this->assertInstanceOf(QueueMessageConsumer::class, $this->domainEventConsumer);
    }

    public function testConsumesMessagesFromQueue(): void
    {
        $this->mockQueue->expects($this->once())->method('consume')->with($this->domainEventConsumer);

        $this->domainEventConsumer->process();
    }

    public function testCallsConsumeWithTheNumberOdMessagesOnTheQueue(): void
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(2, 0);
        $this->mockQueue->expects($this->once())->method('consume')->with($this->domainEventConsumer, 2);
        $this->domainEventConsumer->processAll();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure(): void
    {
        $this->mockQueue->expects($this->once())->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->process();
    }
    
    public function testLogEntryIsWrittenOnQueueReadFailureDuringProcessAll(): void
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockQueue->expects($this->once())->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->processAll();
    }

    public function testDelegatesProcessingToLocatedEventHandler(): void
    {
        $mockEventHandler = $this->createMock(DomainEventHandler::class);
        $mockEventHandler->expects($this->once())->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($mockEventHandler);

        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->domainEventConsumer->process();
    }

    public function testLogsExceptionIfEventHandlerIsNotFound(): void
    {
        $this->mockLogger->expects($this->once())->method('log');

        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindDomainEventHandlerException());

        $this->domainEventConsumer->process();
    }
}
