<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidQueueMessageNameException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class MessageNameTest extends TestCase
{
    /**
     * @dataProvider emptyMessageNameProvider
     * @param string $emptyMessageName
     */
    public function testThrowsExceptionIfEmpty(string $emptyMessageName): void
    {
        $this->expectException(InvalidQueueMessageNameException::class);
        $this->expectExceptionMessage('The message name must not be empty');
        new MessageName($emptyMessageName);
    }

    /**
     * @return array[]
     */
    public function emptyMessageNameProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    public function testCanBeCastToString(): void
    {
        $name = 'foo-bar';
        $this->assertSame($name, (string) new MessageName($name));
    }
}
