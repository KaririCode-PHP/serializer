<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Integration;

use KaririCode\Serializer\Integration\ProcessorBridge;
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessorBridge::class)]
final class ProcessorBridgeTest extends TestCase
{
    #[Test]
    public function testProcessReturnsJsonResult(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $bridge = new ProcessorBridge($engine, 'json');
        $data = ['key' => 'value', 'number' => 42];

        $result = $bridge->process($data);

        $this->assertSame('json', $result->getFormat());
        $this->assertJson($result->getPayload());
        $decoded = json_decode($result->getPayload(), true);
        $this->assertSame('value', $decoded['key']);
        $this->assertSame(42, $decoded['number']);
    }

    #[Test]
    public function testProcessDefaultFormatIsJson(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $bridge = new ProcessorBridge($engine);

        $result = $bridge->process(['x' => '1']);

        $this->assertSame('json', $result->getFormat());
    }

    #[Test]
    public function testProcessWithXmlFormat(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $bridge = new ProcessorBridge($engine, 'xml');

        $result = $bridge->process(['city' => 'Juazeiro']);

        $this->assertSame('xml', $result->getFormat());
        $this->assertStringContainsString('Juazeiro', $result->getPayload());
    }
}
