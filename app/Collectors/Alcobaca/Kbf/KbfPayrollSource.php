<?php

namespace App\Collectors\Alcobaca\Kbf;

use App\Models\Source;

final class KbfPayrollSource
{
    public const KEY = 'alcobaca.prefeitura.kbf-payroll';

    public static function resolve(): Source
    {
        return Source::query()->firstOrCreate(['key' => self::KEY], [
            'name' => 'Prefeitura de Alcobaça — Remuneração mensal (KBF)',
            'entity' => 'Prefeitura Municipal de Alcobaça',
            'municipality_ibge_code' => '2900801',
            'base_url' => (string) config('collectors.kbf.base_url'),
            'official_url' => (string) config('collectors.kbf.payroll_official_url'),
            'status' => 'not_integrated', 'enabled' => true,
            'metadata' => ['jurisdiction' => 'municipal', 'state' => 'BA', 'resource' => 'monthly-payroll'],
        ]);
    }
}
