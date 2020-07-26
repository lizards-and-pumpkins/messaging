<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Core\Factory\MasterFactory;
use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CommandHandlerLocator
{
    /**
     * @var MasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getHandlerFor(Message $command): CommandHandler
    {
        $commandHandlerClass = $this->getUnqualifiedCommandClassName($command);
        $method = 'create' . $commandHandlerClass;

        if (! method_exists($this->factory, $method)) {
            throw new UnableToFindCommandHandlerException(
                sprintf('Unable to find a handler "%s" for command "%s"', $commandHandlerClass, $command->getName())
            );
        }

        return $this->factory->{$method}($command);
    }

    private function getUnqualifiedCommandClassName(Message $event): string
    {
        $camelCaseEventName = $this->snakeCaseToCamelCase($event->getName() . '_command');

        return $camelCaseEventName . 'Handler';
    }

    private function snakeCaseToCamelCase(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
