<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Core;

use KaririCode\PropertyInspector\AttributeAnalyzer;
use KaririCode\PropertyInspector\Utility\PropertyInspector;
use KaririCode\Serializer\Attribute\Serialize;
use KaririCode\Serializer\Result\SerializationResult;

/**
 * Serializes objects to format strings using #[Serialize] attributes.
 *
 * Uses kariricode/property-inspector for attribute scanning.
 * Properties WITHOUT #[Serialize] are included automatically (using property
 * name as the wire name) — only `#[Serialize(ignore: true)]` omits one.
 *
 * @package KaririCode\Serializer\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.2.0 ARFA 1.3
 */
final readonly class AttributeSerializer
{
    private PropertyInspector $inspector;

    public function __construct(private SerializerEngine $engine)
    {
        $this->inspector = new PropertyInspector(
            new AttributeAnalyzer(Serialize::class),
        );
    }

    /**
     * Serialize an object to a string.
     *
     * @param list<string> $groups Filter by group (empty = all).
     */
    public function serialize(object $object, ?string $format = null, array $groups = []): SerializationResult
    {
        $handler = new SerializeAttributeHandler($groups);

        /** @var SerializeAttributeHandler $handler */
        $handler = $this->inspector->inspect($object, $handler);

        // Include properties WITHOUT #[Serialize] using their property name
        $this->includeUnannotatedProperties($object, $handler);

        return $this->engine->serialize($handler->getProcessedPropertyValues(), $format);
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

        // Scan a proto-instance to build wire→property name map
        $ref = new \ReflectionClass($class);
        /** @var T $proto */
        $proto = $ref->newInstanceWithoutConstructor();

        $handler = new SerializeAttributeHandler();

        /** @var SerializeAttributeHandler $handler */
        $handler = $this->inspector->inspect($proto, $handler);
        $nameMap = $handler->getNameMap();

        $object = $ref->newInstanceWithoutConstructor();

        $resolved = [];
        foreach ($data as $wireName => $value) {
            $propName = $nameMap[$wireName] ?? $wireName;
            $resolved[$propName] = $value;
        }

        $handler->setProcessedValues($resolved);
        $handler->applyChanges($object);

        /** @var T $object */
        return $object;
    }

    /**
     * Includes properties that have no #[Serialize] attribute using their property
     * name as the wire name (unless already handled by the handler or skipped via ignore).
     */
    private function includeUnannotatedProperties(object $object, SerializeAttributeHandler $handler): void
    {
        $ref = new \ReflectionClass($object);
        // Exclude ALL annotated properties (handled + ignored + group-filtered)
        $evaluatedProps = $handler->getEvaluatedProperties();

        foreach ($ref->getProperties() as $property) {
            $propName = $property->getName();

            // Skip any property that had #[Serialize] on it (already processed)
            if (\in_array($propName, $evaluatedProps, strict: true)) {
                continue;
            }

            // Include with property name as wire name
            try {
                $value = $property->getValue($object);
            } catch (\Error) {
                $value = null;
            }

            $handler->addUnannotatedProperty($propName, $value);
        }
    }
}
