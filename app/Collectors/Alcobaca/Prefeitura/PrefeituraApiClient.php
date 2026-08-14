<?php

namespace App\Collectors\Alcobaca\Prefeitura;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

final class PrefeituraApiClient
{
    public function __construct(
        private readonly PayrollEnvelopeValidator $validator,
        private readonly OrganizationalStructureValidator $organizationalStructureValidator,
    ) {}

    /**
     * @param  array<string, int|string>  $filters
     *
     * @throws ConnectionException
     */
    public function payrollPage(int $page, int $perPage, array $filters = []): PayrollPage
    {
        if ($page < 1 || $perPage < 1 || $perPage > 500) {
            throw new InvalidArgumentException('Página deve ser >= 1 e per_page deve estar entre 1 e 500.');
        }

        $url = rtrim((string) config('collectors.prefeitura.base_url'), '/').'/folha-pagamento';
        $this->assertAllowedUrl($url);

        $query = array_merge($filters, ['page' => $page, 'per_page' => $perPage]);
        $startedAt = hrtime(true);
        $response = $this->request()->get($url, $query);
        $responseTimeMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);

        if ($response->redirect()) {
            throw new RuntimeException('Redirecionamento recusado pelo coletor para preservar a allowlist.');
        }

        $response->throw();

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('A API da folha não retornou um objeto JSON.');
        }

        $this->validator->validate($payload);

        /** @var array<int, array<string, mixed>> $records */
        $records = $payload['data'];

        return new PayrollPage(
            records: $records,
            envelope: $payload,
            total: (int) $payload['total'],
            perPage: (int) $payload['per_page'],
            currentPage: (int) $payload['current_page'],
            lastPage: (int) $payload['last_page'],
            url: $url.'?'.http_build_query($query),
            httpStatus: $response->status(),
            contentType: $response->header('Content-Type'),
            etag: $response->header('ETag'),
            lastModified: $response->header('Last-Modified'),
            responseTimeMs: $responseTimeMs,
        );
    }

    /** @throws ConnectionException */
    public function organizationalStructurePage(int $page, int $perPage): OrganizationalStructurePage
    {
        if ($page < 1 || $perPage < 1 || $perPage > 500) {
            throw new InvalidArgumentException('Página deve ser >= 1 e per_page deve estar entre 1 e 500.');
        }

        $url = rtrim((string) config('collectors.prefeitura.base_url'), '/').'/estrutura-organizacional';
        $this->assertAllowedUrl($url);
        $query = ['page' => $page, 'per_page' => $perPage];
        $startedAt = hrtime(true);
        $response = $this->request()->get($url, $query);
        $responseTimeMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);

        if ($response->redirect()) {
            throw new RuntimeException('Redirecionamento recusado pelo coletor para preservar a allowlist.');
        }

        $response->throw();
        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('A API da estrutura organizacional não retornou um objeto JSON.');
        }

        $this->organizationalStructureValidator->validateEnvelope($payload);

        /** @var array<int, array<string, mixed>> $records */
        $records = $payload['data'];

        return new OrganizationalStructurePage(
            records: $records,
            envelope: $payload,
            total: (int) $payload['total'],
            currentPage: (int) $payload['current_page'],
            lastPage: (int) $payload['last_page'],
            url: $url.'?'.http_build_query($query),
            httpStatus: $response->status(),
            contentType: $response->header('Content-Type'),
            etag: $response->header('ETag'),
            lastModified: $response->header('Last-Modified'),
            responseTimeMs: $responseTimeMs,
        );
    }

    private function request(): PendingRequest
    {
        $caBundle = config('collectors.ca_bundle');

        return Http::acceptJson()
            ->withUserAgent((string) config('collectors.user_agent'))
            ->connectTimeout((int) config('collectors.connect_timeout_seconds'))
            ->timeout((int) config('collectors.timeout_seconds'))
            ->retry(
                (int) config('collectors.max_attempts'),
                (int) config('collectors.retry_delay_ms'),
                throw: false,
            )
            ->withOptions([
                'allow_redirects' => false,
                'verify' => is_string($caBundle) && $caBundle !== '' ? $caBundle : true,
            ]);
    }

    private function assertAllowedUrl(string $url): void
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowedHosts = array_map('strtolower', (array) config('collectors.prefeitura.allowed_hosts'));

        if ($scheme !== 'https' || ! in_array($host, $allowedHosts, true)) {
            throw new InvalidArgumentException('URL do coletor fora da allowlist oficial.');
        }
    }
}
