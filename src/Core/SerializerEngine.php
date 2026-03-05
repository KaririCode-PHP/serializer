<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Core;

use KaririCode\Serializer\Configuration\SerializerConfiguration;
use KaririCode\Serializer\Contract\EncoderRegistry;
use KaririCode\Serializer\Result\SerializationResult;

/**
 * Central serialization orchestrator.
 *
 * Delegates to format-specific encoders via the registry.
 *
 * @package KaririCode\Serializer\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class SerializerEngine
{
    public function __construct(
        private readonly EncoderRegistry $registry,
        private readonly ?SerializerConfiguration $configuration = null,
    ) {
    }

    /**
     * Serialize array data to a string in the given format.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $parameters
     */
    public function serialize(array $data, ?string $format = null, array $parameters = []): SerializationResult
    {
        $config = $this->configuration ?? new SerializerConfiguration();
        $fmt = $format ?? $config->defaultFormat;
        $encoder = $this->registry->resolve($fmt);

        $ctx = SerializationContextImpl::create($fmt);
        if ($config->prettyPrint) {
            $ctx = $ctx->withParameters(['pretty' => true]);
        }
        if ($parameters !== []) {
            $ctx = $ctx->withParameters($parameters);
        }

        $payload = $encoder->encode($data, $ctx);

        return new SerializationResult($data, $payload, $fmt);
    }

    /**
     * Deserialize string payload to array data.
     *
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public function deserialize(string $payload, ?string $format = null, array $parameters = []): array
    {
        $config = $this->configuration ?? new SerializerConfiguration();
        $fmt = $format ?? $config->defaultFormat;
        $encoder = $this->registry->resolve($fmt);

        $ctx = SerializationContextImpl::create($fmt);
        if ($parameters !== []) {
            $ctx = $ctx->withParameters($parameters);
        }

        return $encoder->decode($payload, $ctx);
    }
}
