<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Logging\Message;

use LizardsAndPumpkins\Messaging\Queue\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Logging\Message\QueueAddLogMessage
 */
class QueueAddLogMessageTest extends TestCase
{
    /**
     * @var Queue|MockObject
     */
    private $stubQueue;

    final protected function setUp(): void
    {
        $this->stubQueue = $this->createMock(Queue::class);
    }
    public function testItUsesTheClassNameForTheStringRepresentationForObjects()
    {
        $expected = sprintf('%s instance added to queue', __CLASS__);
        $this->assertSame($expected, (string) new QueueAddLogMessage($this, $this->stubQueue));
    }

    /**
     * @param array|int|float|string|resource|null $nonObject
     * @param string $expected
     * @dataProvider nonObjectDataProvider
     */
    public function testItUsesTheDataTypeForTheStringRepresentationForNonObjects($nonObject, string $expected): void
    {
        $this->assertSame($expected, (string) new QueueAddLogMessage($nonObject, $this->stubQueue));
    }

    /**
     * @return array[]
     */
    public function nonObjectDataProvider() : array
    {
        return [
            [[], 'Array added to queue'],
            [2, 'Integer added to queue'],
            [0.9, 'Double added to queue'],
            ['foo', 'String added to queue'],
            [fopen(__FILE__, 'r'), 'Resource added to queue'],
            [null, 'NULL added to queue']
        ];
    }

    public function testTheQueueIsPartOfTheMessageContext(): void
    {
        $logMessage = new QueueAddLogMessage(new \stdClass(), $this->stubQueue);
        $this->assertArrayHasKey('queue', $logMessage->getContext());
        $this->assertSame($this->stubQueue, $logMessage->getContext()['queue']);
    }

    public function testTheAddedDataIsPartOfTheMessageContext(): void
    {
        $logMessage = new QueueAddLogMessage($this, $this->stubQueue);
        $this->assertArrayHasKey('data', $logMessage->getContext());
        $this->assertSame($this, $logMessage->getContext()['data']);
    }

    public function testTheContextSynopsisIncludesTheQueueClassName()
    {
        $logMessage = new QueueAddLogMessage($this, $this->stubQueue);
        $this->assertStringContainsString(get_class($this->stubQueue), $logMessage->getContextSynopsis());
    }
}
