<?php

namespace Tests\Feature;

use App\Collectors\Alcobaca\Kbf\KbfActiveEmployeeCollector;
use App\Collectors\Alcobaca\Kbf\KbfPayrollCollector;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PeoplePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_keeps_people_with_the_same_name_separate_by_registration(): void
    {
        $this->collectPeople();
        $this->get(route('people.index', ['q' => 'pessoa um']))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('People/Index')->has('results', 2)
            ->where('results.0.registration', '100')->where('results.1.registration', '101')
            ->where('results.0.name', 'PESSOA UM')->where('results.1.name', 'PESSOA UM'));
    }

    public function test_profile_exposes_employment_payroll_revisions_and_provenance(): void
    {
        Http::fakeSequence()->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=people; Path=/'])
            ->push('<script>parent.x.setTotalRows(2);</script>')->push($this->peopleGrid())
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=one; Path=/'])
            ->push('<script>x.setTotalRows(1)</script>')->push($this->payrollGrid('1000.00', '100.00', '900.00'))
            ->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=two; Path=/'])
            ->push('<script>x.setTotalRows(1)</script>')->push($this->payrollGrid('1100.00', '100.00', '1000.00'));
        app(KbfActiveEmployeeCollector::class)->collect();
        app(KbfPayrollCollector::class)->collect(7, 2026);
        app(KbfPayrollCollector::class)->collect(7, 2026);
        $person = Person::query()->where('external_id', '100')->sole();

        $this->get(route('people.show', $person))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('People/Show')->where('person.registration', '100')
            ->where('person.employments.0.position', 'CARGO TESTE')->has('person.payroll', 2)
            ->where('person.payroll.0.isLatest', true)->where('person.payroll.0.netCents', 100000)
            ->where('person.payroll.1.isLatest', false)->where('person.payroll.1.netCents', 90000)
            ->where('person.payroll.0.provenance.validationStatus', 'valid'));
    }

    public function test_short_or_missing_search_does_not_publish_a_people_directory(): void
    {
        $this->collectPeople();
        $this->get(route('people.index'))->assertInertia(fn (Assert $page) => $page->has('results', 0));
        $this->get(route('people.index', ['q' => 'p']))->assertInertia(fn (Assert $page) => $page->has('results', 0));
    }

    private function collectPeople(): void
    {
        Http::fakeSequence()->push('<html>form</html>', 200, ['Set-Cookie' => 'JSESSIONID=people; Path=/'])
            ->push('<script>parent.x.setTotalRows(2);</script>')->push($this->peopleGrid());
        app(KbfActiveEmployeeCollector::class)->collect();
    }

    private function peopleGrid(): string
    {
        return <<<'HTML'
            <script>data_818627 = [
            {'field818629':'100','field818630':'PESSOA UM','field818631':'01/02/2025','field818632':'SECRETARIA TESTE','field818633':'EFETIVO','field818634':'CARGO TESTE','field818635':'200',row:0},
            {'field818629':'101','field818630':'PESSOA UM','field818631':'','field818632':'','field818633':'CONTRATADO','field818634':'FUNÇÃO TESTE','field818635':'100',row:1}
            ];</script>
            HTML;
    }

    private function payrollGrid(string $gross, string $deductions, string $net): string
    {
        return '<script>data_819875 = ['.
            "{'field819881':'100','field819882':'PESSOA UM','field820284':'01/02/2025','field820285':'','field819885':'CARGO TESTE',".
            "'field820286':'40','field819898':'Julho/2026','field819994':'1 - Pagamento Mensal','field819887':{$gross},".
            "'field819888':{$deductions},'field819889':{$net},'field819891':'CENTRO','field827558':'LOCAL',row:0}".
            '];</script>';
    }
}
