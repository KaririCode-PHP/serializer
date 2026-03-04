<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Event;

use KaririCode\Serializer\Result\SerializationResult;

final readonly class SerializationCompletedEvent
{
    public function __construct(public SerializationResult $result, public float $durationMs, public float $timestamp = 0) {}
}
