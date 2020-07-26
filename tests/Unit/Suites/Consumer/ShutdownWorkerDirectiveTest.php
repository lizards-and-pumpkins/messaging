<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Consumer\Exception\InvalidMessageConsumerPidException;
use LizardsAndPumpkins\Messaging\Consumer\Exception\NotShutdownWorkerDirectiveMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerDirectiveTest extends TestCase
{
    public function testImplementsCommandAndEventInterface(): void
    {
        $this->assertInstanceOf(Command::class, new ShutdownWorkerDirective('*'));
        $this->assertInstanceOf(DomainEvent::class, new ShutdownWorkerDirective('*'));
    }
    
    public function testReturnsMessageWithShutdownWorkerNameAndPayload(): void
    {
        $message = (new ShutdownWorkerDirective('123'))->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ShutdownWorkerDirective::CODE, $message->getName());
        $this->assertSame('123', $message->getPayload()['pid']);
    }

    public function testReturnsMessageWithSpecifiedRetryCount(): void
    {
        $this->assertSame(0, (new ShutdownWorkerDirective('123'))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(1, (new ShutdownWorkerDirective('123', 1))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(2, (new ShutdownWorkerDirective('123', 2))->toMessage()->getPayload()['retry_count']);
    }

    public function testThrowsExceptionIfMessageCodeDoesNotMatchShutdownWorkerCode(): void
    {
        $this->expectException(NotShutdownWorkerDirectiveMessageException::class);
        $message = 'Unable to rehydrate from "foo" queue message, expected "shutdown_worker"';
        $this->expectExceptionMessage($message);

        ShutdownWorkerDirective::fromMessage(Message::withCurrentTime('foo', [], []));
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $testPid = '2233';
        $testRetryCount = 42;
        $message = (new ShutdownWorkerDirective($testPid, $testRetryCount))->toMessage();

        $rehydratedCommand = ShutdownWorkerDirective::fromMessage($message);

        $this->assertInstanceOf(ShutdownWorkerDirective::class, $rehydratedCommand);
        $this->assertSame($testPid, $rehydratedCommand->getPid());
        $this->assertSame($testRetryCount, $rehydratedCommand->getRetryCount());
    }

    /**
     * @dataProvider invalidConsumerPidProvider
     * @param string $invalidConsumerPid
     */
    public function testThrowsExceptionIfPidIsInvalid(string $invalidConsumerPid): void
    {
        $this->expectException(InvalidMessageConsumerPidException::class);
        $message = sprintf('The consumer PID has to be digits or "*" for any, got "%s"', $invalidConsumerPid);
        $this->expectExceptionMessage($message);
        new ShutdownWorkerDirective($invalidConsumerPid);
    }

    /**
     * @return array[]
     */
    public function invalidConsumerPidProvider(): array
    {
        return [
            [''],
            ['abc'],
            ['_'],
            ['%'],
            ['.'],
            ['1^2'],
            [' 1'],
            ['1 '],
            ['0'],
        ];
    }

    public function testRetriesWithIncreasedRetryCount(): void
    {
        $directive0 = new ShutdownWorkerDirective('111');
        $directive1 = $directive0->retry();
        $directive2 = $directive1->retry();
        $this->assertSame(0, $directive0->getRetryCount());
        $this->assertSame(1, $directive1->getRetryCount());
        $this->assertSame(2, $directive2->getRetryCount());
    }
}
