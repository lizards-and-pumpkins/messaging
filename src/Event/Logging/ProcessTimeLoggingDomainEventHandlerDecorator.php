<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event\Logging;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Event\Logging\Message\DomainEventProcessedLogMessage;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ProcessTimeLoggingDomainEventHandlerDecorator implements DomainEventHandler
{
    /**
     * @var DomainEventHandler
     */
    private $component;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(DomainEventHandler $component, Logger $logger)
    {
        $this->component = $component;
        $this->logger = $logger;
    }

    public function process(Message $message): void
    {
        $start = microtime(true);
        $this->component->process($message);
        $this->logger->log(new DomainEventProcessedLogMessage(
            $this->formatMessageString(microtime(true) - $start),
            $this->component
        ));
    }

    private function formatMessageString(float $time): string
    {
        return sprintf('DomainEventHandler::process %s %f', get_class($this->component), $time);
    }
}
