<?php

namespace App\Collectors\Alcobaca\Prefeitura;

final readonly class ProcurementCollectorResult
{
    public function __construct(public int $runId, public string $status, public int $fetched, public int $created, public int $unchanged, public int $invalid) {}
}
