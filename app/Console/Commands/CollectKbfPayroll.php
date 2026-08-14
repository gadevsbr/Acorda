<?php

namespace App\Console\Commands;

use App\Collectors\Alcobaca\Kbf\KbfPayrollCollector;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CollectKbfPayroll extends Command
{
    protected $signature = 'collect:kbf-payroll {--month= : Mês de referência, 1 a 12} {--year= : Ano de referência}';

    protected $description = 'Coleta e normaliza uma competência da remuneração oficial KBF';

    public function handle(KbfPayrollCollector $collector): int
    {
        $reference = CarbonImmutable::now()->subMonthNoOverflow();
        $month = $this->option('month') ?? $reference->month;
        $year = $this->option('year') ?? $reference->year;
        $month = filter_var($month, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
        $year = filter_var($year, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]);
        if ($month === false || $year === false) {
            $this->error('Competência inválida.');

            return self::INVALID;
        }

        $lock = Cache::lock("collector:kbf-payroll:{$year}:{$month}", 3600);
        try {
            $lock->block(1);
            $result = $collector->collect($month, $year);
            $this->table(['Execução', 'Status', 'Obtidos', 'Novos', 'Inalterados', 'Inválidos', 'Normalizados'], [[
                $result->runId, $result->status, $result->fetched, $result->created,
                $result->unchanged, $result->invalid, $result->normalized,
            ]]);

            return self::SUCCESS;
        } catch (LockTimeoutException) {
            $this->error('Já existe uma coleta desta competência em execução.');

            return self::FAILURE;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Coleta falhou: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
