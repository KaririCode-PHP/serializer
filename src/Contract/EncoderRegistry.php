<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Contract;

/**
 * Contract for format-encoder registries.
 *
 * @package KaririCode\Serializer\Contract
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
interface EncoderRegistry
{
    public function register(Encoder $encoder): void;

    public function resolve(string $format): Encoder;

    public function has(string $format): bool;

    /** @return list<string> */
    public function formats(): array;
}
