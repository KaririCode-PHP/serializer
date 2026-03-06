# SPEC-001: Serializer Engine Specification

**Version:** 3.1.0 | **ARFA:** 1.3

## 1. Engine Contract

```php
final class SerializerEngine
{
    public function serialize(array $data, ?string $format, array $parameters): SerializationResult;
    public function deserialize(string $payload, ?string $format, array $parameters): array;
}
```

## 2. Serialize Flow

```
1. Resolve format (parameter → configuration default → 'json')
2. Resolve encoder from registry
3. Build SerializationContext (format + prettyPrint + parameters)
4. Call encoder.encode(data, context) → string payload
5. Return SerializationResult(originalData, payload, format)
```

## 3. Deserialize Flow

```
1. Resolve format (parameter → configuration default → 'json')
2. Resolve encoder from registry
3. Build SerializationContext (format + parameters)
4. Call encoder.decode(payload, context) → array
5. Return array
```

## 4. Context

`SerializationContext` carries: format, parameters map.
Immutable — `withFormat()` / `withParameters()` return new instances.

## 5. Error Handling

Encoders throw `SerializationException` (extends `RuntimeException`) with factory methods:
- `encodingFailed(format, reason)` — when encode() fails
- `decodingFailed(format, reason)` — when decode() fails
- `unsupportedFormat(format)` — when no encoder registered

## 6. Configuration

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| defaultFormat | string | `'json'` | Format when null passed to serialize/deserialize |
| prettyPrint | bool | false | Enable pretty-printing (format-dependent) |

## 7. AttributeSerializer

Reads `#[Serialize]` attributes:
- `name`: wire-name override (PHP `$firstName` → JSON `first_name`)
- `groups`: visibility control
- `ignore`: exclude from serialization

```php
$result = $serializer->serialize($dto, 'json', ['public']);
$object = $serializer->deserialize($json, UserDto::class, 'json');
```

## Quality Gate (kcode DevKit)

This component uses `kcode` (KaririCode DevKit global binary) for all quality checks.
See [ARFA Spec V4.0 §16](https://github.com/KaririCode-Framework/kariricode-devkit) for the full infrastructure standard.

### Bootstrap (once per machine/CI)

```bash
# Install kcode globally
curl -L https://github.com/KaririCode-Framework/kariricode-devkit/releases/latest/download/kcode.phar \
     -o /usr/local/bin/kcode && chmod +x /usr/local/bin/kcode

# Bootstrap project toolchain
kcode init
```

### Common Commands

| Command | Description |
|---------|-------------|
| `kcode quality` | Full pipeline: cs-fix → analyse → test |
| `kcode test` | PHPUnit + pcov coverage |
| `kcode analyse` | PHPStan L9 + Psalm L1 |
| `kcode cs:fix` | Apply KaririCode code style |
| `kcode cs:fix --check` | Dry-run style check |
| `kcode security` | `composer audit` vulnerability scan |

### Internal Tool Direct Access

After `kcode init`, tools are under `.kcode/vendor/bin/`:

```bash
# PHPStan with custom flags
.kcode/vendor/bin/phpstan analyse --level=9 --configuration=.kcode/phpstan.neon src/

# Psalm — auto-add #[Override]
.kcode/vendor/bin/psalm --config=.kcode/psalm.xml --alter --issues=MissingOverrideAttribute

# php-cs-fixer — preview diff
.kcode/vendor/bin/php-cs-fixer fix --config=.kcode/php-cs-fixer.php --dry-run --diff

# PHPUnit — single test filter
.kcode/vendor/bin/phpunit --configuration=.kcode/phpunit.xml.dist --filter=testMyMethod
```
