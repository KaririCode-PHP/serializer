<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;

/**
 * Encodes/decodes data as RFC 3986-compliant URL query strings (application/x-www-form-urlencoded).
 *
 * @package KaririCode\Serializer\Encoder
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class QueryStringEncoder implements Encoder
{
    /** @param array<mixed> $data */
    #[\Override]
    public function encode(array $data, SerializationContext $context): string
    {
        return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    }

    /** @return array<mixed> */
    #[\Override]
    public function decode(string $payload, SerializationContext $context): array
    {
        /** @var array<string, mixed> $result */
        $result = [];
        parse_str($payload, $result);

        return $result;
    }

    #[\Override]
    public function supports(string $format): bool
    {
        return $format === 'query_string';
    }

    #[\Override]
    public function getFormat(): string
    {
        return 'query_string';
    }
}
