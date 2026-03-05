<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Event;

use KaririCode\Serializer\Result\SerializationResult;

/**
 * Event emitted when a serialization operation completes successfully.
 *
 * @package KaririCode\Serializer\Event
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class SerializationCompletedEvent
{
    public function __construct(public SerializationResult $result, public float $durationMs, public float $timestamp = 0)
    {
    }
}
