<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;
use KaririCode\Serializer\Exception\SerializationException;

/**
 * Encodes/decodes flat arrays-of-arrays as RFC 4180-compliant CSV via php://temp.
 *
 * Parameters: separator (string, ','), enclosure (string, '"'), header (bool, true).
 *
 * @package KaririCode\Serializer\Encoder
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class CsvEncoder implements Encoder
{
    /** @param array<mixed> $data */
    #[\Override]
    public function encode(array $data, SerializationContext $context): string
    {
        if ($data === []) {
            return '';
        }

        $separatorParam = $context->getParameter('separator');
        $enclosureParam = $context->getParameter('enclosure');
        $separator = \is_string($separatorParam) ? $separatorParam : ',';
        $enclosure = \is_string($enclosureParam) ? $enclosureParam : '"';
        $hasHeader = (bool) $context->getParameter('header', true);

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw SerializationException::encodingFailed('csv', 'Cannot open temp stream.');
        }

        $firstRow = reset($data);
        if ($hasHeader && \is_array($firstRow)) {
            /** @var array<int|string, string> $keys */
            $keys = array_keys($firstRow);
            fputcsv($stream, $keys, $separator, $enclosure, escape: '\\');
        }

        foreach ($data as $row) {
            if (\is_array($row)) {
                /** @var array<int|string, bool|float|int|string|null> $row */
                fputcsv($stream, $row, $separator, $enclosure, escape: '\\');
            }
        }

        rewind($stream);
        $output = stream_get_contents($stream);
        fclose($stream);

        return $output !== false ? $output : '';
    }

    /** @return array<mixed> */
    #[\Override]
    public function decode(string $payload, SerializationContext $context): array
    {
        $separatorParam = $context->getParameter('separator');
        $enclosureParam = $context->getParameter('enclosure');
        $separator = \is_string($separatorParam) ? $separatorParam : ',';
        $enclosure = \is_string($enclosureParam) ? $enclosureParam : '"';
        $hasHeader = (bool) $context->getParameter('header', true);

        $lines = array_filter(
            explode("\n", str_replace("\r\n", "\n", $payload)),
            static fn (string $l) => trim($l) !== '',
        );

        if ($lines === []) {
            return [];
        }

        $rows = array_values(
            array_map(static fn (string $line) => str_getcsv($line, $separator, $enclosure, escape: '\\'), $lines),
        );

        if ($hasHeader && \count($rows) > 1) {
            /** @var list<string|null> $rawHeaders */
            $rawHeaders = array_shift($rows);
            $headers = array_map(static fn (string|null $v): string => (string) $v, $rawHeaders);

            /** @var array<string, mixed> $result */
            $result = [];
            foreach ($rows as $row) {
                $padded = array_pad($row, \count($headers), '');
                $result[] = array_combine($headers, $padded);
            }

            return $result;
        }

        return $rows;
    }

    #[\Override]
    public function supports(string $format): bool
    {
        return $format === 'csv';
    }

    #[\Override]
    public function getFormat(): string
    {
        return 'csv';
    }
}
