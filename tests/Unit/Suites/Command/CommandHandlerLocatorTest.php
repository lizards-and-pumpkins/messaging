<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
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

    final protected function setUp(): void
    {
        $this->locator = new CommandHandlerLocator();
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated(): void
    {
        $commandCode = 'non_existing_foo';

        /** @var Message|MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn($commandCode);

        $this->expectException(UnableToFindCommandHandlerException::class);
        $this->expectExceptionMessage(sprintf('Unable to find a handler for "%s" command', $commandCode));

        $this->locator->getHandlerFor($stubCommand);
    }

    public function testReturnsCommandHandler(): void
    {
        $commandCode = 'foo';

        $dummyCommandHandler = $this->createMock(CommandHandler::class);

        /** @var Message|MockObject $stubCommand */
        $stubCommand = $this->createMock(Message::class);
        $stubCommand->method('getName')->willReturn($commandCode);

        $this->locator->register($commandCode, $dummyCommandHandler);
        $result = $this->locator->getHandlerFor($stubCommand);

        $this->assertInstanceOf(CommandHandler::class, $result);
    }
}
