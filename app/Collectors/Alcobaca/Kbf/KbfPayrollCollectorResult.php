<?php

namespace App\Collectors\Alcobaca\Kbf;

final readonly class KbfPayrollCollectorResult
{
    public function __construct(
        public int $runId, public string $status, public int $fetched, public int $created,
        public int $unchanged, public int $invalid, public int $normalized,
    ) {}
}
