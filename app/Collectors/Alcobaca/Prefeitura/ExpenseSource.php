<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use App\Models\Source;

final class ExpenseSource
{
    public const KEY = 'alcobaca.prefeitura.expenses';

    public static function resolve(): Source
    {
        return Source::query()->firstOrCreate(['key' => self::KEY], [
            'name' => 'Prefeitura de Alcobaça — Despesas', 'entity' => 'Prefeitura Municipal de Alcobaça',
            'municipality_ibge_code' => '2900801', 'base_url' => config('collectors.prefeitura.base_url'),
            'official_url' => config('collectors.prefeitura.official_url'), 'status' => 'not_integrated', 'enabled' => true,
            'metadata' => ['jurisdiction' => 'municipal', 'state' => 'BA', 'resource' => 'despesas'],
        ]);
    }
}
