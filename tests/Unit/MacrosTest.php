<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;

class MacrosTest extends TestCase
{
    public function test_str_macros_format_documents_and_plate(): void
    {
        $this->assertSame('12.345.678/0001-99', Str::cnpj('12345678000199'));
        $this->assertSame('123.456.789-01', Str::cpf('12345678901'));
        $this->assertSame('(11) 99999-8888', Str::telefone('5511999998888'));
        $this->assertSame('01310-100', Str::cep('01310100'));
        $this->assertSame('BRA-2E19', Str::placa('bra2e19'));
    }

    public function test_collection_to_money_sums_valor_field_and_formats_brl(): void
    {
        $collection = collect([
            ['valor' => 10.5],
            ['valor' => 20],
            ['valor' => 5.25],
        ]);

        $this->assertSame('R$ 35,75', $collection->toMoney());
    }

    public function test_collection_group_by_day_groups_by_date_field(): void
    {
        $collection = new Collection([
            ['data_transacao' => '2026-04-23 09:00:00', 'valor' => 10],
            ['data_transacao' => '2026-04-23 11:00:00', 'valor' => 20],
            ['data_transacao' => '2026-04-24 08:00:00', 'valor' => 30],
        ]);

        $grouped = $collection->groupByDay('data_transacao');

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped->get('2026-04-23'));
        $this->assertCount(1, $grouped->get('2026-04-24'));
    }

    public function test_request_macros_detect_mobile_and_ajax_requests(): void
    {
        $mobileRequest = Request::create('/dashboard', 'GET', server: [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
        ]);

        $ajaxRequest = Request::create('/api/test', 'GET', server: [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertTrue($mobileRequest->isMobile());
        $this->assertFalse($mobileRequest->isAjax());
        $this->assertFalse($ajaxRequest->isMobile());
        $this->assertTrue($ajaxRequest->isAjax());
    }
}
