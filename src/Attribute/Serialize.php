<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Attribute;

/**
 * Marks a property for serialization control.
 *
 * Parameters: name (wire-name override), groups (string[]), format-specific options.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class Serialize
{
    /** @param list<string> $groups */
    public function __construct(
        public ?string $name = null,
        public array $groups = [],
        public bool $ignore = false,
    ) {}
}
