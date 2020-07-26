<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event\Logging;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Command\Logging\ProcessTimeLoggingCommandHandlerDecorator;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\Logging\ProcessTimeLoggingCommandHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Command\Logging\Message\CommandProcessedLogMessage
 */
class ProcessTimeLoggingCommandHandlerDecoratorTest extends TestCase
{
    /**
     * @var ProcessTimeLoggingCommandHandlerDecorator
     */
    private $handlerDecorator;

    /**
     * @var Logger|MockObject
     */
    private $mockLogger;

    /**
     * @var CommandHandler|MockObject
     */
    private $mockDecoratedCommandHandler;

    /**
     * @var Message|MockObject
     */
    private $dummyMessage;

    final protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(Logger::class);
        $this->mockDecoratedCommandHandler = $this->createMock(CommandHandler::class);
        $this->handlerDecorator = new ProcessTimeLoggingCommandHandlerDecorator(
            $this->mockDecoratedCommandHandler,
            $this->mockLogger
        );
        $this->dummyMessage = $this->createMock(Message::class);
    }

    public function testItIsACommandHandler(): void
    {
        $this->assertInstanceOf(CommandHandler::class, $this->handlerDecorator);
    }

    public function testItDelegatesToTheDecoratedSubjectForProcessing(): void
    {
        $this->mockDecoratedCommandHandler->expects($this->once())->method('process');
        $this->handlerDecorator->process($this->dummyMessage);
    }

    public function testItLogsEachCallToProcess(): void
    {
        $this->mockLogger->expects($this->once())->method('log');
        $this->handlerDecorator->process($this->dummyMessage);
    }

    public function testTheMessageFormat(): void
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->willReturnCallback(function (LogMessage $logMessage) {
                if (!preg_match('/^CommandHandler::process [a-z0-9_\\\]+ \d+\.\d+/i', (string)$logMessage)) {
                    $message = sprintf('%s unexpected message format, got "%s"', get_class($logMessage), $logMessage);
                    $this->fail($message);
                }
            });

        $this->handlerDecorator->process($this->dummyMessage);
    }
}
