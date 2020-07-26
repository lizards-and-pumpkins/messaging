<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class MessageTest extends TestCase
{
    public function testReturnsTheInjectedTimestamp(): void
    {
        $date = '2016-05-18 06:00:00';
        $message = Message::withGivenTime('foo name', ['bar' =>  'payload'], [], new \DateTimeImmutable($date));
        $this->assertSame((new \DateTimeImmutable($date))->getTimestamp(), $message->getTimestamp());
    }

    public function testReturnsTheMessageName(): void
    {
        $name = 'foo';
        $message = Message::withCurrentTime($name, ['baz' => 'payload'], []);
        $this->assertSame($name, $message->getName());
    }

    public function testValidatesTheMessageName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Message::withCurrentTime('', ['qux' => 'payload'], ['metadata']);
    }

    public function testReturnsThePayload(): void
    {
        $payload = ['bar' => 'baz'];
        $message = Message::withCurrentTime('foo name', $payload, []);
        $this->assertSame($payload, $message->getPayload());
    }

    public function testReturnsTheMetadata(): void
    {
        $metadata = ['data_version' => 'foo-bar'];
        $message = Message::withCurrentTime('foo name', ['bar' => 'payload'], $metadata);
        $this->assertSame($metadata, $message->getMetadata());
    }

    public function testItValidatesTheMetadata(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Message::withCurrentTime('foo name', ['bar' => 'payload'], ['' => $this]);
    }

    public function testCanBeInstantiatedWithoutInjectingTheCurrentDateTime(): void
    {
        $startTime = time();
        $message = Message::withCurrentTime('some.name', ['some' => 'payload'], ['some' => 'metadata']);
        $this->assertInstanceOf(Message::class, $message);
        $isCurrentTime = $message->getTimestamp() === $startTime || $message->getTimestamp() === $startTime + 1;
        $this->assertTrue($isCurrentTime, 'The message was not instantiated for the current datetime');
    }

    public function testCanBeInstantiatedWithGivenTime(): void
    {
        $time = new \DateTimeImmutable('2016-05-18 06:00:00');
        $message = Message::withGivenTime('some.name', ['some' => 'payload'], ['some' => 'metadata'], $time);
        $this->assertSame($time->getTimestamp(), $message->getTimestamp());
    }

    public function testItCanBeJsonSerializedAndRehydrated(): void
    {
        $source = Message::withCurrentTime('foo', ['bar' => 'foo'], ['baz' => 'qux']);
        $rehydrated = Message::rehydrate($source->serialize());

        $this->assertInstanceOf(Message::class, $rehydrated);
        $this->assertSame($source->getName(), $rehydrated->getName());
        $this->assertSame($source->getPayload(), $rehydrated->getPayload());
        $this->assertSame($source->getMetadata(), $rehydrated->getMetadata());
        $this->assertSame($source->getTimestamp(), $rehydrated->getTimestamp());
    }
}
