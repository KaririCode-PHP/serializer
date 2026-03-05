<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Attribute;

/**
 * Marks a property for serialization control.
 *
 * Parameters: name (wire-name override), groups (string[]), ignore (bool).
 * Properties without this attribute are included automatically using their property name.
 *
 * @package KaririCode\Serializer\Attribute
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class Serialize
{
    /** @param list<string> $groups */
    public function __construct(
        public ?string $name = null,
        public array $groups = [],
        public bool $ignore = false,
    ) {
    }
}
