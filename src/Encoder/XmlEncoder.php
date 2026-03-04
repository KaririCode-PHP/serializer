<?php

declare(strict_types=1);

namespace KaririCode\Serializer\Encoder;

use KaririCode\Serializer\Contract\Encoder;
use KaririCode\Serializer\Contract\SerializationContext;
use KaririCode\Serializer\Exception\SerializationException;

final readonly class XmlEncoder implements Encoder
{
    public function encode(array $data, SerializationContext $context): string
    {
        $rootTag = (string) $context->getParameter('root', 'root');

        try {
            $xml = new \SimpleXMLElement("<{$rootTag}/>");
            $this->arrayToXml($data, $xml);
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = (bool) $context->getParameter('pretty', false);
            $dom->loadXML($xml->asXML());
            return $dom->saveXML() ?: '';
        } catch (\Throwable $e) {
            throw SerializationException::encodingFailed('xml', $e->getMessage());
        }
    }

    public function decode(string $payload, SerializationContext $context): array
    {
        try {
            $xml = new \SimpleXMLElement($payload, LIBXML_NOCDATA);
            return $this->xmlToArray($xml);
        } catch (\Throwable $e) {
            throw SerializationException::decodingFailed('xml', $e->getMessage());
        }
    }

    public function supports(string $format): bool { return $format === 'xml'; }
    public function getFormat(): string { return 'xml'; }

    private function arrayToXml(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            $tag = is_int($key) ? 'item' : $key;
            if (is_array($value)) {
                $child = $xml->addChild($tag);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($tag, htmlspecialchars((string) ($value ?? ''), ENT_XML1));
            }
        }
    }

    private function xmlToArray(\SimpleXMLElement $xml): array
    {
        $result = [];
        foreach ($xml->children() as $key => $child) {
            $value = $child->count() > 0 ? $this->xmlToArray($child) : (string) $child;
            if (isset($result[$key])) {
                if (!is_array($result[$key]) || !isset($result[$key][0])) {
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
