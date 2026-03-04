<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Configuration;

final readonly class SerializerConfiguration
{
    public function __construct(
        public string $defaultFormat = 'json',
        public bool $prettyPrint = false,
    ) {}
}
