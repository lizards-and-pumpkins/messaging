<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue\Exception\InvalidMessageMetadataException;

class MessageMetadata
{
    /**
     * @var string[]
     */
    private $metadata;

    /**
     * @param string[] $metadata
     */
    public function __construct(array $metadata)
    {
        $this->validateMetadataKeys($metadata);
        $this->metadata = $metadata;
    }

    /**
     * @param string[] $metadata
     */
    private function validateMetadataKeys(array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            $this->validateKey($key);
            $this->validateValue($value);
        }
    }

    /**
     * @return string[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    private function validateKey(string $key): void
    {
        if ('' === $key) {
            throw new InvalidMessageMetadataException('The message metadata array keys must not be empty');
        }
    }

    /**
     * @param string|int|bool|double $value
     */
    private function validateValue($value): void
    {
        if (! is_string($value) && ! is_int($value) && ! is_bool($value) && ! is_double($value)) {
            throw new InvalidMessageMetadataException(sprintf(
                'The message metadata values may only me strings, booleans, integers or doubles, got %s',
                $this->getType($value)
            ));
        }
    }

    /**
     * @param mixed $var
     * @return string
     */
    private function getType($var): string
    {
        return is_object($var) ?
            get_class($var) :
            gettype($var);
    }
}
