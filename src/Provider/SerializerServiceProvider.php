<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Provider;

use KaririCode\Serializer\Configuration\SerializerConfiguration;
use KaririCode\Serializer\Core\AttributeSerializer;
use KaririCode\Serializer\Core\InMemoryEncoderRegistry;
use KaririCode\Serializer\Core\SerializerEngine;
use KaririCode\Serializer\Encoder;

/**
 * Registers all 4 built-in encoders.
 *
 * @package KaririCode\Serializer\Provider
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class SerializerServiceProvider
{
    public function createRegistry(): InMemoryEncoderRegistry
    {
        $registry = new InMemoryEncoderRegistry();
        $registry->register(new Encoder\JsonEncoder());
        $registry->register(new Encoder\XmlEncoder());
        $registry->register(new Encoder\CsvEncoder());
        $registry->register(new Encoder\QueryStringEncoder());
        return $registry;
    }

    public function createEngine(?SerializerConfiguration $configuration = null): SerializerEngine
    {
        return new SerializerEngine($this->createRegistry(), $configuration);
    }

    public function createAttributeSerializer(?SerializerConfiguration $configuration = null): AttributeSerializer
    {
        return new AttributeSerializer($this->createEngine($configuration));
    }
}
