<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;
use KaririCode\Serializer\Exception\SerializationException;

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
        try {
            $xml = new \SimpleXMLElement($payload, LIBXML_NOCDATA);

            return $this->xmlToArray($xml);
        } catch (\Throwable $e) {
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
            $keyStr = $key;
            if (isset($result[$keyStr])) {
                if (! \is_array($result[$keyStr]) || ! isset($result[$keyStr][0])) {
                    $result[$keyStr] = [$result[$keyStr]];
                }
                $result[$keyStr][] = $value;
            } else {
                $result[$keyStr] = $value;
            }
        }

        return $result;
    }
}
