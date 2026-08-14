<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Alcobaca\Prefeitura\PrefeituraApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

class PrefeituraApiClientTest extends TestCase
{
    public function test_it_fetches_and_validates_a_payroll_page(): void
    {
        Http::fake([
            'www.acessoinformacao.com.br/*' => Http::response($this->fixture('payroll-page-1.json'), 200, [
                'Content-Type' => 'application/json',
                'ETag' => '"fixture-etag"',
            ]),
        ]);

        $page = app(PrefeituraApiClient::class)->payrollPage(1, 2, [
            'ano_referencia' => 2026,
            'mes_referencia' => 7,
        ]);

        $this->assertSame(3, $page->total);
        $this->assertSame(2, $page->lastPage);
        $this->assertCount(2, $page->records);
        $this->assertSame('"fixture-etag"', $page->etag);

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Accept', 'application/json')
            && $request['page'] === 1
            && $request['per_page'] === 2
            && $request['ano_referencia'] === 2026
        );
    }

    public function test_it_fails_closed_when_the_envelope_schema_changes(): void
    {
        Http::fake([
            '*' => Http::response(['items' => []], 200, ['Content-Type' => 'application/json']),
        ]);

        $this->expectException(ValidationException::class);

        app(PrefeituraApiClient::class)->payrollPage(1, 50);
    }

    public function test_it_refuses_a_base_url_outside_the_official_allowlist(): void
    {
        config()->set('collectors.prefeitura.base_url', 'https://example.net/private');
        Http::fake();

        $this->expectException(InvalidArgumentException::class);

        try {
            app(PrefeituraApiClient::class)->payrollPage(1, 50);
        } finally {
            Http::assertNothingSent();
        }
    }

    /** @return array<string, mixed> */
    private function fixture(string $name): array
    {
        return json_decode(
            (string) file_get_contents(base_path('tests/Fixtures/prefeitura/'.$name)),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
