<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Core;

use KaririCode\Serializer\Core\InMemoryEncoderRegistry;
use KaririCode\Serializer\Encoder\JsonEncoder;
use KaririCode\Serializer\Exception\SerializationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryEncoderRegistry::class)]
final class InMemoryEncoderRegistryTest extends TestCase
{
    #[Test]
    public function testRegisterAndResolve(): void
    {
        $registry = new InMemoryEncoderRegistry();
        $encoder = new JsonEncoder();
        $registry->register($encoder);
        $this->assertTrue($registry->has('json'));
        $this->assertSame($encoder, $registry->resolve('json'));
    }

    #[Test]
    public function testDuplicateThrows(): void
    {
        $registry = new InMemoryEncoderRegistry();
        $registry->register(new JsonEncoder());
        $this->expectException(SerializationException::class);
        $registry->register(new JsonEncoder());
    }

    #[Test]
    public function testUnknownThrows(): void
    {
        $this->expectException(SerializationException::class);
        new InMemoryEncoderRegistry()->resolve('msgpack');
    }

    #[Test]
    public function testFormatsReturnsRegisteredFormats(): void
    {
        $registry = new InMemoryEncoderRegistry();
        $registry->register(new JsonEncoder());
        $formats = $registry->formats();
        $this->assertContains('json', $formats);
        $this->assertCount(1, $formats);
    }
}
