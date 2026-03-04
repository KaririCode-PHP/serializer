<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Conformance;

use KaririCode\Serializer\Core\SerializationContextImpl;
use PHPUnit\Framework\TestCase;

final class ImmutableStateTest extends TestCase
{
    public function testContextImmutability(): void
    {
        $ctx = SerializationContextImpl::create('json');
        $ctx2 = $ctx->withFormat('xml');
        $this->assertNotSame($ctx, $ctx2);
        $this->assertSame('json', $ctx->getFormat());
        $this->assertSame('xml', $ctx2->getFormat());
    }

    public function testParametersImmutability(): void
    {
        $ctx = SerializationContextImpl::create();
        $ctx2 = $ctx->withParameters(['x' => 1]);
        $this->assertNotSame($ctx, $ctx2);
        $this->assertSame([], $ctx->getParameters());
    }
}
