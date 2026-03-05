<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Tests\Unit\Core;

use KaririCode\Serializer\Core\SerializeAttributeHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SerializeAttributeHandler::class)]
final class SerializeAttributeHandlerTest extends TestCase
{
    #[Test]
    public function testHandleAttributeWithNonSerializeAttributeReturnsNull(): void
    {
        $handler = new SerializeAttributeHandler();
        $otherAttribute = new \stdClass();

        $result = $handler->handleAttribute('name', $otherAttribute, 'value');

        $this->assertNull($result);
        $this->assertSame([], $handler->getProcessedPropertyValues());
    }

    #[Test]
    public function testHandleAttributeWithIgnoreSkipsProperty(): void
    {
        $attr = new \KaririCode\Serializer\Attribute\Serialize(ignore: true);
        $handler = new SerializeAttributeHandler();
        $handler->handleAttribute('password', $attr, 'secret');

        $this->assertSame(['password'], $handler->getIgnoredProperties());
        $this->assertSame(['password'], $handler->getEvaluatedProperties());
        $this->assertFalse(isset($handler->getProcessedPropertyValues()['password']));
    }

    #[Test]
    public function testHandleAttributeWithGroupFilterExcludes(): void
    {
        $attr = new \KaririCode\Serializer\Attribute\Serialize(groups: ['admin']);
        $handler = new SerializeAttributeHandler(['public']); // active group = public

        $result = $handler->handleAttribute('secret', $attr, 'admin-only');

        $this->assertNull($result);
        // Property is evaluated (has #[Serialize]) but not in the result set
        $this->assertSame(['secret'], $handler->getEvaluatedProperties());
        $this->assertFalse(isset($handler->getProcessedPropertyValues()['secret']));
    }

    #[Test]
    public function testHandleAttributeWithMatchingGroupIncludes(): void
    {
        $attr = new \KaririCode\Serializer\Attribute\Serialize(groups: ['public', 'api']);
        $handler = new SerializeAttributeHandler(['public']);

        $handler->handleAttribute('name', $attr, 'Walmir');

        $this->assertSame(['name' => 'Walmir'], $handler->getProcessedPropertyValues());
        $this->assertSame(['name' => 'name'], $handler->getNameMap());
    }

    #[Test]
    public function testHandleAttributeWithCustomWireName(): void
    {
        $attr = new \KaririCode\Serializer\Attribute\Serialize(name: 'full_name');
        $handler = new SerializeAttributeHandler();

        $handler->handleAttribute('firstName', $attr, 'Walmir');

        $this->assertSame(['full_name' => 'Walmir'], $handler->getProcessedPropertyValues());
        $this->assertSame(['full_name' => 'firstName'], $handler->getNameMap());
    }

    #[Test]
    public function testAddUnannotatedPropertyIncludesValue(): void
    {
        $handler = new SerializeAttributeHandler();
        $handler->addUnannotatedProperty('email', 'w@k.org');

        $this->assertSame(['email' => 'w@k.org'], $handler->getProcessedPropertyValues());
    }

    #[Test]
    public function testGetProcessingResultMessagesReturnsEmpty(): void
    {
        $handler = new SerializeAttributeHandler();
        $this->assertSame([], $handler->getProcessingResultMessages());
    }

    #[Test]
    public function testGetProcessingResultErrorsReturnsEmpty(): void
    {
        $handler = new SerializeAttributeHandler();
        $this->assertSame([], $handler->getProcessingResultErrors());
    }

    #[Test]
    public function testSetProcessedValuesAndApplyChanges(): void
    {
        $obj = new class () {
            public string $name = '';
            public string $city = '';
        };

        $handler = new SerializeAttributeHandler();
        $handler->setProcessedValues(['name' => 'Walmir', 'city' => 'Juazeiro']);
        $handler->applyChanges($obj);

        $this->assertSame('Walmir', $obj->name);
        $this->assertSame('Juazeiro', $obj->city);
    }

    #[Test]
    public function testApplyChangesSkipsNonExistentProperty(): void
    {
        $obj = new class () {
            public string $name = 'unchanged';
        };

        $handler = new SerializeAttributeHandler();
        $handler->setProcessedValues(['non_existent' => 'value', 'name' => 'Walmir']);
        $handler->applyChanges($obj);

        $this->assertSame('Walmir', $obj->name);
    }

    #[Test]
    public function testGroupsEmptyMatchesAllGroups(): void
    {
        // When handler groups is empty, all attributes match regardless of their groups
        $attr = new \KaririCode\Serializer\Attribute\Serialize(groups: ['admin']);
        $handler = new SerializeAttributeHandler([]); // no group filter

        $handler->handleAttribute('secret', $attr, 'admin-only');

        $this->assertSame(['secret' => 'admin-only'], $handler->getProcessedPropertyValues());
    }
}
