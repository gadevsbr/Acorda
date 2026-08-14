<?php

namespace App\Console\Commands;

use App\Collectors\Alcobaca\Prefeitura\PayrollCollector;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CollectPrefeituraPayroll extends Command
{
    protected $signature = 'collect:prefeitura-payroll
        {--year= : Ano de referência com quatro dígitos}
        {--month= : Mês de referência entre 1 e 12}
        {--max-pages= : Máximo de páginas nesta execução}
        {--per-page= : Registros solicitados por página, entre 1 e 500}';

    protected $description = 'Coleta a folha da Prefeitura preservando registros brutos e rastreabilidade';

    public function handle(PayrollCollector $collector): int
    {
        $filters = [];
        $year = $this->option('year');
        $month = $this->option('month');

        if ($year !== null && (! ctype_digit((string) $year) || (int) $year < 2000 || (int) $year > 2100)) {
            $this->error('O ano deve ter quatro dígitos e estar entre 2000 e 2100.');

            return self::INVALID;
        }

        if ($month !== null && (! ctype_digit((string) $month) || (int) $month < 1 || (int) $month > 12)) {
            $this->error('O mês deve estar entre 1 e 12.');

            return self::INVALID;
        }

        if ($year !== null) {
            $filters['ano_referencia'] = (int) $year;
        }
        if ($month !== null) {
            $filters['mes_referencia'] = (int) $month;
        }

        $maxPages = $this->positiveOption('max-pages');
        $perPage = $this->positiveOption('per-page');
        if ($maxPages === false || $perPage === false || (is_int($perPage) && $perPage > 500)) {
            $this->error('max-pages deve ser positivo e per-page deve estar entre 1 e 500.');

            return self::INVALID;
        }

        $lock = Cache::lock('collector:prefeitura-payroll', 3600);

        try {
            $lock->block(1);
            $result = $collector->collect($filters, $maxPages ?: null, $perPage ?: null);

            $this->table(
                ['Execução', 'Status', 'Obtidos', 'Novos', 'Inalterados', 'Inválidos', 'Próxima página'],
                [[
                    $result->runId,
                    $result->status,
                    $result->fetched,
                    $result->created,
                    $result->unchanged,
                    $result->invalid,
                    $result->nextPage,
                ]],
            );

            if ($result->status === 'partial') {
                $this->warn('Fonte parcial: consulte source_health_checks antes de interpretar ausência de registros.');
            }

            return self::SUCCESS;
        } catch (LockTimeoutException) {
            $this->error('Já existe uma coleta da folha em execução.');

            return self::FAILURE;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Coleta falhou: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            optional($lock)->release();
        }
    }

    private function positiveOption(string $name): int|false|null
    {
        $value = $this->option($name);
        if ($value === null) {
            return null;
        }

        return ctype_digit((string) $value) && (int) $value > 0 ? (int) $value : false;
    }
}
