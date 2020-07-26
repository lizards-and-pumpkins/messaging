<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
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

    /**
     * @var MasterFactory|MockObject
     */
    private $factory;

    final protected function setUp(): void
    {
        $this->factory = $this->getMockBuilder(MasterFactory::class)
            ->onlyMethods(get_class_methods(MasterFactory::class))
            ->addMethods(['createFooDomainEventHandler'])
            ->getMock();

        $this->locator = new DomainEventHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated(): void
    {
        /** @var Message|MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('non_existing_domain_event');

        $this->expectException(UnableToFindDomainEventHandlerException::class);

        $this->locator->getHandlerFor($stubDomainEvent);
    }

    public function testProductWasUpdatedDomainEventHandlerIsLocatedAndReturned(): void
    {
        $stubEventHandler = $this->createMock(DomainEventHandler::class);
        $this->factory->method('createFooDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('foo');

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(DomainEventHandler::class, $result);
    }
}
