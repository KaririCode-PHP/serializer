<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Exception;

use KaririCode\Serializer\Exception\SerializationException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// NOTE: #[CoversClass] intentionally omitted for SerializationException.
// PHPUnit 12 + pcov reports classes extending \RuntimeException as
// "not a valid target for code coverage" — this triggers warnings that
// with failOnWarning="true" cause exit code 1.
// Coverage is achieved transitively via EncoderTest and SerializerEngineTest.
final class SerializationExceptionTest extends TestCase
{
    #[Test]
    public function testEncodingFailed(): void
    {
        $e = SerializationException::encodingFailed('json', 'malformed');

        $this->assertInstanceOf(SerializationException::class, $e);
        $this->assertSame("Failed to encode to 'json': malformed", $e->getMessage());
    }

    #[Test]
    public function testDecodingFailed(): void
    {
        $e = SerializationException::decodingFailed('xml', 'bad input');

        $this->assertInstanceOf(SerializationException::class, $e);
        $this->assertSame("Failed to decode from 'xml': bad input", $e->getMessage());
    }

    #[Test]
    public function testUnsupportedFormat(): void
    {
        $e = SerializationException::unsupportedFormat('yaml');

        $this->assertInstanceOf(SerializationException::class, $e);
        $this->assertSame("No encoder registered for format 'yaml'.", $e->getMessage());
    }

    #[Test]
    public function testDuplicateEncoder(): void
    {
        $e = SerializationException::duplicateEncoder('csv');

        $this->assertInstanceOf(SerializationException::class, $e);
        $this->assertSame("Encoder for format 'csv' is already registered.", $e->getMessage());
    }
}
