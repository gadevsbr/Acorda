<?php

namespace App\Collectors\Alcobaca\Prefeitura;

final readonly class PayrollPage
{
    /**
     * @param  array<int, array<string, mixed>>  $records
     * @param  array<string, mixed>  $envelope
     */
    public function __construct(
        public array $records,
        public array $envelope,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
        public string $url,
        public int $httpStatus,
        public ?string $contentType,
        public ?string $etag,
        public ?string $lastModified,
        public int $responseTimeMs,
    ) {}
}
