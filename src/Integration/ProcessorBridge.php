<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Integration;

use KaririCode\Serializer\Core\SerializerEngine;
use KaririCode\Serializer\Result\SerializationResult;

final readonly class ProcessorBridge
{
    public function __construct(
        private SerializerEngine $engine,
        private string $format = 'json',
    ) {}

    /** @param array<string, mixed> $data */
    public function process(array $data): SerializationResult
    {
        return $this->engine->serialize($data, $this->format);
    }
}
