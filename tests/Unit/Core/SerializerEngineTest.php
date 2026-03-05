<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Core;

use KaririCode\Serializer\Configuration\SerializerConfiguration;
use KaririCode\Serializer\Core\SerializerEngine;
use KaririCode\Serializer\Encoder\CsvEncoder;
use KaririCode\Serializer\Encoder\JsonEncoder;
use KaririCode\Serializer\Encoder\QueryStringEncoder;
use KaririCode\Serializer\Encoder\XmlEncoder;
use KaririCode\Serializer\Exception\SerializationException;
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SerializerEngine::class)]
#[CoversClass(JsonEncoder::class)]
#[CoversClass(XmlEncoder::class)]
#[CoversClass(CsvEncoder::class)]
#[CoversClass(QueryStringEncoder::class)]
final class SerializerEngineTest extends TestCase
{
    private SerializerEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new SerializerServiceProvider()->createEngine();
    }

    #[Test]
    public function testJsonRoundtrip(): void
    {
        $data = ['name' => 'Walmir', 'age' => 30, 'tags' => ['php', 'arfa']];

        $result = $this->engine->serialize($data, 'json');
        $this->assertSame('json', $result->getFormat());
        $this->assertJson($result->getPayload());

        $decoded = $this->engine->deserialize($result->getPayload(), 'json');
        $this->assertSame($data, $decoded);
    }

    #[Test]
    public function testJsonPrettyPrint(): void
    {
        $engine = new SerializerServiceProvider()->createEngine(
            new SerializerConfiguration(prettyPrint: true),
        );
        $result = $engine->serialize(['a' => 1], 'json');
        $this->assertStringContainsString("\n", $result->getPayload());
    }

    #[Test]
    public function testXmlRoundtrip(): void
    {
        $data = ['name' => 'Walmir', 'city' => 'Juazeiro'];

        $result = $this->engine->serialize($data, 'xml');
        $this->assertStringContainsString('Walmir', $result->getPayload());

        $decoded = $this->engine->deserialize($result->getPayload(), 'xml');
        $this->assertSame('Walmir', $decoded['name']);
        $this->assertSame('Juazeiro', $decoded['city']);
    }

    #[Test]
    public function testCsvRoundtrip(): void
    {
        $data = [
            ['id' => '1', 'name' => 'Alice'],
            ['id' => '2', 'name' => 'Bob'],
        ];

        $result = $this->engine->serialize($data, 'csv');
        $this->assertStringContainsString('id,name', $result->getPayload());

        $decoded = $this->engine->deserialize($result->getPayload(), 'csv');
        $this->assertCount(2, $decoded);
        $this->assertSame('Alice', $decoded[0]['name']);
    }

    #[Test]
    public function testQueryStringRoundtrip(): void
    {
        $data = ['page' => '1', 'per_page' => '20', 'sort' => 'name'];

        $result = $this->engine->serialize($data, 'query_string');
        $this->assertStringContainsString('page=1', $result->getPayload());

        $decoded = $this->engine->deserialize($result->getPayload(), 'query_string');
        $this->assertSame('1', $decoded['page']);
        $this->assertSame('20', $decoded['per_page']);
    }

    #[Test]
    public function testDefaultFormat(): void
    {
        $engine = new SerializerServiceProvider()->createEngine(
            new SerializerConfiguration(defaultFormat: 'xml'),
        );
        $result = $engine->serialize(['a' => '1']);
        $this->assertSame('xml', $result->getFormat());
    }

    #[Test]
    public function testSerializeWithNullFormatUsesDefault(): void
    {
        // default format = 'json'
        $result = $this->engine->serialize(['key' => 'value']);
        $this->assertSame('json', $result->getFormat());
        $this->assertJson($result->getPayload());
    }

    #[Test]
    public function testDeserializeWithNullFormatUsesDefault(): void
    {
        $decoded = $this->engine->deserialize('{"x": 42}');
        $this->assertSame(42, $decoded['x']);
    }

    #[Test]
    public function testDeserializeWithExplicitParameters(): void
    {
        // Pass separator parameter to CSV deserialize
        $csvPayload = "name,score\nAlice,90\n";
        $decoded = $this->engine->deserialize($csvPayload, 'csv');
        $this->assertSame('Alice', $decoded[0]['name']);
        $this->assertSame('90', $decoded[0]['score']);
    }

    #[Test]
    public function testSerializeWithCustomParameters(): void
    {
        // custom CSV separator
        $data = [['a' => '1', 'b' => '2']];
        $result = $this->engine->serialize($data, 'csv', ['separator' => ';']);
        $this->assertStringContainsString('a;b', $result->getPayload());
    }

    #[Test]
    public function testSerializeThrowsForUnknownFormat(): void
    {
        $this->expectException(SerializationException::class);
        $this->engine->serialize(['key' => 'value'], 'yaml');
    }

    #[Test]
    public function testDeserializeThrowsForUnknownFormat(): void
    {
        $this->expectException(SerializationException::class);
        $this->engine->deserialize('some:data', 'yaml');
    }
}
