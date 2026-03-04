<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Core;

use KaririCode\Serializer\Contract\SerializationContext;

final readonly class SerializationContextImpl implements SerializationContext
{
    private function __construct(
        private string $format,
        private array $parameters,
    ) {}

    public static function create(string $format = 'json'): self
    {
        return new self($format, []);
    }

    public function getFormat(): string { return $this->format; }
    public function getParameter(string $key, mixed $default = null): mixed { return $this->parameters[$key] ?? $default; }
    public function getParameters(): array { return $this->parameters; }

    public function withFormat(string $format): static
    {
        return new self($format, $this->parameters);
    }

    public function withParameters(array $parameters): static
    {
        return new self($this->format, [...$this->parameters, ...$parameters]);
    }
}
