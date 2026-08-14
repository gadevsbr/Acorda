<?php

namespace App\Collectors\Alcobaca\Kbf;

use RuntimeException;

final class KbfActiveEmployeeParser
{
    /** @return array<int, array<string, string|null>> */
    public function parse(string $html): array
    {
        if (! preg_match('/data_818627\s*=\s*(\[.*?\]);/s', $html, $dataset)) {
            throw new RuntimeException('A grade de servidores do KBF não foi localizada.');
        }

        preg_match_all('/\{(.*?)\}/s', $dataset[1], $rows);
        $records = [];
        $mapping = [
            'field818629' => 'registration',
            'field818630' => 'name',
            'field818631' => 'admission_date',
            'field818632' => 'cost_center',
            'field818633' => 'employment_regime',
            'field818634' => 'position',
            'field818635' => 'monthly_workload',
        ];

        foreach ($rows[1] as $row) {
            $record = [];
            foreach ($mapping as $field => $name) {
                if (! preg_match("/'".$field."':'((?:\\\\.|[^'])*)'/s", $row, $value)) {
                    throw new RuntimeException("Campo {$field} ausente na grade de servidores do KBF.");
                }
                $decoded = str_replace(["\\'", '\\\\'], ["'", '\\'], $value[1]);
                $record[$name] = trim($decoded) !== '' ? trim($decoded) : null;
            }
            $records[] = $record;
        }

        return $records;
    }
}
