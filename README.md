# KaririCode Serializer

<div align="center">

[![CI](https://github.com/KaririCode-Framework/kariricode-serializer/actions/workflows/ci.yml/badge.svg)](https://github.com/KaririCode-Framework/kariricode-serializer/actions/workflows/ci.yml)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-22c55e.svg)](LICENSE)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-Level%209-4F46E5)](https://phpstan.org/)
[![Psalm](https://img.shields.io/badge/Psalm-Level%201-4F46E5)](https://psalm.dev/)
[![Tests](https://img.shields.io/badge/Tests-100%25_pass-22c55e)](tests/)
[![Coverage](https://img.shields.io/badge/Coverage-100%25-22c55e)](tests/)
[![Encoders](https://img.shields.io/badge/Encoders-4-22c55e)](docs/spec/SPEC-002-encoder-reference.md)
[![ARFA](https://img.shields.io/badge/ARFA-1.3-F97316)](https://kariricode.org)
[![KaririCode Framework](https://img.shields.io/badge/KaririCode-Framework-F97316)](https://kariricode.org)

**Multi-format serialization for PHP 8.4+.**  
JSON · XML · CSV · QueryString · #[Serialize] attributes · zero dependencies.

[Installation](#installation) · [Quick Start](#quick-start) · [Attribute API](#attribute-driven-dto-serialization) · [All Encoders](#all-4-encoders) · [CI Integration](#ci-integration) · [Architecture](#architecture)

</div>

---

## The Problem

Different API consumers demand different wire formats — REST clients expect JSON, B2B integrations require XML, data pipelines need CSV, and legacy systems use query strings. Writing separate serializers for each format creates unsustainable duplication:

```php
// Repeated across every endpoint, every format
$json  = json_encode($dto);        // no field control, no groups
$xml   = $this->buildXmlManually($dto);   // 40 lines of DOMDocument boilerplate
$csv   = implode(',', array_values(get_object_vars($dto))); // completely unpredictable
```

## The Solution

```php
use KaririCode\Serializer\Provider\SerializerServiceProvider;
use KaririCode\Serializer\Attribute\Serialize;

final readonly class ProductDTO
{
    #[Serialize(groups: ['api', 'export'])]
    public string $name;

    #[Serialize(groups: ['api'])]
    public float $price;

    #[Serialize(groups: ['export'], name: 'SKU')]
    public string $sku;
}

$provider   = new SerializerServiceProvider();
$serializer = $provider->createAttributeSerializer($provider->createConfiguration());

$dto = new ProductDTO('Laptop Pro', 2999.99, 'LAP-PRO-001');

// Format-agnostic serialization — same DTO, any format
$json  = $serializer->serialize($dto, 'json', groups: ['api']);
$csv   = $serializer->serialize($dto, 'csv',  groups: ['export']);
$xml   = $serializer->serialize($dto, 'xml',  groups: ['api', 'export']);
$qs    = $serializer->serialize($dto, 'query_string', groups: ['api']);
```

---

## Features

- ✅ **4 built-in encoders** — JSON, XML, CSV, QueryString — same `serialize()` call
- ✅ **Serialization groups** — control which fields appear in which context
- ✅ **Field renaming** — `#[Serialize(name: 'SKU')]` maps to the desired wire name
- ✅ **Attribute-driven API** — `#[Serialize]` with groups, aliases, ordering
- ✅ **Zero external dependencies** — only `kariricode/property-inspector` for reflection
- ✅ **PHPStan Level 9 + Psalm Level 1** — full static-analysis compliance
- ✅ **100% test coverage** — 100% Classes/Methods/Lines
- ✅ **ARFA 1.3 compliant** — `final readonly` domain classes

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.4+ |
| Composer | 2.x |
| kariricode/property-inspector | ^2.0 |

---

## Installation

```bash
composer require kariricode/serializer
```

---

## Quick Start

### 1. Direct Encoder API

```php
use KaririCode\Serializer\Provider\SerializerServiceProvider;

$provider   = new SerializerServiceProvider();
$serializer = $provider->createSerializer();

// Serialize any array to any format
$json = $serializer->encode(['name' => 'Alice', 'age' => 30], 'json');
$xml  = $serializer->encode(['name' => 'Alice', 'age' => 30], 'xml');
$csv  = $serializer->encode([['name', 'age'], ['Alice', 30]], 'csv');
$qs   = $serializer->encode(['name' => 'Alice', 'age' => 30], 'query_string');
```

### 2. Attribute API (DTO-driven)

```php
use KaririCode\Serializer\Attribute\Serialize;

final readonly class InvoiceDTO
{
    #[Serialize(groups: ['api', 'export'])]
    public string $id;

    #[Serialize(groups: ['api'], name: 'totalAmount')]
    public float $total;

    #[Serialize(groups: ['export'])]
    public string $customerName;
}
```

---

## Real-World Use Cases

### Use Case 1 — REST API JSON Response with Groups

```php
// Public API — only expose id, name, price
$json = $serializer->serialize($productDto, 'json', groups: ['api']);
// Internal export — expose all fields including cost_price
$csv  = $serializer->serialize($productDto, 'csv',  groups: ['export']);
```

### Use Case 2 — B2B XML Integration

```php
final readonly class InvoiceDTO
{
    #[Serialize(groups: ['b2b'], name: 'InvoiceNumber')]
    public string $id;

    #[Serialize(groups: ['b2b'], name: 'TotalAmount')]
    public float $total;
}

$xml = $serializer->serialize($invoiceDto, 'xml', groups: ['b2b']);
// <root><InvoiceNumber>INV-001</InvoiceNumber><TotalAmount>1500.00</TotalAmount></root>
```

### Use Case 3 — CSV Data Export Pipeline

```php
// Export 50k rows to CSV for data warehouse
foreach ($productBatch as $product) {
    $csvRow = $serializer->serialize($product, 'csv', groups: ['export']);
    $stream->write($csvRow);
}
```

### Use Case 4 — Legacy Query String API Integration

```php
// Third-party API requires form-encoded query string
$queryString = $serializer->serialize($requestDto, 'query_string');
$response    = $httpClient->get("https://legacy.api.com/endpoint?{$queryString}");
```

---

## All 4 Encoders

| Format | Alias | Best For |
|--------|-------|----------|
| **JSON** | `json` | REST APIs, frontend consumption, storage |
| **XML** | `xml` | B2B integrations, SOAP-adjacent, config files |
| **CSV** | `csv` | Data exports, spreadsheets, data pipelines |
| **QueryString** | `query_string` | URL parameters, form submissions, legacy APIs |

### Decoder (reverse)

```php
$array = $serializer->decode($jsonString, 'json');
$array = $serializer->decode($xmlString,  'xml');
$array = $serializer->decode($csvString,  'csv');
$array = $serializer->decode($qs,         'query_string');
```

---

## Attribute-Driven DTO Serialization

The `#[Serialize]` attribute gives per-field control over wire format representation:

```php
use KaririCode\Serializer\Attribute\Serialize;

final readonly class OrderDTO
{
    #[Serialize(groups: ['api', 'admin'], name: 'order_id')]
    public string $id;

    #[Serialize(groups: ['api'])]
    public string $status;

    #[Serialize(groups: ['admin'], name: 'internal_cost')]
    public float $costPrice;

    // Fields without #[Serialize] are excluded from ALL groups
    public string $secretToken;
}
```

---

## CI Integration

```yaml
- name: Run quality pipeline
  run: |
    kcode init
    sed -i 's/beStrictAboutCoverageMetadata="true"/beStrictAboutCoverageMetadata="false"/' .kcode/phpunit.xml.dist
    kcode quality
```

| Tool | Level | Result |
|------|-------|--------|
| `php-cs-fixer` | KaririCode standard | ✅ |
| `phpstan` | Level 9 | ✅ |
| `psalm` | Level 1 | ✅ |
| `phpunit` + `pcov` | 100% coverage | ✅ |

---

## Architecture

### Design Decisions

| Decision | Rationale | ADR |
|----------|-----------|-----|
| Encoder registry keyed by format | Pluggable new formats without engine changes | [ADR-001](docs/adr/ADR-001-encoder-registry-pattern.md) |
| `#[Serialize]` attribute groups | Context-sensitive field exposure per consumer | [ADR-002](docs/adr/ADR-002-attribute-serialization-groups.md) |
| Zero runtime deps for encoders | JSON/XML/CSV via PHP 8.4+ built-ins only | [ADR-003](docs/adr/ADR-003-zero-dependency-encoders.md) |
| `PropertyInspector` for reflection | Caching; no per-call `new ReflectionClass()` | [ADR-004](docs/adr/ADR-004-property-inspector-caching.md) |

### Project Stats

| Metric | Value |
|--------|-------|
| Encoder types | 4 (JSON, XML, CSV, QueryString) |
| External dependencies | 1 (`kariricode/property-inspector`) |
| Line coverage | 100% |
| PHPStan level | 9 (0 errors) |
| Psalm level | 1 (0 errors) |

---

## Ecosystem Integration

| Library | Integration |
|---------|-------------|
| [kariricode/normalizer](https://github.com/KaririCode-Framework/kariricode-normalizer) | Normalize complex types before serialization |
| [kariricode/sanitizer](https://github.com/KaririCode-Framework/kariricode-sanitizer) | Sanitize before reverse-serializing (import) |
| [kariricode/validator](https://github.com/KaririCode-Framework/kariricode-validator) | Validate deserialized payload before processing |
| [kariricode/property-inspector](https://github.com/KaririCode-Framework/kariricode-property-inspector) | Reflection caching for `#[Serialize]` scanning |
| [kariricode/devkit](https://github.com/KaririCode-Framework/kariricode-devkit) | `kcode quality` CI pipeline runner |

---

## Contributing

1. Fork the repository  
2. Create a feature branch: `git checkout -b feat/my-encoder`  
3. Run: `kcode quality` — all 4 tools must pass  
4. Submit a pull request against `main`

---

<div align="center">

**Part of the [KaririCode Framework](https://kariricode.org) ecosystem.**

[![GitHub](https://img.shields.io/badge/GitHub-KaririCode-181717?logo=github)](https://github.com/KaririCode-Framework)
[![Packagist](https://img.shields.io/badge/Packagist-kariricode%2Fserializer-F28D1A?logo=packagist&logoColor=white)](https://packagist.org/packages/kariricode/serializer)

*Built with ❤️ by [Walmir Silva](https://github.com/walmir-silva) · [kariricode.org](https://kariricode.org)*

</div>
