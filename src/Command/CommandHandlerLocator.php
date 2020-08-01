<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CommandHandlerLocator
{
    /**
     * @var CommandHandler[]
     */
    private $handlers = [];

    public function register(string $commandCode, CommandHandler $handler): void
    {
        $this->handlers[$commandCode] = $handler;
    }

    public function getHandlerFor(Message $command): CommandHandler
    {
        if (! array_key_exists($command->getName(), $this->handlers)) {
            throw new UnableToFindCommandHandlerException(
                sprintf('Unable to find a handler for "%s" command', $command->getName())
            );
        }

        return $this->handlers[$command->getName()];
    }
}
