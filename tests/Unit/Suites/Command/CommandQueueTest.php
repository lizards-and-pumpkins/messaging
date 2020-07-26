<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class CommandQueueTest extends TestCase
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var AnyInvokedCount
     */
    private $addToQueueSpy;

    private function assertAddedMessageCount(int $expected): void
    {
        $queueMessagesCount = $this->addToQueueSpy->getInvocationCount();
        $message = sprintf('Expected queue message count to be %d, got %d', $expected, $queueMessagesCount);
        $this->assertSame($expected, $queueMessagesCount, $message);
    }

    final protected function setUp(): void
    {
        $this->addToQueueSpy = new AnyInvokedCount();

        $mockQueue = $this->createMock(Queue::class);
        $mockQueue->expects($this->addToQueueSpy)->method('add');

        $this->commandQueue = new CommandQueue($mockQueue);
    }

    public function testAddsCommandsToQueue(): void
    {
        /** @var Command|MockObject $command */
        $command = $this->createMock(Command::class);
        $command->method('toMessage')->willReturn($this->createMock(Message::class));

        $this->commandQueue->add($command);

        $this->assertAddedMessageCount(1);
    }
}
