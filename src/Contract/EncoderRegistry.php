<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Contract;

interface EncoderRegistry
{
    public function register(Encoder $encoder): void;
    public function resolve(string $format): Encoder;
    public function has(string $format): bool;
    /** @return list<string> */
    public function formats(): array;
}
