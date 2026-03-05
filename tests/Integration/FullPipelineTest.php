<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Integration;

use KaririCode\Serializer\Core\SerializerEngine;
use KaririCode\Serializer\Encoder\CsvEncoder;
use KaririCode\Serializer\Encoder\JsonEncoder;
use KaririCode\Serializer\Encoder\QueryStringEncoder;
use KaririCode\Serializer\Encoder\XmlEncoder;
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonEncoder::class)]
#[CoversClass(XmlEncoder::class)]
#[CoversClass(CsvEncoder::class)]
#[CoversClass(QueryStringEncoder::class)]
#[CoversClass(SerializerEngine::class)]
final class FullPipelineTest extends TestCase
{
    private SerializerEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new SerializerServiceProvider()->createEngine();
    }

    #[Test]
    public function testAllFormatsRoundtrip(): void
    {
        // JSON full roundtrip
        $json = $this->engine->serialize(['x' => 'hello'], 'json');
        $this->assertSame(['x' => 'hello'], $this->engine->deserialize($json->getPayload(), 'json'));

        // XML full roundtrip
        $xml = $this->engine->serialize(['x' => 'hello'], 'xml');
        $this->assertSame(['x' => 'hello'], $this->engine->deserialize($xml->getPayload(), 'xml'));

        // Query String full roundtrip
        $qs = $this->engine->serialize(['x' => 'hello'], 'query_string');
        $this->assertSame(['x' => 'hello'], $this->engine->deserialize($qs->getPayload(), 'query_string'));
    }

    #[Test]
    public function testCsvRoundtrip(): void
    {
        $data = [['id' => '1', 'name' => 'A'], ['id' => '2', 'name' => 'B']];

        $csv = $this->engine->serialize($data, 'csv');
        $decoded = $this->engine->deserialize($csv->getPayload(), 'csv');

        $this->assertCount(2, $decoded);
        $this->assertSame('A', $decoded[0]['name']);
    }

    #[Test]
    public function testJsonEncodeAndDecodeComplexData(): void
    {
        // Exercises JsonEncoder::encode + decode with nested data, pretty-print
        $data = [
            'name' => 'Walmir',
            'nested' => ['key' => 'value'],
            'list' => [1, 2, 3],
            'flag' => true,
            'nothing' => null,
        ];

        $result = $this->engine->serialize($data, 'json');
        $this->assertJson($result->getPayload());

        $decoded = $this->engine->deserialize($result->getPayload(), 'json');
        $this->assertSame($data, $decoded);
    }

    #[Test]
    public function testXmlEncodeNestedData(): void
    {
        // Exercises XmlEncoder::arrayToXml with nested arrays (covers recursive arrayToXml path)
        $data = ['person' => ['name' => 'Walmir', 'city' => 'Juazeiro']];
        $result = $this->engine->serialize($data, 'xml');
        $this->assertStringContainsString('<person>', $result->getPayload());

        $decoded = $this->engine->deserialize($result->getPayload(), 'xml');
        $this->assertSame('Walmir', $decoded['person']['name']);
    }

    #[Test]
    public function testXmlDecodeRepeatedKeys(): void
    {
        // Exercises xmlToArray repeated-key branch
        $xml = '<?xml version="1.0" encoding="UTF-8"?><root><item>a</item><item>b</item></root>';
        $decoded = $this->engine->deserialize($xml, 'xml');
        $this->assertIsArray($decoded['item']);
        $this->assertContains('a', $decoded['item']);
        $this->assertContains('b', $decoded['item']);
    }

    #[Test]
    public function testCsvWithCustomSeparator(): void
    {
        // Exercises CsvEncoder::str() helper for both separator and enclosure params
        $data = [['name' => 'Alice', 'role' => 'admin']];
        $result = $this->engine->serialize($data, 'csv', ['separator' => ';']);
        $this->assertStringContainsString('name;role', $result->getPayload());

        $decoded = $this->engine->deserialize($result->getPayload(), 'csv', ['separator' => ';']);
        $this->assertSame('Alice', $decoded[0]['name']);
    }

    #[Test]
    public function testSerializerEngineDeserializeWithParameters(): void
    {
        // Explicitly tests SerializerEngine::deserialize $parameters branch (if $parameters !== [])
        $csv = "name,age\nBob,40\n";
        $decoded = $this->engine->deserialize($csv, 'csv', ['separator' => ',']);
        $this->assertSame('Bob', $decoded[0]['name']);
    }
}
