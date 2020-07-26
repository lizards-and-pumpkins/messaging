<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer\Logging\Message;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Consumer\Logging\Message\ConsumerShutdownRequestedLogMessage
 */
class ConsumerShutdownRequestedLogMessageTest extends TestCase
{
    public function testIsALogMessage(): void
    {
        $dummyDirective = $this->createMock(ShutdownWorkerDirective::class);
        $this->assertInstanceOf(LogMessage::class, new ConsumerShutdownRequestedLogMessage(getmypid(), $dummyDirective));
    }

    public function testFormatsMessageWithPid(): void
    {
        $currentPid = 555;
        $dummyDirective = $this->createMock(ShutdownWorkerDirective::class);
        $expected = sprintf('Shutting down consumer PID %s', $currentPid);
        $this->assertEquals($expected, new ConsumerShutdownRequestedLogMessage($currentPid, $dummyDirective));
    }

    public function testReturnsContextWithCurrentPidAndDirective(): void
    {
        $currentPid = 555;
        $dummyDirective = $this->createMock(ShutdownWorkerDirective::class);
        $logMessage = new ConsumerShutdownRequestedLogMessage($currentPid, $dummyDirective);
        $expected = [
            'current_pid' => $currentPid,
            'shutdown_directive' => $dummyDirective,
        ];
        $this->assertSame($expected, $logMessage->getContext());
    }

    public function testReturnsContextSynopsisWithPidAndDirectivePattern(): void
    {
        $currentPid = 555;
        $stubDirective = $this->createMock(ShutdownWorkerDirective::class);
        $stubDirective->method('getPid')->willReturn('*');
        $logMessage = new ConsumerShutdownRequestedLogMessage($currentPid, $stubDirective);
        $expected = 'Shutdown Directive PID: *, Consumer Process PID: 555';
        $this->assertEquals($expected, $logMessage->getContextSynopsis());
    }
}
