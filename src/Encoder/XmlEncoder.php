<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;
use KaririCode\Serializer\Exception\SerializationException;

/**
 * Encodes/decodes arrays as application/xml via SimpleXMLElement + DOMDocument.
 *
 * Uses `libxml_use_internal_errors` to suppress PHP notices on malformed XML decode.
 * Supports `root` and `pretty` context parameters.
 *
 * @package KaririCode\Serializer\Encoder
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final readonly class XmlEncoder implements Encoder
{
    /** @param array<mixed> $data */
    #[\Override]
    public function encode(array $data, SerializationContext $context): string
    {
        $rootParam = $context->getParameter('root', 'root');
        $rootTag = \is_string($rootParam) ? $rootParam : 'root';

        try {
            $xml = new \SimpleXMLElement("<{$rootTag}/>");
            $this->arrayToXml($data, $xml);
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = (bool) $context->getParameter('pretty', false);
            $xmlString = $xml->asXML();
            if (! \is_string($xmlString) || $xmlString === '') {
                throw SerializationException::encodingFailed('xml', 'asXML() returned empty.');
            }
            $dom->loadXML($xmlString);

            return $dom->saveXML() ?: '';
        } catch (\Throwable $e) {
            throw SerializationException::encodingFailed('xml', $e->getMessage());
        }
    }

    /** @return array<mixed> */
    #[\Override]
    public function decode(string $payload, SerializationContext $context): array
    {
        // Suppress PHP warnings emitted by SimpleXMLElement before throwing on invalid XML.
        // Without this, `new SimpleXMLElement('not xml')` emits 3 PHP notices that CI
        // treats as test-suite warnings (exit code 1 even with failOnWarning=false).
        $prevErrors = libxml_use_internal_errors(true);

        try {
            $xml = new \SimpleXMLElement($payload, LIBXML_NOCDATA);
            libxml_use_internal_errors($prevErrors);

            return $this->xmlToArray($xml);
        } catch (\Throwable $e) {
            libxml_use_internal_errors($prevErrors);
            libxml_clear_errors();

            throw SerializationException::decodingFailed('xml', $e->getMessage());
        }
    }

    #[\Override]
    public function supports(string $format): bool
    {
        return $format === 'xml';
    }

    #[\Override]
    public function getFormat(): string
    {
        return 'xml';
    }

    /** @param array<mixed> $data */
    private function arrayToXml(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            $tag = \is_int($key) ? 'item' : $key;
            if (\is_array($value)) {
                $child = $xml->addChild($tag);
                if ($child !== null) {
                    $this->arrayToXml($value, $child);
                }
            } else {
                $strValue = \is_scalar($value) || $value === null ? (string) $value : '';
                $xml->addChild($tag, htmlspecialchars($strValue, ENT_XML1));
            }
        }
    }

    /** @return array<mixed> */
    private function xmlToArray(\SimpleXMLElement $xml): array
    {
        $result = [];
        $children = $xml->children();
        if ($children === null) {
            return $result;
        }

        foreach ($children as $key => $child) {
            $value = $child->count() > 0 ? $this->xmlToArray($child) : (string) $child;
            if (isset($result[$key])) {
                if (! \is_array($result[$key]) || ! isset($result[$key][0])) {
                    $result[$key] = [$result[$key]];
                }
                $result[$key][] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
