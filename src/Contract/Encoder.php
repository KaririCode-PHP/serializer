<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Contract;

/**
 * Format encoder: converts array↔string for a specific format.
 *
 * Each encoder handles one wire format (JSON, XML, CSV, YAML).
 *
 * @package KaririCode\Serializer\Contract
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
interface Encoder
{
    /** @param array<mixed> $data */
    public function encode(array $data, SerializationContext $context): string;

    /** @return array<mixed> */
    public function decode(string $payload, SerializationContext $context): array;

    public function supports(string $format): bool;

    public function getFormat(): string;
}
