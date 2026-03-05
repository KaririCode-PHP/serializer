<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Provider;

use KaririCode\Serializer\Configuration\SerializerConfiguration;
use KaririCode\Serializer\Core\AttributeSerializer;
use KaririCode\Serializer\Core\SerializerEngine;
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SerializerServiceProvider::class)]
final class SerializerServiceProviderTest extends TestCase
{
    private SerializerServiceProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new SerializerServiceProvider();
    }

    #[Test]
    public function testRegistersAll4Formats(): void
    {
        $registry = $this->provider->createRegistry();
        $formats = $registry->formats();
        $this->assertCount(4, $formats);
        foreach (['json', 'xml', 'csv', 'query_string'] as $fmt) {
            $this->assertTrue($registry->has($fmt), "Missing format: {$fmt}");
        }
    }

    #[Test]
    public function testCreateEngineReturnsSerializerEngine(): void
    {
        $engine = $this->provider->createEngine();
        $this->assertInstanceOf(SerializerEngine::class, $engine);
    }

    #[Test]
    public function testCreateEngineWithConfiguration(): void
    {
        $config = new SerializerConfiguration(defaultFormat: 'xml', prettyPrint: true);
        $engine = $this->provider->createEngine($config);
        $this->assertInstanceOf(SerializerEngine::class, $engine);

        $result = $engine->serialize(['key' => 'value']);
        $this->assertSame('xml', $result->getFormat());
    }

    #[Test]
    public function testCreateAttributeSerializerReturnsAttributeSerializer(): void
    {
        $serializer = $this->provider->createAttributeSerializer();
        $this->assertInstanceOf(AttributeSerializer::class, $serializer);
    }

    #[Test]
    public function testCreateAttributeSerializerWithConfiguration(): void
    {
        $config = new SerializerConfiguration(defaultFormat: 'json');
        $serializer = $this->provider->createAttributeSerializer($config);
        $this->assertInstanceOf(AttributeSerializer::class, $serializer);
    }
}
