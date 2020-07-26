<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command\Logging\Message;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\Logging\Message\FailedToReadFromCommandQueueMessage
 */
class FailedToReadFromCommandQueueMessageTest extends TestCase
{
    /**
     * @var FailedToReadFromCommandQueueMessage
     */
    private $message;

    /**
     * @var \Exception
     */
    private $testException;

    final protected function setUp(): void
    {
        $this->testException = new \Exception('foo');
        $this->message = new FailedToReadFromCommandQueueMessage($this->testException);
    }

    public function testLogMessageIsReturned(): void
    {
        $result = (string) $this->message;
        $expectation = "Failed to read from command queue message with following exception:\n\nfoo";

        $this->assertEquals($expectation, $result);

    }

    public function testExceptionContextIsReturned(): void
    {
        $result = $this->message->getContext();

        $this->assertSame(['exception' => $this->testException], $result);
    }

    public function testItIncludesTheExceptionFileAndLineInTheSynopsis(): void
    {
        $synopsis = $this->message->getContextSynopsis();
        $this->assertStringContainsString($this->testException->getFile(), $synopsis);
        $this->assertStringContainsString((string) $this->testException->getLine(), $synopsis);
    }
}
