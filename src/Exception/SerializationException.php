<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Exception;

/**
 * Domain exception for all serialization and deserialization failures.
 *
 * Uses static factory methods for type-safe construction.
 *
 * @package KaririCode\Serializer\Exception
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class SerializationException extends \RuntimeException
{
    public static function encodingFailed(string $format, string $reason): self
    {
        return new self("Failed to encode to '{$format}': {$reason}");
    }

    public static function decodingFailed(string $format, string $reason): self
    {
        return new self("Failed to decode from '{$format}': {$reason}");
    }

    public static function unsupportedFormat(string $format): self
    {
        return new self("No encoder registered for format '{$format}'.");
    }

    public static function duplicateEncoder(string $format): self
    {
        return new self("Encoder for format '{$format}' is already registered.");
    }
}
