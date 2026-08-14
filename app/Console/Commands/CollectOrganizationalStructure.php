<?php

namespace App\Console\Commands;

use App\Collectors\Alcobaca\Prefeitura\OrganizationalStructureCollector;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CollectOrganizationalStructure extends Command
{
    protected $signature = 'collect:prefeitura-organizations
        {--max-pages=10 : Máximo de páginas nesta execução}
        {--per-page=100 : Registros solicitados por página, entre 1 e 500}';

    protected $description = 'Coleta e normaliza a estrutura organizacional oficial da Prefeitura';

    public function handle(OrganizationalStructureCollector $collector): int
    {
        $maxPages = filter_var($this->option('max-pages'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $perPage = filter_var($this->option('per-page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
        if ($maxPages === false || $perPage === false) {
            $this->error('max-pages deve ser positivo e per-page deve estar entre 1 e 500.');

            return self::INVALID;
        }

        $lock = Cache::lock('collector:prefeitura-organizations', 3600);

        try {
            $lock->block(1);
            $result = $collector->collect($maxPages, $perPage);
            $this->table(
                ['Execução', 'Status', 'Obtidos', 'Novos', 'Inalterados', 'Inválidos', 'Normalizados'],
                [[
                    $result->runId,
                    $result->status,
                    $result->fetched,
                    $result->created,
                    $result->unchanged,
                    $result->invalid,
                    $result->normalized,
                ]],
            );

            return self::SUCCESS;
        } catch (LockTimeoutException) {
            $this->error('Já existe uma coleta da estrutura organizacional em execução.');

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
