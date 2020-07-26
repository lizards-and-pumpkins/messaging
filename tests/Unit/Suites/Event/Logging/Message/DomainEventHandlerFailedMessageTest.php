<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event\Logging\Message;

use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\Logging\Message\DomainEventHandlerFailedMessage
 */
class DomainEventHandlerFailedMessageTest extends TestCase
{
    /**
     * @var \Exception
     */
    private $testException;

    /**
     * @var DomainEventHandlerFailedMessage
     */
    private $message;

    /**
     * @var string
     */
    private $exceptionMessage = 'foo';

    final protected function setUp(): void
    {
        /** @var Message|MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('test_foo_domain_event');

        $this->testException = new \Exception($this->exceptionMessage);

        $this->message = new DomainEventHandlerFailedMessage($stubDomainEvent, $this->testException);
    }

    public function testLogMessageIsReturned(): void
    {
        $expectation = sprintf(
            "Failure during processing domain event \"test_foo_domain_event\" with following message:\n%s",
            $this->exceptionMessage
        );

        $this->assertEquals($expectation, (string) $this->message);
    }

    public function testExceptionContextIsReturned(): void
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->testException], $result);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis(): void
    {
        $synopsis = $this->message->getContextSynopsis();
        $this->assertStringContainsString($this->testException->getFile(), $synopsis);
        $this->assertStringContainsString((string) $this->testException->getLine(), $synopsis);
    }
}
