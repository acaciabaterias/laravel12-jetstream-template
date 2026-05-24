<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

class PlatformFiscalPublicationRules
{
    public function __construct(
        private readonly PlatformFiscalTaxProfileRules $platformFiscalTaxProfileRules,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $catalogEntries
     * @param  array<int, array<string, mixed>>  $scenarioMappings
     * @param  array<string, mixed>  $coverageSnapshot
     * @return array{passed:bool,messages:array<int,string>}
     */
    public function validate(array $catalogEntries, array $scenarioMappings, array $coverageSnapshot): array
    {
        $messages = [];
        $supportedDirections = (array) config('platform_fiscal_rules.supported_directions', []);
        $catalogCodes = collect($catalogEntries)->pluck('cfop_code')->filter()->values();
        $scenarioKeys = collect($scenarioMappings)->pluck('scenario_key')->filter()->values();

        if ($catalogEntries === []) {
            $messages[] = 'Informe ao menos um CFOP para a publicacao.';
        }

        if ($scenarioMappings === []) {
            $messages[] = 'Informe ao menos um mapeamento de cenario fiscal.';
        }

        if ($catalogCodes->count() !== $catalogCodes->unique()->count()) {
            $messages[] = 'Nao repita CFOPs no catalogo da publicacao.';
        }

        if ($scenarioKeys->count() !== $scenarioKeys->unique()->count()) {
            $messages[] = 'Nao repita cenarios fiscais na mesma publicacao.';
        }

        foreach ($catalogEntries as $catalogEntry) {
            if (! in_array($catalogEntry['operation_direction'] ?? null, $supportedDirections, true)) {
                $messages[] = sprintf('O CFOP %s possui direcao fiscal nao suportada.', $catalogEntry['cfop_code'] ?? 'n/d');
            }
        }

        $catalogCodeLookup = $catalogCodes->all();

        foreach ($scenarioMappings as $scenarioMapping) {
            if (! in_array($scenarioMapping['cfop_code'] ?? null, $catalogCodeLookup, true)) {
                $messages[] = sprintf('O cenario %s referencia um CFOP ausente no catalogo.', $scenarioMapping['scenario_key'] ?? 'n/d');
            }

            if (! in_array($scenarioMapping['operation_direction'] ?? null, $supportedDirections, true)) {
                $messages[] = sprintf('O cenario %s possui direcao fiscal nao suportada.', $scenarioMapping['scenario_key'] ?? 'n/d');
            }

            array_push($messages, ...$this->platformFiscalTaxProfileRules->validate($scenarioMapping));
        }

        foreach ((array) ($coverageSnapshot['invalid_mappings'] ?? []) as $invalidMapping) {
            if (($invalidMapping['issue_type'] ?? null) === 'direction_mismatch') {
                $messages[] = sprintf('O cenario %s possui direcao incompativel com a definicao governada.', $invalidMapping['scenario_key']);
            }
        }

        return [
            'passed' => $messages === [],
            'messages' => $messages,
        ];
    }
}
