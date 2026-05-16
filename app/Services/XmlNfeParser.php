<?php

namespace App\Services;

use RuntimeException;
use SimpleXMLElement;

class XmlNfeParser
{
    public function parse(string $xmlContent): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('XML de NF-e inválido.');
        }

        $chave = $this->extractInvoiceKey($xml);
        $itens = $this->extractItems($xml);

        return [
            'chave_nfe' => $chave,
            'itens' => $itens,
        ];
    }

    protected function extractInvoiceKey(SimpleXMLElement $xml): ?string
    {
        $infNfe = $xml->xpath("//*[local-name()='infNFe']");
        if (! empty($infNfe) && isset($infNfe[0]['Id'])) {
            $id = (string) $infNfe[0]['Id'];
            $digits = preg_replace('/\D+/', '', $id);

            return $digits !== '' ? $digits : null;
        }

        $chNFe = $xml->xpath("//*[local-name()='chNFe']");
        if (! empty($chNFe)) {
            $digits = preg_replace('/\D+/', '', (string) $chNFe[0]);

            return $digits !== '' ? $digits : null;
        }

        return null;
    }

    protected function extractItems(SimpleXMLElement $xml): array
    {
        $detNodes = $xml->xpath("//*[local-name()='det']");
        $items = [];

        foreach ($detNodes ?: [] as $det) {
            $cProd = $det->xpath(".//*[local-name()='cProd']");
            $qCom = $det->xpath(".//*[local-name()='qCom']");

            $sku = isset($cProd[0]) ? trim((string) $cProd[0]) : null;
            $quantity = isset($qCom[0]) ? (int) round((float) $qCom[0]) : 0;

            if ($sku && $quantity > 0) {
                $items[] = [
                    'sku' => $sku,
                    'quantidade' => $quantity,
                ];
            }
        }

        return $items;
    }
}
