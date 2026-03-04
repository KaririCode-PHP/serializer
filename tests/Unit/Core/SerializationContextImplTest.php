<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Core;

use KaririCode\Serializer\Core\SerializationContextImpl;
use PHPUnit\Framework\TestCase;

final class SerializationContextImplTest extends TestCase
{
    public function testCreateDefaults(): void
    {
        $ctx = SerializationContextImpl::create('json');
        $this->assertSame('json', $ctx->getFormat());
        $this->assertSame([], $ctx->getParameters());
    }

    public function testWithFormatReturnsNewInstance(): void
    {
        $ctx = SerializationContextImpl::create('json');
        $ctx2 = $ctx->withFormat('xml');
        $this->assertNotSame($ctx, $ctx2);
        $this->assertSame('xml', $ctx2->getFormat());
    }

    public function testWithParametersMerges(): void
    {
        $ctx = SerializationContextImpl::create()
            ->withParameters(['a' => 1])
            ->withParameters(['b' => 2]);
        $this->assertSame(1, $ctx->getParameter('a'));
        $this->assertSame(2, $ctx->getParameter('b'));
    }
}
