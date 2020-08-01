<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator
 */
class DomainEventHandlerLocatorTest extends TestCase
{
    /**
     * @var DomainEventHandlerLocator
     */
    private $locator;

    final protected function setUp(): void
    {
        $this->locator = new DomainEventHandlerLocator();
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated(): void
    {
        $eventCode = 'non_existing_domain_event';

        /** @var Message|MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn($eventCode);

        $this->expectException(UnableToFindDomainEventHandlerException::class);
        $this->expectExceptionMessage(sprintf('Unable to find a handler for "%s" event', $eventCode));

        $this->locator->getHandlerFor($stubDomainEvent);
    }

    public function testReturnsDomainEventHandler(): void
    {
        $eventCode = 'foo';

        $dummyEventHandler = $this->createMock(DomainEventHandler::class);

        /** @var Message|MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn($eventCode);

        $this->locator->register($eventCode, $dummyEventHandler);
        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertSame($dummyEventHandler, $result);
    }
}
