<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Configuration;

/**
 * Immutable configuration value object for the serializer engine.
 *
 * @package KaririCode\Serializer\Configuration
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class SerializerConfiguration
{
    public function __construct(
        public string $defaultFormat = 'json',
        public bool $prettyPrint = false,
    ) {
    }
}
