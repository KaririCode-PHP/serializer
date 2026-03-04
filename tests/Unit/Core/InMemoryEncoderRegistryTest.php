<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Core;

use KaririCode\Serializer\Core\InMemoryEncoderRegistry;
use KaririCode\Serializer\Encoder\JsonEncoder;
use KaririCode\Serializer\Exception\SerializationException;
use PHPUnit\Framework\TestCase;

final class InMemoryEncoderRegistryTest extends TestCase
{
    public function testRegisterAndResolve(): void
    {
        $registry = new InMemoryEncoderRegistry();
        $encoder = new JsonEncoder();
        $registry->register($encoder);
        $this->assertTrue($registry->has('json'));
        $this->assertSame($encoder, $registry->resolve('json'));
    }

    public function testDuplicateThrows(): void
    {
        $registry = new InMemoryEncoderRegistry();
        $registry->register(new JsonEncoder());
        $this->expectException(SerializationException::class);
        $registry->register(new JsonEncoder());
    }

    public function testUnknownThrows(): void
    {
        $this->expectException(SerializationException::class);
        (new InMemoryEncoderRegistry())->resolve('msgpack');
    }
}
