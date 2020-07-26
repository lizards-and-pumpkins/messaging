<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Stub\TestDomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Queue;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class DomainEventQueueTest extends TestCase
{
    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

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

        $this->eventQueue = new DomainEventQueue($mockQueue);
    }

    public function testAddsDomainEventToMessageQueue(): void
    {
        $this->eventQueue->add(new TestDomainEvent());
        $this->assertAddedMessageCount(1);
    }
}
