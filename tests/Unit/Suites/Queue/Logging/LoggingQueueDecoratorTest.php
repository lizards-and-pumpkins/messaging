<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Logging;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Queue\Logging\Message\QueueAddLogMessage;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Logging\Message\QueueAddLogMessage
 */
class LoggingQueueDecoratorTest extends TestCase
{
    /**
     * @var LoggingQueueDecorator;
     */
    private $decorator;

    /**
     * @var Queue|MockObject
     */
    private $decoratedQueue;

    /**
     * @var Logger|MockObject
     */
    private $mockLogger;

    /**
     * @return Message|MockObject
     */
    private function createMockMessage(): Message
    {
        return $this->createMock(Message::class);
    }

    final protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(Logger::class);
        $this->decoratedQueue = $this->createMock(Queue::class);
        $this->decorator = new LoggingQueueDecorator($this->decoratedQueue, $this->mockLogger);
    }

    public function testItImplementsTheQueueInterface(): void
    {
        $this->assertInstanceOf(Queue::class, $this->decorator);
    }

    public function testItDelegatesCountCallsToTheDecoratedQueue(): void
    {
        $expected = 42;
        $this->decoratedQueue->expects($this->once())->method('count')->willReturn($expected);
        $this->assertSame($expected, $this->decorator->count());
    }

    public function testItDelegatesAddCallsToTheDecoratedQueue(): void
    {
        $testMessage = $this->createMockMessage();
        $this->decoratedQueue->expects($this->once())->method('add')->with($testMessage);
        $this->decorator->add($testMessage);
    }

    public function testItDelegatesConsumeCallsToTheDecoratedQueue(): void
    {
        /** @var MessageReceiver|MockObject $stubMessageReceiver */
        $stubMessageReceiver = $this->createMock(MessageReceiver::class);
        $maxNumberOfMessagesToConsume = 1;
        $this->decoratedQueue->expects($this->once())->method('consume')
            ->with($stubMessageReceiver, $maxNumberOfMessagesToConsume);
        $this->decorator->consume($stubMessageReceiver, $maxNumberOfMessagesToConsume);
    }

    public function testItLogsAddedMessages(): void
    {
        $testData = $this->createMockMessage();
        $this->mockLogger->expects($this->once())->method('log')->with($this->isInstanceOf(QueueAddLogMessage::class));
        $this->decorator->add($testData);
    }

    public function testItDelegatesClearCallsToTheDecoratedQueue()
    {
        $this->decoratedQueue->expects($this->once())->method('clear');
        $this->decorator->clear();
    }
}
