<?php

namespace App\Console\Commands;

use App\Collectors\Alcobaca\Kbf\KbfActiveEmployeeCollector;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CollectKbfActiveEmployees extends Command
{
    protected $signature = 'collect:kbf-active-employees';

    protected $description = 'Preserva a grade oficial de servidores ativos publicada no KBF';

    public function handle(KbfActiveEmployeeCollector $collector): int
    {
        $lock = Cache::lock('collector:kbf-active-employees', 3600);
        try {
            $lock->block(1);
            $result = $collector->collect();
            $this->table(['Execução', 'Status', 'Obtidos', 'Novos', 'Inalterados', 'Inválidos', 'Normalizados'], [[
                $result->runId, $result->status, $result->fetched, $result->created, $result->unchanged, $result->invalid, $result->normalized,
            ]]);

            return self::SUCCESS;
        } catch (LockTimeoutException) {
            $this->error('Já existe uma coleta KBF em execução.');

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
