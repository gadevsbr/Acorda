<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Alcobaca\Kbf\KbfActiveEmployeeCollector;
use App\Models\Employment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KbfActiveEmployeeCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_valid_active_employee_rows_and_is_idempotent(): void
    {
        $this->fakeKbf(repetitions: 2);
        $collector = app(KbfActiveEmployeeCollector::class);
        $first = $collector->collect();

        $this->assertSame('success', $first->status);
        $this->assertSame(2, $first->created);
        $this->assertSame(0, $first->invalid);
        $this->assertSame(2, $first->normalized);
        $this->assertDatabaseCount('raw_source_records', 2);
        $this->assertDatabaseCount('people', 2);
        $this->assertDatabaseCount('positions', 2);
        $this->assertDatabaseCount('employments', 2);
        $this->assertDatabaseHas('raw_source_records', ['external_id' => '100', 'validation_status' => 'valid']);
        $this->assertDatabaseHas('people', ['external_id' => '100', 'name' => 'PESSOA UM']);
        $this->assertDatabaseHas('people', ['external_id' => '101', 'name' => 'PESSOA UM']);
        $this->assertDatabaseHas('employments', ['registration' => '100', 'employment_regime' => 'EFETIVO', 'is_current' => true]);
        $this->assertDatabaseHas('sources', ['key' => 'alcobaca.prefeitura.kbf-active-employees', 'status' => 'operational']);

        $second = $collector->collect();
        $this->assertSame(0, $second->created);
        $this->assertSame(2, $second->unchanged);
        $this->assertDatabaseCount('raw_source_records', 2);
        $this->assertDatabaseCount('people', 2);
        $this->assertDatabaseCount('employments', 2);
    }

    public function test_total_mismatch_fails_closed(): void
    {
        $this->fakeKbf(total: 3);
        $this->expectExceptionMessage('informou 3 registros, mas a grade entregou 2');
        app(KbfActiveEmployeeCollector::class)->collect();
    }

    public function test_a_missing_registration_is_only_marked_not_current_after_a_complete_dataset(): void
    {
        $oneRow = <<<'HTML'
        <script>data_818627 = [
        {'field818629':'100','field818630':'PESSOA UM','field818631':'01/02/2025','field818632':'SECRETARIA TESTE','field818633':'EFETIVO','field818634':'CARGO TESTE','field818635':'200',row:0}
        ];</script>
        HTML;
        Http::fakeSequence()
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=first-session; Path=/; Secure'])
            ->push('<script>parent.x.setTotalRows(2);</script>')
            ->push($this->grid())
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=next-session; Path=/; Secure'])
            ->push('<script>parent.x.setTotalRows(1);</script>')
            ->push($oneRow);

        app(KbfActiveEmployeeCollector::class)->collect();
        app(KbfActiveEmployeeCollector::class)->collect();

        $this->assertDatabaseHas('employments', ['registration' => '100', 'is_current' => true, 'ended_observed_at' => null]);
        $this->assertDatabaseHas('employments', ['registration' => '101', 'is_current' => false]);
        $this->assertNotNull(Employment::query()->where('registration', '101')->sole()->ended_observed_at);
    }

    private function fakeKbf(int $total = 2, int $repetitions = 1): void
    {
        $grid = $this->grid();
        $sequence = Http::fakeSequence();
        for ($attempt = 0; $attempt < $repetitions; $attempt++) {
            $sequence
                ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=test-session; Path=/; Secure', 'Content-Type' => 'text/html;charset=ISO-8859-1'])
                ->push("<script>parent.x.setTotalRows({$total});</script>", 200, ['Content-Type' => 'text/html;charset=UTF-8'])
                ->push($grid, 200, ['Content-Type' => 'text/html;charset=UTF-8']);
        }
    }

    private function grid(): string
    {
        return <<<'HTML'
        <script>
        data_818627 = [
        {'field818629':'100','field818630':'PESSOA UM','field818631':'01/02/2025','field818632':'SECRETARIA TESTE','field818633':'EFETIVO','field818634':'CARGO TESTE','field818635':'200',row:0},
        {'field818629':'101','field818630':'PESSOA UM','field818631':'','field818632':'','field818633':'CONTRATADO','field818634':'FUNÇÃO TESTE','field818635':'100',row:1}
        ];
        </script>
        HTML;
    }
}
