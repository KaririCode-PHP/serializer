# ADR-001: Encoder Registry Pattern

**Status:** Accepted  
**Date:** 2026-02-28  
**Component:** KaririCode\Serializer V3.1

## Context

The Serializer must support multiple wire formats (JSON, XML, CSV, QueryString) while
maintaining a single entry point. We need a mechanism to add, remove, or replace encoders
without modifying the engine.

## Decision

Use the same Registry pattern as Validator/Sanitizer/Transformer, but adapted for format-based
registration instead of alias-based:

```php
interface EncoderRegistry
{
    public function register(Encoder $encoder): void;    // auto-keyed by $encoder->getFormat()
    public function resolve(string $format): Encoder;
    public function has(string $format): bool;
}
```

Each Encoder self-identifies its format via `getFormat()`. The registry is keyed by format
string. Duplicate registration throws `SerializationException::duplicateEncoder()`.

## Alternatives Considered

- **Alias-based registry** (like Rule registries): Rejected — format names are inherent to the
  encoder, not user-assigned aliases.
- **Map in engine constructor**: Simpler but less extensible, harder to test.

## Consequences

- Adding a new format (e.g., YAML, MsgPack) requires only implementing `Encoder` and registering.
- Engine is format-agnostic — it delegates entirely to the registry.
- Default format is configured in `SerializerConfiguration`, not hardcoded.
