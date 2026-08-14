<?php

namespace App\Collectors\Alcobaca\Kbf;

final readonly class KbfActiveEmployeeDataset
{
    /** @param array<int, array<string, string|null>> $records */
    public function __construct(
        public array $records,
        public int $total,
        public string $url,
        public int $httpStatus,
        public ?string $contentType,
        public int $responseTimeMs,
    ) {}
}
