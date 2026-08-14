<?php

namespace App\Console\Commands;

use App\Collectors\Alcobaca\Prefeitura\ExpenseCollector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CollectExpenses extends Command
{
    protected $signature = 'collect:prefeitura-expenses {--max-pages=20} {--per-page=100}';

    protected $description = 'Preserva despesas oficiais da Prefeitura com validação e histórico bruto';

    public function handle(ExpenseCollector $collector): int
    {
        $max = filter_var($this->option('max-pages'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $perPage = filter_var($this->option('per-page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
        if ($max === false || $perPage === false) {
            $this->error('Opções de paginação inválidas.');

            return self::INVALID;
        }
        $lock = Cache::lock('collector:prefeitura-expenses', 3600);
        try {
            if (! $lock->get()) {
                $this->error('Já existe uma coleta de despesas em execução.');

                return self::FAILURE;
            }
            $result = $collector->collect($max, $perPage);
            $this->table(['Execução', 'Status', 'Obtidos', 'Novos', 'Inalterados', 'Inválidos'], [[$result->runId, $result->status, $result->fetched, $result->created, $result->unchanged, $result->invalid]]);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Coleta falhou: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            optional($lock)->release();
        }
    }
}
