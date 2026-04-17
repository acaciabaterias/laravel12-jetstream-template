<?php

namespace App\Services;

use Exception;

class NFeParserService
{
    /**
     * Parse NFe (SEFAZ Brazil) XML format into standardized inventory ingestion arrays.
     */
    public function parse(string $xmlContent): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            throw new Exception("Falha ao decodificar a estrutura do XML.");
        }

        // Handle both raw NFe and wrapped nfeProc formats
        $nfe = isset($xml->NFe) ? $xml->NFe : $xml;
        
        if (!isset($nfe->infNFe)) {
            throw new Exception("O nó infNFe não foi encontrado. Certifique-se de que é uma NF-e válida.");
        }

        $infNFe = $nfe->infNFe;
        $chave = str_replace('NFe', '', (string) $infNFe->attributes()->Id);

        $emitente = [
            'cnpj' => (string) $infNFe->emit->CNPJ,
            'nome' => (string) $infNFe->emit->xNome,
        ];

        $itens = [];
        if (isset($infNFe->det)) {
            foreach ($infNFe->det as $det) {
                $prod = $det->prod;
                $codigo = (string) $prod->cProd;
                $ean = (string) $prod->cEAN;
                
                $itens[] = [
                    'codigo_fornecedor' => $codigo,
                    'ean' => ($ean !== 'SEM GTIN' && $ean !== '') ? $ean : null,
                    'nome' => (string) $prod->xProd,
                    'ncm' => (string) $prod->NCM,
                    'quantidade' => (float) $prod->qCom, // Usually integer for batteries, but NFe defines as float
                    'valor_unitario' => (float) $prod->vUnCom,
                ];
            }
        }

        return [
            'chave' => $chave,
            'fornecedor' => $emitente,
            'itens' => $itens,
        ];
    }
}
