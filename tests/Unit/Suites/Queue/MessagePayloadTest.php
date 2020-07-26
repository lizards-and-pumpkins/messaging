<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessagePayloadException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class MessagePayloadTest extends TestCase
{
    /**
     * @dataProvider messagePayloadDataProvider
     * @param string[] $testPayload
     */
    public function testItReturnsTheGivenPayload(array $testPayload): void
    {
        $this->assertSame($testPayload, (new MessagePayload($testPayload))->getPayload());
    }

    /**
     * @return array[]
     */
    public function messagePayloadDataProvider(): array
    {
        return [
            [[]],
            [['foo' => 'bar']],
        ];
    }

    /**
     * @param array[] $invalidPayload
     * @param string $expectedType
     * @param string $expectedPath
     * @dataProvider invalidPayloadProvider
     */
    public function testThrowsExceptionIfPayloadContainsNonScalarValues(
        array $invalidPayload,
        string $expectedType,
        string $expectedPath
    ): void {
        $this->expectException(InvalidQueueMessagePayloadException::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid message payload data type found at "%s": %s (must be string, int, float or boolean)',
            $expectedPath,
            $expectedType
        ));

        new MessagePayload($invalidPayload);
    }

    /**
     * @return array[]
     */
    public function invalidPayloadProvider(): array
    {
        return [
            [['foo' => $this], get_class($this), '/foo'],
            [['bar' => fopen(__FILE__, 'r')], 'resource', '/bar'],
            [['baz' => null], 'NULL', '/baz'],
            [['sub' => ['qux' => null]], 'NULL', '/sub/qux'],
        ];
    }
}
