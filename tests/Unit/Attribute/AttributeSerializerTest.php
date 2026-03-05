<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Attribute;

use KaririCode\Serializer\Attribute\Serialize;
use KaririCode\Serializer\Core\AttributeSerializer;
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeSerializer::class)]
final class AttributeSerializerTest extends TestCase
{
    private AttributeSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new SerializerServiceProvider()->createAttributeSerializer();
    }

    #[Test]
    public function testSerializeWithNameMapping(): void
    {
        $dto = new class () {
            #[Serialize(name: 'first_name')]
            public string $firstName = 'Walmir';

            #[Serialize(name: 'last_name')]
            public string $lastName = 'Silva';

            #[Serialize(ignore: true)]
            public string $password = 'secret';

            public string $email = 'walmir@kariricode.org';
        };

        $result = $this->serializer->serialize($dto, 'json');

        $decoded = json_decode($result->getPayload(), true);
        $this->assertSame('Walmir', $decoded['first_name']);
        $this->assertSame('Silva', $decoded['last_name']);
        $this->assertArrayNotHasKey('password', $decoded);
        $this->assertSame('walmir@kariricode.org', $decoded['email']);
    }

    #[Test]
    public function testSerializeWithGroups(): void
    {
        $dto = new class () {
            #[Serialize(groups: ['public', 'api'])]
            public string $name = 'Walmir';

            #[Serialize(groups: ['admin'])]
            public string $secret = 'hidden';
        };

        $result = $this->serializer->serialize($dto, 'json', ['public']);

        $decoded = json_decode($result->getPayload(), true);
        $this->assertSame('Walmir', $decoded['name']);
        $this->assertArrayNotHasKey('secret', $decoded);
    }

    #[Test]
    public function testDeserializeWithNameMapping(): void
    {
        $json = '{"first_name":"Walmir","last_name":"Silva","email":"w@k.org"}';
        $obj = $this->serializer->deserialize($json, SerializerTestDto::class, 'json');

        $this->assertSame('Walmir', $obj->firstName);
        $this->assertSame('Silva', $obj->lastName);
    }

    #[Test]
    public function testSerializeWithNullFormatUsesDefault(): void
    {
        $dto = new class () {
            public string $name = 'Test';
        };

        $result = $this->serializer->serialize($dto);
        $this->assertSame('json', $result->getFormat());
        $this->assertJson($result->getPayload());
    }

    #[Test]
    public function testSerializeIncludesUnannotatedProperties(): void
    {
        // Object with no #[Serialize] at all — all properties included via unannotated path
        $dto = new class () {
            public string $city = 'Juazeiro';
            public int $population = 280000;
        };

        $result = $this->serializer->serialize($dto, 'json');
        $decoded = json_decode($result->getPayload(), true);

        $this->assertSame('Juazeiro', $decoded['city']);
        $this->assertSame(280000, $decoded['population']);
    }

    #[Test]
    public function testSerializeUnannotatedPropertyWithUninitializedValue(): void
    {
        // A property declared but not initialized — should be included as null (no Error)
        // This covers the Error catch branch in includeUnannotatedProperties
        $dto = new class () {
            // Typed, uninitialized — getValue() will throw \Error before PHP 8.0 style
            // In PHP 8.4, untyped public properties are null; typed+uninitialized throw
            public string $uninitializedProp;
            public string $name = 'ok';
        };

        // Should NOT throw — the Error is caught and null used
        $result = $this->serializer->serialize($dto, 'json');
        $decoded = json_decode($result->getPayload(), true);
        $this->assertArrayHasKey('name', $decoded);
        // uninitializedProp may be null or absent depending on PHP version
    }

    #[Test]
    public function testDeserializeWithDefaultFormat(): void
    {
        $json = '{"firstName":"Ana","lastName":"Lima"}';
        $obj = $this->serializer->deserialize($json, SerializerTestDto2::class);

        $this->assertSame('Ana', $obj->firstName);
        $this->assertSame('Lima', $obj->lastName);
    }
}

class SerializerTestDto
{
    #[Serialize(name: 'first_name')]
    public string $firstName = '';

    #[Serialize(name: 'last_name')]
    public string $lastName = '';

    public string $email = '';
}

class SerializerTestDto2
{
    public string $firstName = '';
    public string $lastName = '';
}
