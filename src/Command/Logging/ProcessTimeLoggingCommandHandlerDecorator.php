<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command\Logging;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Command\Logging\Message\CommandProcessedLogMessage;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ProcessTimeLoggingCommandHandlerDecorator implements CommandHandler
{
    /**
     * @var CommandHandler
     */
    private $decoratedCommandHandler;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(CommandHandler $decoratedCommandHandler, Logger $logger)
    {
        $this->decoratedCommandHandler = $decoratedCommandHandler;
        $this->logger = $logger;
    }
    
    public function process(Message $message): void
    {
        $startTime = microtime(true);
        $this->decoratedCommandHandler->process($message);
        $processTime = microtime(true) - $startTime;
        $msg = sprintf('CommandHandler::process %s %f', get_class($this->decoratedCommandHandler), $processTime);
        $this->logger->log(new CommandProcessedLogMessage($msg, $this));
    }
}
