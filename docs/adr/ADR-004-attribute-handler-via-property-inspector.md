# ADR-004 — Attribute-Driven Serialization via Property Inspector

**Status:** Accepted  
**Date:** 2025-01-01  
**Standard:** ARFA 1.3

---

## Context

PHP objects need to be serialized to wire formats (JSON, XML, CSV) with field-level control.
Direct reflection scanning in each encoder violates SRP and creates tight coupling between
the serializer and PHP's reflection API.

The KaririCode ecosystem already provides `kariricode/property-inspector`, a generic
attribute scanner used by the sanitizer and validator libraries.

---

## Decision

All attribute-driven serialization goes through `SerializeAttributeHandler`, which implements
`PropertyAttributeHandler` and `PropertyChangeApplier` from the `property-inspector` contract.

The `AttributeSerializer` composes `PropertyInspector` + `SerializeAttributeHandler` instead
of calling PHP reflection directly.

```
Object → PropertyInspector → SerializeAttributeHandler → wire-name array → Encoder
```

---

## Consequences

**Positive:**
- Zero direct reflection calls in the serializer itself
- Groups filtering, field renaming, and ignore flags handled in one place
- Unannotated properties included automatically via `addUnannotatedProperty()`
- Deserialize path uses `applyChanges()` — same contract, symmetric approach

**Negative:**
- Adds `kariricode/property-inspector` as a runtime dependency

---

## Alternatives Rejected

- Inline reflection in each encoder: violates SRP, duplicates scanning logic
- Symfony Serializer integration: too heavy, no need for normalizer chain

---

## Related

- ADR-001: Format-agnostic engine
- SPEC-002: Attribute model
- SPEC-003: SerializeAttributeHandler contract
