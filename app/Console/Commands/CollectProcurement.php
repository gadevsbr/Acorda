<?php

namespace App\Console\Commands;

use App\Collectors\Alcobaca\Prefeitura\ProcurementCollector;
use Illuminate\Console\Command;

class CollectProcurement extends Command
{
    protected $signature = 'collect:prefeitura-procurement {resource : contratos, licitacoes, fornecedores ou fiscais-contrato} {--max-pages=20} {--per-page=100}';

    protected $description = 'Preserva datasets oficiais de compras e contratações';

    public function handle(ProcurementCollector $collector): int
    {
        try {
            $result = $collector->collect((string) $this->argument('resource'), (int) $this->option('max-pages'), (int) $this->option('per-page'));
            $this->table(['Execução', 'Status', 'Obtidos', 'Novos', 'Inalterados', 'Inválidos'], [[$result->runId, $result->status, $result->fetched, $result->created, $result->unchanged, $result->invalid]]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
