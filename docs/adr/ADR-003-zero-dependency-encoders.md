# ADR-003: Zero-Dependency Encoder Implementations

**Status:** Accepted  
**Date:** 2026-02-28  
**Component:** KaririCode\Serializer V3.1

## Context

The KaririCode Framework maintains zero external dependencies. Encoders must be implemented
using only PHP built-ins.

## Decision

| Format | PHP Functions Used |
|--------|-------------------|
| JSON | `json_encode()`, `json_decode()` with `JSON_THROW_ON_ERROR` |
| XML | `SimpleXMLElement`, `DOMDocument` (ext-dom, bundled by default) |
| CSV | `fputcsv()`, `str_getcsv()`, `php://temp` stream |
| QueryString | `http_build_query()`, `parse_str()` |

All functions are available in PHP 8.4 standard distribution. `ext-dom` is bundled (not
external). No PECL or Composer dependencies required.

## Alternatives Considered

- **ext-msgpack for MsgPack**: Rejected — PECL dependency. Can be added as user-registered encoder.
- **ext-yaml**: Rejected — PECL dependency. Same approach.

## Consequences

- `composer install` with no system dependencies beyond PHP 8.4.
- Binary formats (MsgPack, Protobuf) available as user extensions.
- XML encoder handles common cases; complex XML schemas need custom encoders.
