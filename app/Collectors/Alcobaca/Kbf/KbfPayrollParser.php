<?php

namespace App\Collectors\Alcobaca\Kbf;

use RuntimeException;

final class KbfPayrollParser
{
    /** @return array<int, array<string, int|string|null>> */
    public function parse(string $html): array
    {
        if (! preg_match('/data_819875\s*=\s*(\[.*?\]);/s', $html, $dataset)) {
            throw new RuntimeException('A grade de remuneração do KBF não foi localizada.');
        }

        preg_match_all('/\{(.*?)\}/s', $dataset[1], $rows);
        $records = [];
        $strings = [
            'field819881' => 'registration', 'field819882' => 'name', 'field820284' => 'admission_date',
            'field820285' => 'termination_date', 'field819885' => 'position', 'field820286' => 'weekly_workload',
            'field819898' => 'reference', 'field819994' => 'calculation_type', 'field819891' => 'cost_center',
            'field827558' => 'workplace',
        ];
        $money = ['field819887' => 'gross_cents', 'field819888' => 'deductions_cents', 'field819889' => 'net_cents'];

        foreach ($rows[1] as $row) {
            $record = [];
            foreach ($strings as $field => $name) {
                if (! preg_match("/'".$field."':'((?:\\\\.|[^'])*)'/s", $row, $value)) {
                    throw new RuntimeException("Campo {$field} ausente na grade de remuneração do KBF.");
                }
                $decoded = str_replace(["\\'", '\\\\'], ["'", '\\'], $value[1]);
                $record[$name] = trim($decoded) !== '' ? trim($decoded) : null;
            }
            foreach ($money as $field => $name) {
                if (! preg_match("/'".$field."':(-?\\d+(?:\\.\\d+)?)/", $row, $value)) {
                    throw new RuntimeException("Campo monetário {$field} ausente na grade de remuneração do KBF.");
                }
                $record[$name] = $this->cents($value[1]);
            }
            $records[] = $record;
        }

        return $records;
    }

    private function cents(string $value): int
    {
        $negative = str_starts_with($value, '-');
        $unsigned = ltrim($value, '-');
        [$whole, $decimal] = array_pad(explode('.', $unsigned, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');

        return $negative ? -$cents : $cents;
    }
}
