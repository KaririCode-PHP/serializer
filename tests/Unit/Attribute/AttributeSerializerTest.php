<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Attribute;

use KaririCode\Serializer\Attribute\Serialize;
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use PHPUnit\Framework\TestCase;

final class AttributeSerializerTest extends TestCase
{
    public function testSerializeWithNameMapping(): void
    {
        $dto = new class {
            #[Serialize(name: 'first_name')]
            public string $firstName = 'Walmir';

            #[Serialize(name: 'last_name')]
            public string $lastName = 'Silva';

            #[Serialize(ignore: true)]
            public string $password = 'secret';

            public string $email = 'walmir@kariricode.org';
        };

        $serializer = (new SerializerServiceProvider())->createAttributeSerializer();
        $result = $serializer->serialize($dto, 'json');

        $decoded = json_decode($result->getPayload(), true);
        $this->assertSame('Walmir', $decoded['first_name']);
        $this->assertSame('Silva', $decoded['last_name']);
        $this->assertArrayNotHasKey('password', $decoded);
        $this->assertSame('walmir@kariricode.org', $decoded['email']);
    }

    public function testSerializeWithGroups(): void
    {
        $dto = new class {
            #[Serialize(groups: ['public', 'api'])]
            public string $name = 'Walmir';

            #[Serialize(groups: ['admin'])]
            public string $secret = 'hidden';
        };

        $serializer = (new SerializerServiceProvider())->createAttributeSerializer();
        $result = $serializer->serialize($dto, 'json', ['public']);

        $decoded = json_decode($result->getPayload(), true);
        $this->assertSame('Walmir', $decoded['name']);
        $this->assertArrayNotHasKey('secret', $decoded);
    }

    public function testDeserializeWithNameMapping(): void
    {
        $json = '{"first_name":"Walmir","last_name":"Silva","email":"w@k.org"}';
        $serializer = (new SerializerServiceProvider())->createAttributeSerializer();
        $obj = $serializer->deserialize($json, SerializerTestDto::class, 'json');

        $this->assertSame('Walmir', $obj->firstName);
        $this->assertSame('Silva', $obj->lastName);
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
