# KaririCode Serializer

<div align="center">

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![CI](https://github.com/KaririCode-Framework/kariricode-serializer/actions/workflows/ci.yml/badge.svg)](https://github.com/KaririCode-Framework/kariricode-serializer/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-22c55e.svg)](LICENSE)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-Level%209-4F46E5)](https://phpstan.org/)
[![Tests](https://img.shields.io/badge/Tests-100%25_pass-22c55e)](tests/)
[![Encoders](https://img.shields.io/badge/Encoders-4-22c55e)](https://kariricode.org)
[![Zero Runtime Deps](https://img.shields.io/badge/Runtime%20Deps-0-22c55e)](composer.json)
[![ARFA](https://img.shields.io/badge/ARFA-1.3-orange)](https://kariricode.org)
[![KaririCode Framework](https://img.shields.io/badge/KaririCode-Framework-orange)](https://kariricode.org)

**Multi-format serialization for PHP 8.4+ — JSON, XML, CSV, QueryString, zero dependencies.**

[Installation](#installation) · [Quick Start](#quick-start) · [Attribute-Driven](#attribute-driven-dto-serialization) · [All Encoders](#all-4-encoders) · [Architecture](#architecture)

</div>

---

## The Problem

Multi-format serialization for PHP always pulls in heavy dependencies or loses the data contract:

```php
// symfony/serializer: 7+ packages, complex config, annotation magic
// league/fractal: transformer boilerplate, no attribute support
// json_encode/decode alone: no groups, no field renaming, no XML/CSV

// Switching from JSON to XML means rewriting your serialization layer entirely.
```

## The Solution

```php
use KaririCode\Serializer\Provider\SerializerServiceProvider;

$engine = (new SerializerServiceProvider())->createEngine();

// One API, four formats — swap format string to change output
$json = $engine->serialize(['name' => 'Walmir', 'age' => 30], 'json');
$xml  = $engine->serialize(['name' => 'Walmir', 'age' => 30], 'xml', ['root' => 'user']);
$csv  = $engine->serialize([['name' => 'Walmir', 'age' => 30]], 'csv');
$qs   = $engine->serialize(['name' => 'Walmir', 'age' => 30], 'query_string');

// RFC 8259 (JSON) · RFC 4180 (CSV) · RFC 3986 (URL) — built-in compliance
```

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.4 or higher |
| ext-simplexml | Built-in (required for XML) |
| kariricode/property-inspector | ^2.0 |

---

## Installation

```bash
composer require kariricode/serializer
```

---

## Quick Start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use KaririCode\Serializer\Provider\SerializerServiceProvider;

$engine = (new SerializerServiceProvider())->createEngine();

// Serialize array to JSON
$result = $engine->serialize(['name' => 'Walmir', 'age' => 30], 'json');
echo $result->getPayload();  // '{"name":"Walmir","age":30}'

// Deserialize JSON to array
$data = $engine->deserialize('{"name":"Walmir"}', 'json');
// ['name' => 'Walmir']

// XML with custom root tag
$result = $engine->serialize(['name' => 'Walmir'], 'xml', ['root' => 'user']);
// <user><name>Walmir</name></user>
```

---

## Attribute-Driven DTO Serialization

```php
use KaririCode\Serializer\Attribute\Serialize;

class UserDto
{
    #[Serialize(name: 'user_id', groups: ['admin'])]
    public int $id = 42;

    #[Serialize(groups: ['public', 'admin'])]
    public string $name = 'Walmir Silva';

    #[Serialize(ignore: true)]
    public string $passwordHash = '...';
}

$provider   = new SerializerServiceProvider();
$serializer = $provider->createAttributeSerializer();

// Serialize to JSON (public group)
$json = $serializer->serialize(new UserDto(), 'json', ['public']);
// {"name":"Walmir Silva"}

// Serialize to JSON (admin group)
$json = $serializer->serialize(new UserDto(), 'json', ['admin']);
// {"user_id":42,"name":"Walmir Silva"}

// Deserialize from JSON
$dto = $serializer->deserialize('{"name":"Walmir"}', UserDto::class, 'json');
```

---

## All 4 Encoders

| Format | Encoder | MIME Type | PHP Functions |
|---|---|---|---|
| `json` | JsonEncoder | application/json | json_encode/decode with JSON_THROW_ON_ERROR |
| `xml` | XmlEncoder | application/xml | SimpleXMLElement + DOMDocument |
| `csv` | CsvEncoder | text/csv | fputcsv/str_getcsv via php://temp |
| `query_string` | QueryStringEncoder | application/x-www-form-urlencoded | http_build_query/parse_str |

---

## Adding Custom Encoders

```php
use KaririCode\Serializer\Contract\Encoder;

final readonly class YamlEncoder implements Encoder
{
    public function encode(array $data, SerializationContext $context): string { /* ... */ }
    public function decode(string $payload, SerializationContext $context): array { /* ... */ }
    public function supports(string $format): bool { return $format === 'yaml'; }
    public function getFormat(): string { return 'yaml'; }
}

$registry = $provider->createRegistry();
$registry->register(new YamlEncoder());
```

---

## Engine API (Programmatic)

```php
$engine = (new SerializerServiceProvider())->createEngine();

$result = $engine->serialize(['name' => 'Walmir', 'age' => 30], 'json');
$result->getPayload();      // '{"name":"Walmir","age":30}'
$result->getFormat();       // 'json'
$result->getPayloadSize();  // byte count

$data = $engine->deserialize('{"name":"Walmir"}', 'json');
// ['name' => 'Walmir']

// CSV with custom separator
$result = $engine->serialize([['a' => 1, 'b' => 2]], 'csv', ['separator' => ';']);
```

---

## Ecosystem Position

```
DPO Pipeline:     Input → Validator → Sanitizer → Transformer → Business Logic
Infra Pipeline:   Object ↔ Normalizer ↔ Array ↔ ★ Serializer ★ ↔ String
Cross-Layer:      Request DTO ↔ Mapper ↔ Domain Entity ↔ Mapper ↔ Response DTO
```

The Serializer converts **already-normalized arrays** into wire-format strings for transport. It is the last step in the infrastructure pipeline before the payload leaves the application.

---

## Architecture

### Source layout

```
src/
├── Attribute/       Serialize — name, groups, ignore annotation
├── Configuration/   SerializerConfiguration (immutable)
├── Contract/        Encoder · SerializationContext · SerializerEngine · EncoderRegistry
├── Core/            SerializerEngine · SerializationContextImpl · InMemoryEncoderRegistry
├── Encoder/         JsonEncoder · XmlEncoder · CsvEncoder · QueryStringEncoder
├── Exception/       SerializationException
└── Provider/        SerializerServiceProvider — factory for engine & attribute serializer
```

### Key design decisions

| Decision | Rationale | ADR |
|---|---|---|
| Format-agnostic engine | Swap format string — no code changes | [ADR-001](docs/adr/ADR-001-format-agnostic-engine.md) |
| Serialization groups | Field visibility per consumer without multiple DTOs | [ADR-002](docs/adr/ADR-002-serialization-groups.md) |
| RFC compliance | JSON 8259, CSV 4180, URL 3986 | [ADR-003](docs/adr/ADR-003-rfc-compliance.md) |
| Property Inspector integration | Attribute scanning delegated to kariricode/property-inspector | [ADR-004](docs/adr/ADR-004-attribute-handler-via-property-inspector.md) |
| Zero encoder dependencies | All 4 encoders use only PHP built-ins | [ADR-005](docs/adr/ADR-005-zero-dependency-encoders.md) |

### Specifications

| Spec | Covers |
|---|---|
| [SPEC-001](docs/spec/SPEC-001-encoder-contract.md) | Encoder interface and registration |
| [SPEC-002](docs/spec/SPEC-002-attribute-model.md) | `#[Serialize]` groups and field mapping |
| [SPEC-003](docs/spec/SPEC-003-serialize-attribute-handler.md) | `SerializeAttributeHandler` contract and invariants |

---

## Project Stats

| Metric | Value |
|---|---|
| PHP source files | 20 |
| Source lines | ~810 |
| Test files | 13 |
| Test lines | ~700 |
| External runtime dependencies | 1 (kariricode/property-inspector) |
| Encoder classes | 4 |
| Supported formats | JSON, XML, CSV, QueryString |
| PHPStan level | 9 |
| PHP version | 8.4+ |
| ARFA compliance | 1.3 |

---

## Contributing

```bash
git clone https://github.com/KaririCode-Framework/kariricode-serializer.git
cd kariricode-serializer
composer install
kcode init
kcode quality  # Must pass before opening a PR
```

---

## License

[MIT License](LICENSE) © [Walmir Silva](mailto:community@kariricode.org)

---

<div align="center">

Part of the **[KaririCode Framework](https://kariricode.org)** ecosystem.

[kariricode.org](https://kariricode.org) · [GitHub](https://github.com/KaririCode-Framework/kariricode-serializer) · [Packagist](https://packagist.org/packages/kariricode/serializer) · [Issues](https://github.com/KaririCode-Framework/kariricode-serializer/issues)

</div>
