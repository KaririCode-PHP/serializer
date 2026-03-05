# SPEC-003 — SerializeAttributeHandler Contract

**Version:** 1.0  
**Standard:** ARFA 1.3 / Spec V4.0  
**Class:** `KaririCode\Serializer\Core\SerializeAttributeHandler`

---

## Purpose

`SerializeAttributeHandler` is the pivot between PHP attribute metadata and the encoder
input array. It is scanned by `PropertyInspector` and must implement both
`PropertyAttributeHandler` (for scanning) and `PropertyChangeApplier` (for deserialization).

---

## Interface Methods

| Method | Role |
|--------|------|
| `handleAttribute(name, attribute, value)` | Called per property by PropertyInspector |
| `getProcessedPropertyValues()` | Returns wire-name → value map for encoding |
| `getProcessingResultMessages()` | Always returns `[]` (no messages in serializer) |
| `getProcessingResultErrors()` | Always returns `[]` (no errors in serializer) |
| `getNameMap()` | Returns wire-name → property-name reverse map for deserialization |
| `getIgnoredProperties()` | Returns list of ignored property names |
| `getEvaluatedProperties()` | Returns all property names that had `#[Serialize]` |
| `addUnannotatedProperty(name, value)` | Adds properties without `#[Serialize]` using property name as key |
| `setProcessedValues(values)` | Sets property-name → value map for `applyChanges()` |
| `applyChanges(object)` | Writes values to object properties via PropertyAccessor |

---

## Attribute Processing Rules

```
1. Non-#[Serialize] attribute → return null (passthrough)
2. #[Serialize(ignore: true)] → add to $ignoredProperties, skip
3. Groups filter (handler groups ≠ [], attr groups ≠ []):
   - intersection empty → skip (group-filtered)
4. Otherwise → add to $data[wireName] and $nameMap[wireName]
5. All annotated properties (including filtered/ignored) → $evaluatedProperties
6. Unannotated properties added via addUnannotatedProperty() using property name as wire name
   (only if not in $evaluatedProperties)
```

---

## Invariants

- `getEvaluatedProperties()` always includes ALL properties with `#[Serialize]`, even those
  filtered by group or marked as ignored. This prevents double-inclusion by `includeUnannotatedProperties`.
- `getProcessingResultMessages()` and `getProcessingResultErrors()` always return `[]`.
  The handler uses silent failure (skip) not error accumulation.
- `applyChanges()` silently skips non-existent properties (catches `ReflectionException`).

---

## Related

- ADR-004: Why property-inspector is used
- SPEC-002: `#[Serialize]` attribute model
