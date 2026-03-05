<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Event;

final readonly class SerializationStartedEvent
{
    public function __construct(public string $format, public float $timestamp = 0)
    {
    }
}
