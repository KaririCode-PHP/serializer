<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Core;

use KaririCode\Serializer\Contract\SerializationContext;

/**
 * Immutable serialization context carrying the active format and parameters.
 *
 * Uses the named-constructor pattern: instantiate via `SerializationContextImpl::create()`.
 * `withFormat()` and `withParameters()` return new instances (full immutability).
 *
 * @package KaririCode\Serializer\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class SerializationContextImpl implements SerializationContext
{
    /**
     * @param array<string, mixed> $parameters
     */
    private function __construct(
        private string $format,
        private array $parameters,
    ) {
    }

    public static function create(string $format = 'json'): self
    {
        return new self($format, []);
    }

    #[\Override]
    public function getFormat(): string
    {
        return $this->format;
    }

    #[\Override]
    public function getParameter(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    #[\Override]
    public function getParameters(): array
    {
        return $this->parameters;
    }

    #[\Override]
    public function withFormat(string $format): static
    {
        return new self($format, $this->parameters);
    }

    #[\Override]
    public function withParameters(array $parameters): static
    {
        return new self($this->format, [...$this->parameters, ...$parameters]);
    }
}
