<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Result;

/**
 * Immutable result of a serialization pass.
 *
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class SerializationResult
{
    /**
     * @param array<string, mixed> $originalData
     */
    public function __construct(
        public array $originalData,
        public string $payload,
        public string $format,
    ) {
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /** @return array<string, mixed> */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    public function getPayloadSize(): int
    {
        return \strlen($this->payload);
    }
}
