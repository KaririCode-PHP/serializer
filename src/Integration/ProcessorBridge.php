<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Integration;

use KaririCode\Serializer\Core\SerializerEngine;
use KaririCode\Serializer\Result\SerializationResult;

/**
 * Bridges the processor-pipeline to the serializer engine.
 *
 * Adapts the generic Pipeline interface to format-specific serialization output.
 *
 * @package KaririCode\Serializer\Integration
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class ProcessorBridge
{
    public function __construct(
        private SerializerEngine $engine,
        private string $format = 'json',
    ) {
    }

    /** @param array<string, mixed> $data */
    public function process(array $data): SerializationResult
    {
        return $this->engine->serialize($data, $this->format);
    }
}
