<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command\Logging\Message;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\Logging\Message\CommandProcessedLogMessage
 */
class CommandProcessedLogMessageTest extends TestCase
{
    /**
     * @var CommandHandler|MockObject
     */
    private $mockCommandHandler;

    private function createMessageInstance(string $message) : CommandProcessedLogMessage
    {
        return new CommandProcessedLogMessage($message, $this->mockCommandHandler);
    }

    final protected function setUp(): void
    {
        $this->mockCommandHandler = $this->createMock(CommandHandler::class);
    }

    public function testItIsALogMessage(): void
    {
        $this->assertInstanceOf(LogMessage::class, $this->createMessageInstance('Test Message'));
    }

    public function testItReturnsTheMessage(): void
    {
        $this->assertSame('Test Message', (string)$this->createMessageInstance('Test Message'));
    }

    public function testItReturnsTheLoggedCommandHandlerAsPartOfTheMessageContext(): void
    {
        $message = $this->createMessageInstance('foo');
        $this->assertArrayHasKey('command_handler', $message->getContext());
        $this->assertSame($this->mockCommandHandler, $message->getContext()['command_handler']);
    }

    public function testItAddsTheCommandHandlerClassToTheContextSynopsis(): void
    {
        $message = $this->createMessageInstance('Test Message');
        $this->assertStringContainsString(get_class($this->mockCommandHandler), $message->getContextSynopsis());
    }
}
