<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;
use KaririCode\Serializer\Exception\SerializationException;

final readonly class JsonEncoder implements Encoder
{
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

    public function decode(string $payload, SerializationContext $context): array
    {
        try {
            $result = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            return is_array($result) ? $result : throw new \JsonException('Root must be object or array.');
        } catch (\JsonException $e) {
            throw SerializationException::decodingFailed('json', $e->getMessage());
        }
    }

    public function supports(string $format): bool { return $format === 'json'; }
    public function getFormat(): string { return 'json'; }
}
