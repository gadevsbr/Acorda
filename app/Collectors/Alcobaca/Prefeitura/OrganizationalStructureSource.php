<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use App\Models\Source;

final class OrganizationalStructureSource
{
    public const KEY = 'alcobaca.prefeitura.organizational-structure';

    public static function resolve(): Source
    {
        return Source::query()->firstOrCreate(
            ['key' => self::KEY],
            [
                'name' => 'Prefeitura de Alcobaça — Estrutura Organizacional',
                'entity' => 'Prefeitura Municipal de Alcobaça',
                'municipality_ibge_code' => '2900801',
                'base_url' => (string) config('collectors.prefeitura.base_url'),
                'official_url' => (string) config('collectors.prefeitura.official_url'),
                'status' => 'not_integrated',
                'enabled' => true,
                'metadata' => [
                    'jurisdiction' => 'municipal',
                    'state' => 'BA',
                    'resource' => 'estrutura-organizacional',
                ],
            ],
        );
    }
}
