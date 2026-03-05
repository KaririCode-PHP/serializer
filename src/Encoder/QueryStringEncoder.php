<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;

/** Encodes/decodes URL query strings (application/x-www-form-urlencoded). */
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
