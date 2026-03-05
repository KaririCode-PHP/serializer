<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Core;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\EncoderRegistry;
use KaririCode\Serializer\Exception\SerializationException;

/**
 * In-memory encoder registry backed by a plain PHP array.
 *
 * Throws SerializationException on duplicate registration or unknown format lookup.
 *
 * @package KaririCode\Serializer\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class InMemoryEncoderRegistry implements EncoderRegistry
{
    /** @var array<string, Encoder> */
    private array $encoders = [];

    #[\Override]
    public function register(Encoder $encoder): void
    {
        $format = $encoder->getFormat();
        if (isset($this->encoders[$format])) {
            throw SerializationException::duplicateEncoder($format);
        }
        $this->encoders[$format] = $encoder;
    }

    #[\Override]
    public function resolve(string $format): Encoder
    {
        return $this->encoders[$format] ?? throw SerializationException::unsupportedFormat($format);
    }

    #[\Override]
    public function has(string $format): bool
    {
        return isset($this->encoders[$format]);
    }

    #[\Override]
    public function formats(): array
    {
        return array_keys($this->encoders);
    }
}
