<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Event;

/**
 * Event emitted when a serialization operation begins.
 *
 * @package KaririCode\Serializer\Event
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class SerializationStartedEvent
{
    public function __construct(public string $format, public float $timestamp = 0)
    {
    }
}
