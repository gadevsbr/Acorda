<?php

namespace App\Collectors\Alcobaca\Kbf;

final readonly class KbfPayrollDataset
{
    /** @param array<int, array<string, int|string|null>> $records */
    public function __construct(
        public array $records,
        public int $total,
        public int $month,
        public int $year,
        public string $url,
        public int $httpStatus,
        public ?string $contentType,
        public int $responseTimeMs,
    ) {}
}
