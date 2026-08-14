<?php

namespace App\Console\Commands;

use App\Services\NormalizeProcurementData;
use Illuminate\Console\Command;

class NormalizeProcurement extends Command
{
    protected $signature = 'procurement:normalize';

    protected $description = 'Normaliza compras públicas já preservadas';

    public function handle(NormalizeProcurementData $normalizer): int
    {
        $counts = $normalizer->handle();
        $this->table(array_keys($counts), [array_values($counts)]);

        return self::SUCCESS;
    }
}
