<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

interface QueueMessageConsumer
{
    public function process(): void;

    public function processAll(): void;
}
