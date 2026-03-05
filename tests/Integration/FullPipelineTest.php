<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Integration;

use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\TestCase;

final class FullPipelineTest extends TestCase
{
    public function testAllFormatsRoundtrip(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();

        // JSON
        $json = $engine->serialize(['x' => 'hello'], 'json');
        $this->assertSame(['x' => 'hello'], $engine->deserialize($json->getPayload(), 'json'));

        // XML
        $xml = $engine->serialize(['x' => 'hello'], 'xml');
        $this->assertSame(['x' => 'hello'], $engine->deserialize($xml->getPayload(), 'xml'));

        // Query String
        $qs = $engine->serialize(['x' => 'hello'], 'query_string');
        $this->assertSame(['x' => 'hello'], $engine->deserialize($qs->getPayload(), 'query_string'));
    }

    public function testCsvRoundtrip(): void
    {
        $engine = new SerializerServiceProvider()->createEngine();
        $data = [['id' => '1', 'name' => 'A'], ['id' => '2', 'name' => 'B']];

        $csv = $engine->serialize($data, 'csv');
        $decoded = $engine->deserialize($csv->getPayload(), 'csv');

        $this->assertCount(2, $decoded);
        $this->assertSame('A', $decoded[0]['name']);
    }
}
