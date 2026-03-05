# ADR-005 — Zero External Runtime Dependencies for Encoders

**Status:** Accepted  
**Date:** 2025-01-01  
**Standard:** ARFA 1.3

---

## Context

Each encoder must process wire formats without pulling in third-party libraries.
Adding packages like `symfony/yaml` or `ext-yaml` creates composer dependency constraints
that affect every project using this library.

---

## Decision

All 4 built-in encoders rely exclusively on PHP built-in functions and extensions:

| Encoder | Functions / Extensions |
|---------|------------------------|
| JsonEncoder | `json_encode` / `json_decode` (core) |
| XmlEncoder | `SimpleXMLElement`, `DOMDocument` (ext-simplexml, ext-dom) |
| CsvEncoder | `fputcsv` / `str_getcsv` via `php://temp` (core) |
| QueryStringEncoder | `http_build_query` / `parse_str` (core) |

Third-party encoders (YAML, MessagePack, etc.) can be registered at runtime
via `EncoderRegistry::register()` — they are not bundled.

---

## Consequences

**Positive:**
- `composer.json` `require` section stays empty (zero runtime deps except property-inspector)
- No version conflicts with consumer projects
- CI passes without extra system packages

**Negative:**
- No YAML support out of the box — caller must install and register a YamlEncoder

---

## Alternatives Rejected

- Bundling optional adapters: creates bloat and version matrix problems
- Making ext-yaml required: not universally available on shared hosting

---

## Related

- ADR-003: RFC compliance
- SPEC-001: Encoder contract
