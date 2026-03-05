<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Encoder;

use KaririCode\Serializer\Core\SerializationContextImpl;
use KaririCode\Serializer\Encoder\{CsvEncoder, JsonEncoder, QueryStringEncoder, XmlEncoder};
use KaririCode\Serializer\Exception\SerializationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonEncoder::class)]
#[CoversClass(XmlEncoder::class)]
#[CoversClass(CsvEncoder::class)]
#[CoversClass(QueryStringEncoder::class)]
final class EncoderTest extends TestCase
{
    private function ctx(string $format = 'json', array $params = []): \KaririCode\Serializer\Contract\SerializationContext
    {
        $ctx = SerializationContextImpl::create($format);

        return $params !== [] ? $ctx->withParameters($params) : $ctx;
    }

    // ── JSON ──────────────────────────────────────────────────────────
    #[Test]
    public function testJsonEncoderUnicode(): void
    {
        $encoder = new JsonEncoder();
        $payload = $encoder->encode(['city' => 'São Paulo'], $this->ctx());
        $this->assertStringContainsString('São Paulo', $payload);
    }

    #[Test]
    public function testJsonEncoderPrettyPrint(): void
    {
        $encoder = new JsonEncoder();
        $payload = $encoder->encode(['a' => 1], $this->ctx('json', ['pretty' => true]));
        $this->assertStringContainsString("\n", $payload);
    }

    #[Test]
    public function testJsonEncoderDecodeThrowsOnInvalid(): void
    {
        $this->expectException(SerializationException::class);
        new JsonEncoder()->decode('not json', $this->ctx());
    }

    #[Test]
    public function testJsonEncoderSupportsAndFormat(): void
    {
        $encoder = new JsonEncoder();
        $this->assertTrue($encoder->supports('json'));
        $this->assertFalse($encoder->supports('xml'));
        $this->assertSame('json', $encoder->getFormat());
    }

    // ── XML ───────────────────────────────────────────────────────────
    #[Test]
    public function testXmlEncoderCustomRoot(): void
    {
        $encoder = new XmlEncoder();
        $payload = $encoder->encode(['a' => '1'], $this->ctx('xml', ['root' => 'data']));
        $this->assertStringContainsString('<data>', $payload);
    }

    #[Test]
    public function testXmlEncoderPrettyPrint(): void
    {
        $encoder = new XmlEncoder();
        $payload = $encoder->encode(['key' => 'val'], $this->ctx('xml', ['pretty' => true]));
        $this->assertStringContainsString("\n", $payload);
    }

    #[Test]
    public function testXmlEncoderDecodeThrowsOnInvalid(): void
    {
        $this->expectException(SerializationException::class);
        new XmlEncoder()->decode('not xml', $this->ctx('xml'));
    }

    #[Test]
    public function testXmlEncoderDecodeNested(): void
    {
        $encoder = new XmlEncoder();
        $data = ['person' => ['name' => 'Walmir', 'city' => 'Juazeiro']];
        $payload = $encoder->encode($data, $this->ctx('xml'));
        $decoded = $encoder->decode($payload, $this->ctx('xml'));
        $this->assertSame('Walmir', $decoded['person']['name']);
    }

    #[Test]
    public function testXmlEncoderDecodeRepeatedKeys(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><root><item>a</item><item>b</item></root>';
        $decoded = new XmlEncoder()->decode($xml, $this->ctx('xml'));
        $this->assertIsArray($decoded['item']);
        $this->assertContains('a', $decoded['item']);
        $this->assertContains('b', $decoded['item']);
    }

    #[Test]
    public function testXmlEncoderSupportsAndFormat(): void
    {
        $encoder = new XmlEncoder();
        $this->assertTrue($encoder->supports('xml'));
        $this->assertFalse($encoder->supports('json'));
        $this->assertSame('xml', $encoder->getFormat());
    }

    // ── CSV ───────────────────────────────────────────────────────────
    #[Test]
    public function testCsvEncoderEncodeEmpty(): void
    {
        $this->assertSame('', new CsvEncoder()->encode([], $this->ctx('csv')));
    }

    #[Test]
    public function testCsvEncoderEncodeWithoutHeader(): void
    {
        $payload = new CsvEncoder()->encode(
            [['Alice', '30'], ['Bob', '25']],
            $this->ctx('csv', ['header' => false]),
        );
        $this->assertStringNotContainsString('name', $payload);
    }

    #[Test]
    public function testCsvEncoderDecodeWithHeader(): void
    {
        $data = [['id' => '1', 'name' => 'Alice'], ['id' => '2', 'name' => 'Bob']];
        $encoder = new CsvEncoder();
        $payload = $encoder->encode($data, $this->ctx('csv'));
        $decoded = $encoder->decode($payload, $this->ctx('csv'));

        $this->assertCount(2, $decoded);
        $this->assertSame('Alice', $decoded[0]['name']);
        $this->assertSame('2', $decoded[1]['id']);
    }

    #[Test]
    public function testCsvEncoderDecodeWithoutHeader(): void
    {
        $payload = "Alice,30\nBob,25\n";
        $decoded = new CsvEncoder()->decode($payload, $this->ctx('csv', ['header' => false]));
        $this->assertCount(2, $decoded);
        $this->assertSame(['Alice', '30'], $decoded[0]);
    }

    #[Test]
    public function testCsvEncoderDecodeEmptyPayload(): void
    {
        $decoded = new CsvEncoder()->decode('', $this->ctx('csv'));
        $this->assertSame([], $decoded);
    }

    #[Test]
    public function testCsvEncoderSupportsAndFormat(): void
    {
        $encoder = new CsvEncoder();
        $this->assertTrue($encoder->supports('csv'));
        $this->assertFalse($encoder->supports('json'));
        $this->assertSame('csv', $encoder->getFormat());
    }

    // ── QueryString ───────────────────────────────────────────────────
    #[Test]
    public function testQueryStringEncoding(): void
    {
        $encoder = new QueryStringEncoder();
        $payload = $encoder->encode(['q' => 'hello world', 'page' => '1'], $this->ctx('query_string'));
        $this->assertStringContainsString('q=hello%20world', $payload);
    }

    #[Test]
    public function testQueryStringDecoding(): void
    {
        $encoder = new QueryStringEncoder();
        $payload = $encoder->encode(['page' => '2', 'sort' => 'name'], $this->ctx('query_string'));
        $decoded = $encoder->decode($payload, $this->ctx('query_string'));
        $this->assertSame('2', $decoded['page']);
        $this->assertSame('name', $decoded['sort']);
    }

    #[Test]
    public function testQueryStringSupportsAndFormat(): void
    {
        $encoder = new QueryStringEncoder();
        $this->assertTrue($encoder->supports('query_string'));
        $this->assertFalse($encoder->supports('csv'));
        $this->assertSame('query_string', $encoder->getFormat());
    }
}
