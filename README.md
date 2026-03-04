# KaririCode\Serializer

**Multi-format serialization for PHP 8.4+ — JSON, XML, CSV, QueryString encoders, zero dependencies.**

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![ARFA](https://img.shields.io/badge/ARFA-1.3-orange)]()
[![Encoders](https://img.shields.io/badge/encoders-4-brightgreen)]()

Part of the [KaririCode Framework](https://github.com/kariricode) processing ecosystem.

## Why KaririCode\Serializer

- **4 built-in encoders**: JSON, XML, CSV, QueryString — extensible via Encoder contract
- **Attribute-driven**: `#[Serialize]` with name mapping, groups, and ignore support
- **Serialization groups**: control field visibility per consumer (public, admin, internal)
- **Zero external dependencies** — pure PHP 8.4+ built-ins (json, SimpleXML, fputcsv)
- **Same architecture** as Validator, Sanitizer, Transformer, Normalizer, Mapper

## Installation

```bash
composer require kariricode/serializer
```

## Quick Start

```php
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

$provider = new SerializerServiceProvider();
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

## All 4 Encoders

| Format | Encoder | MIME Type | PHP Functions |
|--------|---------|-----------|---------------|
| `json` | JsonEncoder | application/json | json_encode/decode with JSON_THROW_ON_ERROR |
| `xml` | XmlEncoder | application/xml | SimpleXMLElement + DOMDocument |
| `csv` | CsvEncoder | text/csv | fputcsv/str_getcsv via php://temp |
| `query_string` | QueryStringEncoder | application/x-www-form-urlencoded | http_build_query/parse_str |

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

## Engine API (Programmatic)

```php
$engine = (new SerializerServiceProvider())->createEngine();

// Serialize array to JSON
$result = $engine->serialize(['name' => 'Walmir', 'age' => 30], 'json');
$result->getPayload();       // '{"name":"Walmir","age":30}'
$result->getFormat();        // 'json'
$result->getPayloadSize();   // byte count

// Deserialize JSON to array
$data = $engine->deserialize('{"name":"Walmir"}', 'json');
// ['name' => 'Walmir']

// CSV with custom separator
$result = $engine->serialize([['a' => 1, 'b' => 2]], 'csv', ['separator' => ';']);
```

## Ecosystem Position

```
DPO Pipeline:     Input → Validator → Sanitizer → Transformer → Business Logic
Infra Pipeline:   Object ↔ Normalizer ↔ Array ↔ ★ Serializer ★ ↔ String
Cross-Layer:      Request DTO ↔ Mapper ↔ Domain Entity ↔ Mapper ↔ Response DTO
```

The Serializer converts **already-normalized arrays** into wire format strings for transport.

## Architecture

- ARFA 1.3 compliant (immutable context, reactive pipeline, observability events)
- Quality Directive V4.0 (all encoders `final readonly`, zero dependencies)
- RFC 8259 (JSON), RFC 4180 (CSV), RFC 3986 (URL encoding)
- See [docs/](docs/) for 3 ADRs, 2 SPECs, and compliance report

## Metrics

| Metric | Value |
|--------|-------|
| Source files | 19 |
| Source lines | 662 |
| Test files | 8 |
| Test lines | 396 |
| Total | **27 files / 1,058 lines** |
| Encoder classes | 4 |
| Supported formats | JSON, XML, CSV, QueryString |
| External dependencies | **0** |

## License

MIT © Walmir Silva — KaririCode Framework
