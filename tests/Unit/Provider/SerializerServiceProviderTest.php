<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Provider;

use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\TestCase;

final class SerializerServiceProviderTest extends TestCase
{
    public function testRegistersAll4Formats(): void
    {
        $registry = new SerializerServiceProvider()->createRegistry();
        $formats = $registry->formats();
        $this->assertCount(4, $formats);
        foreach (['json', 'xml', 'csv', 'query_string'] as $fmt) {
            $this->assertTrue($registry->has($fmt), "Missing format: {$fmt}");
        }
    }
}
