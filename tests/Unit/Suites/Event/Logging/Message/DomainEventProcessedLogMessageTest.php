<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event\Logging\Message;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\Logging\Message\DomainEventProcessedLogMessage
 */
class DomainEventProcessedLogMessageTest extends TestCase
{
    private $testMessage = 'Test message';
    
    /**
     * @var DomainEventProcessedLogMessage
     */
    private $logMessage;

    /**
     * @var DomainEventHandler
     */
    private $stubDomainEventHandler;

    final protected function setUp(): void
    {
        $this->stubDomainEventHandler = $this->createMock(DomainEventHandler::class);
        $this->logMessage = new DomainEventProcessedLogMessage($this->testMessage, $this->stubDomainEventHandler);
    }

    public function testItReturnsTheGivenString(): void
    {
        $this->assertSame($this->testMessage, (string) $this->logMessage);
    }

    public function testTheDomainEventHandlerIsPartOfTheContext(): void
    {
        $this->assertIsArray($this->logMessage->getContext());
        $this->assertArrayHasKey('domain_event_handler', $this->logMessage->getContext());
        $this->assertSame($this->stubDomainEventHandler, $this->logMessage->getContext()['domain_event_handler']);
    }

    public function testItIncludesTheDomainEventHandlerClassNameInTheSynopsis(): void
    {
        $this->assertStringContainsString(
            get_class($this->stubDomainEventHandler),
            $this->logMessage->getContextSynopsis()
        );
    }
}
