<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\Logging\ProcessTimeLoggingDomainEventHandlerDecorator;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\Logging\ProcessTimeLoggingDomainEventHandlerDecorator
 * @uses   \LizardsAndPumpkins\Messaging\Event\Logging\Message\DomainEventProcessedLogMessage
 */
class ProcessTimeLoggingDomainEventHandlerDecoratorTest extends TestCase
{
    /**
     * @var DomainEventHandler|MockObject
     */
    private $mockDecoratedEventHandler;

    /**
     * @var Logger|MockObject
     */
    private $mockLogger;

    /**
     * @var ProcessTimeLoggingDomainEventHandlerDecorator;
     */
    private $decorator;

    /**
     * @var Message
     */
    private $dummyMessage;

    final protected function setUp(): void
    {
        $this->dummyMessage = $this->createMock(Message::class);
        $this->mockDecoratedEventHandler = $this->createMock(DomainEventHandler::class);
        $this->mockLogger = $this->createMock(Logger::class);
        $this->decorator = new ProcessTimeLoggingDomainEventHandlerDecorator(
            $this->mockDecoratedEventHandler,
            $this->mockLogger
        );
    }

    public function testItImplementsDomainEventHandler(): void
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->decorator);
    }

    public function testItDelegatesProcessingToComponent(): void
    {
        $this->mockDecoratedEventHandler->expects($this->once())->method('process');
        $this->decorator->process($this->dummyMessage);
    }

    public function testItLogsEachCallToProcess(): void
    {
        $this->mockLogger->expects($this->once())->method('log');
        $this->decorator->process($this->dummyMessage);
    }

    public function testTheMessageFormat(): void
    {
        $this->mockLogger->expects($this->once())->method('log')
            ->willReturnCallback(function (LogMessage $logMessage) {
                if (!preg_match('/^DomainEventHandler::process [a-z0-9_\\\]+ \d+\.\d+/i', (string)$logMessage)) {
                    $message = sprintf('%s unexpected message format, got "%s"', get_class($logMessage), $logMessage);
                    $this->fail($message);
                }
            });
        $this->decorator->process($this->dummyMessage);
    }
}
