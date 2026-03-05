<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Core;

use KaririCode\Serializer\Configuration\SerializerConfiguration;
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\TestCase;

final class SerializerEngineTest extends TestCase
{
    public function testJsonRoundtrip(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $data = ['name' => 'Walmir', 'age' => 30, 'tags' => ['php', 'arfa']];

        $result = $engine->serialize($data, 'json');
        $this->assertSame('json', $result->getFormat());
        $this->assertJson($result->getPayload());

        $decoded = $engine->deserialize($result->getPayload(), 'json');
        $this->assertSame($data, $decoded);
    }

    public function testJsonPrettyPrint(): void
    {
        $engine = new SerializerServiceProvider()->createEngine(
            new SerializerConfiguration(prettyPrint: true),
        );
        $result = $engine->serialize(['a' => 1], 'json');
        $this->assertStringContainsString("\n", $result->getPayload());
    }

    public function testXmlRoundtrip(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $data = ['name' => 'Walmir', 'city' => 'Juazeiro'];

        $result = $engine->serialize($data, 'xml');
        $this->assertStringContainsString('Walmir', $result->getPayload());

        $decoded = $engine->deserialize($result->getPayload(), 'xml');
        $this->assertSame('Walmir', $decoded['name']);
        $this->assertSame('Juazeiro', $decoded['city']);
    }

    public function testCsvRoundtrip(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $data = [
            ['id' => '1', 'name' => 'Alice'],
            ['id' => '2', 'name' => 'Bob'],
        ];

        $result = $engine->serialize($data, 'csv');
        $this->assertStringContainsString('id,name', $result->getPayload());

        $decoded = $engine->deserialize($result->getPayload(), 'csv');
        $this->assertCount(2, $decoded);
        $this->assertSame('Alice', $decoded[0]['name']);
    }

    public function testQueryStringRoundtrip(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $data = ['page' => '1', 'per_page' => '20', 'sort' => 'name'];

        $result = $engine->serialize($data, 'query_string');
        $this->assertStringContainsString('page=1', $result->getPayload());

        $decoded = $engine->deserialize($result->getPayload(), 'query_string');
        $this->assertSame('1', $decoded['page']);
        $this->assertSame('20', $decoded['per_page']);
    }

    public function testDefaultFormat(): void
    {
        $engine = new SerializerServiceProvider()->createEngine(
            new SerializerConfiguration(defaultFormat: 'xml'),
        );
        $result = $engine->serialize(['a' => '1']);
        $this->assertSame('xml', $result->getFormat());
    }
}
