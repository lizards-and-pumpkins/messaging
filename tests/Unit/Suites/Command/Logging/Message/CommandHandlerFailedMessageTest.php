<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command\Logging\Message;

use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\Logging\Message\CommandHandlerFailedMessage
 */
class CommandHandlerFailedMessageTest extends TestCase
{
    /**
     * @var \Exception
     */
    private $testException;

    /**
     * @var CommandHandlerFailedMessage
     */
    private $message;

    /**
     * @var string
     */
    private $exceptionMessage = 'foo';

    final protected function setUp(): void
    {
        /** @var Message|MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn('test_command');

        $this->testException = new \Exception($this->exceptionMessage);

        $this->message = new CommandHandlerFailedMessage($stubCommand, $this->testException);
    }

    public function testLogMessageIsReturned(): void
    {
        $expectation = sprintf(
            "Failure during processing test_command command with following message:\n\n%s",
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
