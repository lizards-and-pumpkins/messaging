<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandHandlerLocator
 */
class CommandHandlerLocatorTest extends TestCase
{
    /**
     * @var CommandHandlerLocator
     */
    private $locator;

    /**
     * @var MasterFactory
     */
    private $factory;

    final protected function setUp(): void
    {
        $this->factory = $this->getMockBuilder(MasterFactory::class)
            ->onlyMethods(get_class_methods(MasterFactory::class))
            ->addMethods(['createFooCommandHandler'])
            ->getMock();

        $this->locator = new CommandHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated(): void
    {
        /** @var Message|MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn('non_existing_foo');

        $this->expectException(UnableToFindCommandHandlerException::class);

        $this->locator->getHandlerFor($stubCommand);
    }

    public function testReturnsCommandHandler(): void
    {
        $stubHandler = $this->createMock(CommandHandler::class);

        $this->factory->expects($this->once())
            ->method('createFooCommandHandler')
            ->willReturn($stubHandler);

        /** @var Message|MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn('foo');

        $result = $this->locator->getHandlerFor($stubCommand);

        $this->assertInstanceOf(CommandHandler::class, $result);
    }
}
