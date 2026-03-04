<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;
use KaririCode\Serializer\Exception\SerializationException;

/**
 * Encodes/decodes flat arrays-of-arrays as CSV.
 *
 * Parameters: separator (string, ','), enclosure (string, '"'), header (bool, true).
 */
final readonly class CsvEncoder implements Encoder
{
    public function encode(array $data, SerializationContext $context): string
    {
        if ($data === []) { return ''; }

        $separator = (string) $context->getParameter('separator', ',');
        $enclosure = (string) $context->getParameter('enclosure', '"');
        $hasHeader = (bool) $context->getParameter('header', true);

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw SerializationException::encodingFailed('csv', 'Cannot open temp stream.');
        }

        $firstRow = reset($data);
        if ($hasHeader && is_array($firstRow)) {
            fputcsv($stream, array_keys($firstRow), $separator, $enclosure);
        }

        foreach ($data as $row) {
            if (is_array($row)) {
                fputcsv($stream, $row, $separator, $enclosure);
            }
        }

        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        return $output !== false ? $output : '';
    }

    public function decode(string $payload, SerializationContext $context): array
    {
        $separator = (string) $context->getParameter('separator', ',');
        $enclosure = (string) $context->getParameter('enclosure', '"');
        $hasHeader = (bool) $context->getParameter('header', true);

        $lines = array_filter(explode("\n", str_replace("\r\n", "\n", $payload)), static fn (string $l) => trim($l) !== '');
        if ($lines === []) { return []; }

        $rows = array_map(static fn (string $line) => str_getcsv($line, $separator, $enclosure), $lines);

        if ($hasHeader && count($rows) > 1) {
            $headers = array_shift($rows);
            return array_map(static fn (array $row) => array_combine($headers, array_pad($row, count($headers), '')), $rows);
        }

        return $rows;
    }

    public function supports(string $format): bool { return $format === 'csv'; }
    public function getFormat(): string { return 'csv'; }
}
