<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Contract;

/**
 * Immutable context for serialization operations.
 *
 * @package KaririCode\Serializer\Contract
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
interface SerializationContext
{
    public function getFormat(): string;
    public function getParameter(string $key, mixed $default = null): mixed;
    /** @return array<string, mixed> */
    public function getParameters(): array;
    public function withFormat(string $format): static;
    /** @param array<string, mixed> $parameters */
    public function withParameters(array $parameters): static;
}
