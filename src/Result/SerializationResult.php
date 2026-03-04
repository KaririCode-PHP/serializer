<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Result;

final readonly class SerializationResult
{
    public function __construct(
        public array $originalData,
        public string $payload,
        public string $format,
    ) {}

    public function getPayload(): string { return $this->payload; }
    public function getFormat(): string { return $this->format; }
    public function getOriginalData(): array { return $this->originalData; }
    public function getPayloadSize(): int { return strlen($this->payload); }
}
