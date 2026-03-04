# KaririCode\Serializer — Architecture Documentation

**Version:** 3.1.0 | **ARFA:** 1.3 | **QD:** V4.0

## Architecture Overview

```
┌──────────────────────────────────────────────────────────────┐
│                     SerializerEngine                         │
│  ┌──────────────┐   ┌─────────┐   ┌──────────────────────┐  │
│  │ EncoderReg.  │──▶│ Encoder │──▶│ SerializationResult  │  │
│  │ (4 formats)  │   │ (JSON/  │   │ (payload + metadata) │  │
│  └──────────────┘   │ XML/..) │   └──────────────────────┘  │
│                     └─────────┘                              │
└────────────┬─────────────────────────────────────────────────┘
             │
  ┌──────────▼───────────┐
  │ AttributeSerializer   │   #[Serialize(name: 'first_name', groups: ['api'])]
  │ (Object ↔ String)    │
  └──────────────────────┘
```

## ARFA 1.3 Compliance

| Principle | Implementation |
|-----------|----------------|
| P1 Immutable State | SerializationContextImpl is `final readonly`, with-methods return new instances |
| P2 Reactive Flow | Encoder delegation pipeline |
| P3 Adaptive Context | Context carries format, parameters, pretty-print flag |
| P4 Protocol Agnostic | Multi-format: JSON, XML, CSV, QueryString |
| P5 Observability | SerializationStarted/CompletedEvent |

## Role in Infrastructure Pipeline

```
Object ↔ Normalizer ↔ Array ↔ ★ Serializer ★ ↔ String (JSON/XML/CSV)
```

**Serializer encodes/decodes arrays into wire format strings.**
It receives already-normalized data (primitives and arrays) from the Normalizer
and converts them to format-specific strings for transport.

## Directory Structure

```
src/
├── Attribute/          #[Serialize] PHP 8.4 attribute (name, groups, ignore)
├── Configuration/      SerializerConfiguration (defaultFormat, prettyPrint)
├── Contract/           Encoder, SerializationContext, EncoderRegistry
├── Core/               SerializerEngine, SerializationContextImpl, InMemoryEncoderRegistry, AttributeSerializer
├── Encoder/            JsonEncoder, XmlEncoder, CsvEncoder, QueryStringEncoder
├── Event/              Started/Completed events
├── Exception/          SerializationException
├── Integration/        ProcessorBridge (ARFA 1.3)
├── Provider/           SerializerServiceProvider (4 formats)
└── Result/             SerializationResult
```

## Documentation Index

| Document | Description |
|----------|-------------|
| [ADR-001](adr/ADR-001-encoder-registry-pattern.md) | Encoder registry pattern |
| [ADR-002](adr/ADR-002-attribute-serialization-groups.md) | Serialization groups |
| [ADR-003](adr/ADR-003-zero-dependency-encoders.md) | Zero-dependency encoder strategy |
| [SPEC-001](spec/SPEC-001-serializer-engine.md) | Serializer engine specification |
| [SPEC-002](spec/SPEC-002-encoder-reference.md) | Encoder reference (4 formats) |
| [QD-V40](quality/QD-V40-COMPLIANCE.md) | Quality Directive V4.0 compliance |

## References

- RFC 8259 (JSON), RFC 4180 (CSV), RFC 3986 (URL encoding)
- ARFA 1.3 Specification §5.3 — DPO Processing Algorithm
- *Framework Design Guidelines* (Cwalina & Abrams) — Extension point design
