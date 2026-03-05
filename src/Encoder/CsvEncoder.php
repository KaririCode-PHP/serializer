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
    private function str(mixed $value, string $default): string
    {
        return \is_string($value) ? $value : $default;
    }

    /** @param array<mixed> $data */
    #[\Override]
    public function encode(array $data, SerializationContext $context): string
    {
        if ($data === []) {
            return '';
        }

        $separator = $this->str($context->getParameter('separator'), ',');
        $enclosure = $this->str($context->getParameter('enclosure'), '"');
        $hasHeader = (bool) $context->getParameter('header', true);

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw SerializationException::encodingFailed('csv', 'Cannot open temp stream.');
        }

        $firstRow = reset($data);
        if ($hasHeader && \is_array($firstRow)) {
            /** @var array<int|string, string> $keys */
            $keys = array_keys($firstRow);
            fputcsv($stream, $keys, $separator, $enclosure);
        }

        foreach ($data as $row) {
            if (\is_array($row)) {
                /** @var array<int|string, bool|float|int|string|null> $row */
                fputcsv($stream, $row, $separator, $enclosure);
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
        $separator = $this->str($context->getParameter('separator'), ',');
        $enclosure = $this->str($context->getParameter('enclosure'), '"');
        $hasHeader = (bool) $context->getParameter('header', true);

        $lines = array_filter(
            explode("\n", str_replace("\r\n", "\n", $payload)),
            static fn (string $l) => trim($l) !== '',
        );

        if ($lines === []) {
            return [];
        }

        $rows = array_values(
            array_map(static fn (string $line) => str_getcsv($line, $separator, $enclosure), $lines),
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
