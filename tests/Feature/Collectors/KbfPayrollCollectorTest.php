<?php

namespace Tests\Feature\Collectors;

use App\Collectors\Alcobaca\Kbf\KbfPayrollCollector;
use App\Models\PayrollRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KbfPayrollCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_corrections_as_revisions_without_overwriting_history(): void
    {
        Http::fakeSequence()
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=one; Path=/'])
            ->push('<script>x.setTotalRows(1)</script>')
            ->push($this->grid(100000, 10000, 90000))
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=two; Path=/'])
            ->push('<script>x.setTotalRows(1)</script>')
            ->push($this->grid(110000, 10000, 100000))
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=three; Path=/'])
            ->push('<script>x.setTotalRows(1)</script>')
            ->push($this->grid(110000, 10000, 100000));

        $collector = app(KbfPayrollCollector::class);
        $first = $collector->collect(7, 2026);
        $second = $collector->collect(7, 2026);
        $third = $collector->collect(7, 2026);

        $this->assertSame('success', $first->status);
        $this->assertSame(1, $first->created);
        $this->assertSame(1, $second->created);
        $this->assertSame(1, $third->unchanged);
        $this->assertDatabaseCount('raw_source_records', 2);
        $this->assertDatabaseCount('payroll_records', 2);
        $old = PayrollRecord::query()->where('gross_cents', 100000)->sole();
        $latest = PayrollRecord::query()->where('gross_cents', 110000)->sole();
        $this->assertFalse($old->is_latest);
        $this->assertTrue($latest->is_latest);
        $this->assertSame($old->id, $latest->supersedes_id);
        $this->assertSame(100000, $latest->net_cents);
    }

    public function test_inconsistent_money_remains_raw_and_is_not_normalized(): void
    {
        Http::fakeSequence()
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=test; Path=/'])
            ->push('<script>x.setTotalRows(1)</script>')
            ->push($this->grid(100000, 10000, 80000));

        $result = app(KbfPayrollCollector::class)->collect(7, 2026);

        $this->assertSame('partial', $result->status);
        $this->assertSame(1, $result->invalid);
        $this->assertDatabaseCount('raw_source_records', 1);
        $this->assertDatabaseCount('payroll_records', 0);
    }

    private function grid(int $gross, int $deductions, int $net): string
    {
        $gross = number_format($gross / 100, 2, '.', '');
        $deductions = number_format($deductions / 100, 2, '.', '');
        $net = number_format($net / 100, 2, '.', '');

        return '<script>data_819875 = ['.
            "{'field819881':'100','field819882':'PESSOA UM','field820284':'01/02/2025','field820285':'','field819885':'CARGO',".
            "'field820286':'40','field819898':'Julho/2026','field819994':'1 - Pagamento Mensal','field819887':{$gross},".
            "'field819888':{$deductions},'field819889':{$net},'field819891':'CENTRO','field827558':'LOCAL',row:0}".
            '];</script>';
    }
}
