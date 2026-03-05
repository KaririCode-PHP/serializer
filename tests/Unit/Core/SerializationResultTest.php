<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Core;

use KaririCode\Serializer\Result\SerializationResult;
use PHPUnit\Framework\TestCase;

final class SerializationResultTest extends TestCase
{
    public function testGetters(): void
    {
        $result = new SerializationResult(
            originalData: ['name' => 'Walmir', 'age' => 30],
            payload: '{"name":"Walmir","age":30}',
            format: 'json',
        );

        $this->assertSame(['name' => 'Walmir', 'age' => 30], $result->getOriginalData());
        $this->assertSame('{"name":"Walmir","age":30}', $result->getPayload());
        $this->assertSame('json', $result->getFormat());
        $this->assertSame(26, $result->getPayloadSize());
    }

    public function testPayloadSizeEmptyPayload(): void
    {
        $result = new SerializationResult(originalData: [], payload: '', format: 'json');
        $this->assertSame(0, $result->getPayloadSize());
    }

    public function testXmlPayload(): void
    {
        $result = new SerializationResult(
            originalData: ['name' => 'Walmir'],
            payload: '<root><name>Walmir</name></root>',
            format: 'xml',
        );
        $this->assertSame('xml', $result->getFormat());
        $this->assertSame(32, $result->getPayloadSize());
    }
}
