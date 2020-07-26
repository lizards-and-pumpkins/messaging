<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

interface MessageReceiver
{
    public function receive(Message $message): void;
}
