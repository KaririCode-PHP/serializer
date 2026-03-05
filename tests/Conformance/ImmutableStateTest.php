<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Conformance;

use KaririCode\Serializer\Core\SerializationContextImpl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SerializationContextImpl::class)]
final class ImmutableStateTest extends TestCase
{
    #[Test]
    public function testContextImmutability(): void
    {
        $ctx = SerializationContextImpl::create('json');
        $ctx2 = $ctx->withFormat('xml');
        $this->assertNotSame($ctx, $ctx2);
        $this->assertSame('json', $ctx->getFormat());
        $this->assertSame('xml', $ctx2->getFormat());
    }

    #[Test]
    public function testParametersImmutability(): void
    {
        $ctx = SerializationContextImpl::create();
        $ctx2 = $ctx->withParameters(['x' => 1]);
        $this->assertNotSame($ctx, $ctx2);
        $this->assertSame([], $ctx->getParameters());
    }
}
