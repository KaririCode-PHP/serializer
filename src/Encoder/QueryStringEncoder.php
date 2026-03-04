<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;

/** Encodes/decodes URL query strings (application/x-www-form-urlencoded). */
final readonly class QueryStringEncoder implements Encoder
{
    public function encode(array $data, SerializationContext $context): string
    {
        return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    }

    public function decode(string $payload, SerializationContext $context): array
    {
        $result = [];
        parse_str($payload, $result);
        return $result;
    }

    public function supports(string $format): bool { return $format === 'query_string'; }
    public function getFormat(): string { return 'query_string'; }
}
