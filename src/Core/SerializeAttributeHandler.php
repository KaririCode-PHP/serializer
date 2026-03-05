<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Core;

use KaririCode\PropertyInspector\Contract\PropertyAttributeHandler;
use KaririCode\PropertyInspector\Contract\PropertyChangeApplier;
use KaririCode\PropertyInspector\Utility\PropertyAccessor;
use KaririCode\Serializer\Attribute\Serialize;

/**
 * Collects #[Serialize] field metadata from properties with support for:
 * - `ignore`: skip the property completely
 * - `name`: custom wire name (serialized key)
 * - `groups`: serialization group filtering
 *
 * @package KaririCode\Serializer\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.2.0 ARFA 1.3
 */
final class SerializeAttributeHandler implements PropertyAttributeHandler, PropertyChangeApplier
{
    /** @var array<string, mixed> Keyed by wire name (may differ from property name). */
    private array $data = [];

    /** @var array<string, string> wireName → propertyName reverse map for deserialization. */
    private array $nameMap = [];

    /** @var list<string> Property names that are explicitly ignored. */
    private array $ignoredProperties = [];

    /**
     * @var list<string> ALL property names that had #[Serialize] on them
     * (whether included or filtered-out by group) — prevents the un-annotated
     * fallback from re-including group-filtered properties.
     */
    private array $evaluatedProperties = [];

    /** @var array<string, mixed> */
    private array $processedValues = [];

    /**
     * @param list<string> $groups Active groups filter (empty = all groups).
     */
    public function __construct(private readonly array $groups = [])
    {
    }

    #[\Override]
    public function handleAttribute(string $propertyName, object $attribute, mixed $value): mixed
    {
        if (! $attribute instanceof Serialize) {
            return null;
        }

        // Mark this property as evaluated regardless of what happens next
        $this->evaluatedProperties[] = $propertyName;

        // Track ignored properties so the serializer can avoid including them
        if ($attribute->ignore) {
            $this->ignoredProperties[] = $propertyName;

            return null;
        }

        // Apply group filter
        if ($this->groups !== [] && $attribute->groups !== []) {
            if (array_intersect($this->groups, $attribute->groups) === []) {
                return null;
            }
        }

        $wireName = $attribute->name ?? $propertyName;
        $this->data[$wireName] = $value;
        $this->nameMap[$wireName] = $propertyName;

        return null;
    }

    #[\Override]
    public function getProcessedPropertyValues(): array
    {
        return $this->data;
    }

    #[\Override]
    public function getProcessingResultMessages(): array
    {
        return [];
    }

    #[\Override]
    public function getProcessingResultErrors(): array
    {
        return [];
    }

    /**
     * Returns the wire→property name reverse map for deserialization.
     *
     * @return array<string, string>
     */
    public function getNameMap(): array
    {
        return $this->nameMap;
    }

    /**
     * Returns the list of property names explicitly marked as ignored.
     *
     * @return list<string>
     */
    public function getIgnoredProperties(): array
    {
        return $this->ignoredProperties;
    }

    /**
     * Returns ALL property names that had #[Serialize] on them,
     * including group-filtered ones  — used to prevent the un-annotated
     * fallback from re-including them.
     *
     * @return list<string>
     */
    public function getEvaluatedProperties(): array
    {
        return $this->evaluatedProperties;
    }

    /**
     * Add a property that had no #[Serialize] attribute — include it with its
     * property name as the wire name (default inclusive behavior).
     */
    public function addUnannotatedProperty(string $propertyName, mixed $value): void
    {
        $this->data[$propertyName] = $value;
    }

    /** @param array<string, mixed> $values */
    public function setProcessedValues(array $values): void
    {
        $this->processedValues = $values;
    }

    #[\Override]
    public function applyChanges(object $object): void
    {
        foreach ($this->processedValues as $property => $value) {
            try {
                new PropertyAccessor($object, $property)->setValue($value);
            } catch (\ReflectionException) {
                // Property doesn't exist — skip silently
            }
        }
    }
}
