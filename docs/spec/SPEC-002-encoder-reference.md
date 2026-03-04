# SPEC-002: Encoder Reference

**Version:** 3.1.0 | **Encoders:** 4

## JsonEncoder

**Format:** `json` | **MIME:** `application/json`

| Feature | Implementation |
|---------|----------------|
| Encoding | `json_encode()` with `JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES` |
| Decoding | `json_decode()` with `assoc=true`, depth 512, `JSON_THROW_ON_ERROR` |
| Pretty-print | `JSON_PRETTY_PRINT` when `context.pretty = true` |
| Error handling | `\JsonException` → `SerializationException::encodingFailed/decodingFailed` |

## XmlEncoder

**Format:** `xml` | **MIME:** `application/xml`

| Feature | Implementation |
|---------|----------------|
| Encoding | `SimpleXMLElement` + `DOMDocument` for formatting |
| Decoding | `SimpleXMLElement` with `LIBXML_NOCDATA` |
| Root tag | Configurable via `context.root` (default: `"root"`) |
| Pretty-print | `DOMDocument::formatOutput` when `context.pretty = true` |
| Arrays | Integer keys become `<item>` elements |

## CsvEncoder

**Format:** `csv` | **MIME:** `text/csv`

| Feature | Implementation |
|---------|----------------|
| Encoding | `fputcsv()` to `php://temp` stream |
| Decoding | `str_getcsv()` per line |
| Separator | `context.separator` (default: `","`) |
| Enclosure | `context.enclosure` (default: `"\"`) |
| Header row | `context.header` (default: `true`) |

Input must be array-of-arrays (rows with consistent keys).

## QueryStringEncoder

**Format:** `query_string` | **MIME:** `application/x-www-form-urlencoded`

| Feature | Implementation |
|---------|----------------|
| Encoding | `http_build_query()` with RFC 3986 encoding |
| Decoding | `parse_str()` |
| Nesting | Supports `foo[bar]=baz` notation |
