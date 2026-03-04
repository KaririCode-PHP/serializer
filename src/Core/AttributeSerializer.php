<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Core;

use KaririCode\Serializer\Attribute\Serialize;
use KaririCode\Serializer\Result\SerializationResult;

/**
 * Serializes objects to format strings using #[Serialize] attributes.
 *
 * Combines with Normalizer for full object→string pipeline.
 */
final readonly class AttributeSerializer
{
    public function __construct(private SerializerEngine $engine) {}

    /**
     * Serialize an object to a string.
     *
     * @param list<string> $groups Filter by group (empty = all).
     */
    public function serialize(object $object, ?string $format = null, array $groups = []): SerializationResult
    {
        $ref = new \ReflectionClass($object);
        $data = [];

        foreach ($ref->getProperties() as $property) {
            $attrs = $property->getAttributes(Serialize::class);
            $attr = $attrs !== [] ? $attrs[0]->newInstance() : null;

            if ($attr?->ignore) { continue; }

            if ($groups !== [] && $attr !== null && $attr->groups !== []) {
                if (array_intersect($groups, $attr->groups) === []) { continue; }
            }

            $wireName = $attr?->name ?? $property->getName();
            try { $data[$wireName] = $property->getValue($object); } catch (\Error) { $data[$wireName] = null; }
        }

        return $this->engine->serialize($data, $format);
    }

    /**
     * Deserialize a string payload into an object.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function deserialize(string $payload, string $class, ?string $format = null): object
    {
        $data = $this->engine->deserialize($payload, $format);

        $ref = new \ReflectionClass($class);
        $object = $ref->newInstanceWithoutConstructor();
        $nameMap = $this->buildNameMap($ref);

        foreach ($data as $wireName => $value) {
            $propName = $nameMap[$wireName] ?? $wireName;
            if ($ref->hasProperty($propName)) {
                $ref->getProperty($propName)->setValue($object, $value);
            }
        }

        return $object;
    }

    /** @return array<string, string> wireName→propertyName */
    private function buildNameMap(\ReflectionClass $ref): array
    {
        $map = [];
        foreach ($ref->getProperties() as $property) {
            $attrs = $property->getAttributes(Serialize::class);
            if ($attrs !== []) {
                /** @var Serialize $attr */
                $attr = $attrs[0]->newInstance();
                if ($attr->name !== null) {
                    $map[$attr->name] = $property->getName();
                }
            }
        }
        return $map;
    }
}
