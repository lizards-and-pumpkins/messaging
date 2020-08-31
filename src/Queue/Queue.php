<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

interface Queue extends \Countable
{
    public function count(): int;

    public function add(Message $message): void;

    public function consume(MessageReceiver $messageReceiver, int $numberOfMessagesToConsume): void;

    public function clear(): void;
}
