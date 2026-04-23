<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Rules\CepRule;
use App\Rules\CnpjRule;
use App\Rules\CpfRule;
use App\Rules\PhoneRule;
use App\Rules\VehiclePlateRule;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SupportHelpersAndRulesTest extends TestCase
{
    public function test_document_format_helpers_return_expected_masks(): void
    {
        $this->assertSame('12.345.678/0001-99', format_cnpj('12345678000199'));
        $this->assertSame('123.456.789-01', format_cpf('12345678901'));
        $this->assertSame('01310-100', format_cep('01310100'));
        $this->assertSame('(11) 99999-8888', format_phone_br('5511999998888'));
    }

    public function test_business_calculation_helpers_return_expected_values(): void
    {
        $this->assertSame(25.0, calculate_sucata_credit(5, 5));
        $this->assertSame(125.0, calculate_battery_final_price(100, 5, 5, false));
        $this->assertSame(100.0, calculate_battery_final_price(100, 5, 5, true));
        $this->assertSame(12.5, calculate_percentage(5, 40));
    }

    public function test_custom_rules_accept_valid_values(): void
    {
        $validator = Validator::make([
            'cnpj' => '12.345.678/0001-95',
            'cpf' => '529.982.247-25',
            'placa' => 'BRA2E19',
            'telefone' => '(11) 99999-8888',
            'cep' => '01310-100',
        ], [
            'cnpj' => [new CnpjRule],
            'cpf' => [new CpfRule],
            'placa' => [new VehiclePlateRule],
            'telefone' => [new PhoneRule],
            'cep' => [new CepRule],
        ]);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
    }

    public function test_custom_rules_reject_invalid_values(): void
    {
        $validator = Validator::make([
            'cnpj' => '11.111.111/1111-11',
            'cpf' => '111.111.111-11',
            'placa' => '1234ABC',
            'telefone' => '999',
            'cep' => '123',
        ], [
            'cnpj' => [new CnpjRule],
            'cpf' => [new CpfRule],
            'placa' => [new VehiclePlateRule],
            'telefone' => [new PhoneRule],
            'cep' => [new CepRule],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cnpj', $validator->errors()->toArray());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
        $this->assertArrayHasKey('placa', $validator->errors()->toArray());
        $this->assertArrayHasKey('telefone', $validator->errors()->toArray());
        $this->assertArrayHasKey('cep', $validator->errors()->toArray());
    }
}
