<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Encoder;

use KaririCode\Serializer\Core\SerializationContextImpl;
use KaririCode\Serializer\Encoder\{JsonEncoder, XmlEncoder, CsvEncoder, QueryStringEncoder};
use KaririCode\Serializer\Exception\SerializationException;
use PHPUnit\Framework\TestCase;

final class EncoderTest extends TestCase
{
    private function ctx(string $format = 'json', array $params = []): \KaririCode\Serializer\Contract\SerializationContext
    {
        $ctx = SerializationContextImpl::create($format);
        return $params !== [] ? $ctx->withParameters($params) : $ctx;
    }

    public function testJsonEncoderUnicode(): void
    {
        $encoder = new JsonEncoder();
        $payload = $encoder->encode(['city' => 'São Paulo'], $this->ctx());
        $this->assertStringContainsString('São Paulo', $payload);
    }

    public function testJsonEncoderInvalidThrows(): void
    {
        $this->expectException(SerializationException::class);
        (new JsonEncoder())->decode('not json', $this->ctx());
    }

    public function testXmlEncoderCustomRoot(): void
    {
        $encoder = new XmlEncoder();
        $payload = $encoder->encode(['a' => '1'], $this->ctx('xml', ['root' => 'data']));
        $this->assertStringContainsString('<data>', $payload);
    }

    public function testXmlEncoderInvalidThrows(): void
    {
        $this->expectException(SerializationException::class);
        (new XmlEncoder())->decode('not xml', $this->ctx('xml'));
    }

    public function testCsvEncoderEmpty(): void
    {
        $this->assertSame('', (new CsvEncoder())->encode([], $this->ctx('csv')));
    }

    public function testCsvEncoderWithoutHeader(): void
    {
        $payload = (new CsvEncoder())->encode(
            [['Alice', '30'], ['Bob', '25']],
            $this->ctx('csv', ['header' => false]),
        );
        $this->assertStringNotContainsString('name', $payload);
    }

    public function testQueryStringEncoding(): void
    {
        $encoder = new QueryStringEncoder();
        $payload = $encoder->encode(['q' => 'hello world', 'page' => '1'], $this->ctx('query_string'));
        $this->assertStringContainsString('q=hello%20world', $payload);
    }

    public function testEncoderSupports(): void
    {
        $this->assertTrue((new JsonEncoder())->supports('json'));
        $this->assertFalse((new JsonEncoder())->supports('xml'));
        $this->assertTrue((new XmlEncoder())->supports('xml'));
        $this->assertTrue((new CsvEncoder())->supports('csv'));
        $this->assertTrue((new QueryStringEncoder())->supports('query_string'));
    }
}
