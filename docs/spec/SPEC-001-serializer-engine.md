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
