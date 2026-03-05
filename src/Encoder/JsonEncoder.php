<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;
use KaririCode\Serializer\Exception\SerializationException;

/**
 * Encodes/decodes data as RFC 8259-compliant JSON.
 *
 * Uses JSON_THROW_ON_ERROR for deterministic error handling.
 * Supports `pretty` context parameter for human-readable output.
 *
 * @package KaririCode\Serializer\Encoder
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class JsonEncoder implements Encoder
{
    /** @param array<mixed> $data */
    #[\Override]
    public function encode(array $data, SerializationContext $context): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
        if ($context->getParameter('pretty', false)) {
            $flags |= JSON_PRETTY_PRINT;
        }

        try {
            return json_encode($data, $flags);
        } catch (\JsonException $e) {
            throw SerializationException::encodingFailed('json', $e->getMessage());
        }
    }

    /** @return array<mixed> */
    #[\Override]
    public function decode(string $payload, SerializationContext $context): array
    {
        try {
            $result = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            if (! \is_array($result)) {
                throw new \JsonException('Root must be object or array.');
            }

            /** @var array<string, mixed> $result */
            return $result;
        } catch (\JsonException $e) {
            throw SerializationException::decodingFailed('json', $e->getMessage());
        }
    }

    #[\Override]
    public function supports(string $format): bool
    {
        return $format === 'json';
    }

    #[\Override]
    public function getFormat(): string
    {
        return 'json';
    }
}
