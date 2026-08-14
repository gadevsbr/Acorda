<?php

namespace Tests\Unit;

use App\Collectors\Alcobaca\Kbf\KbfPayrollParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class KbfPayrollParserTest extends TestCase
{
    public function test_it_converts_decimal_money_to_integer_cents(): void
    {
        $html = "data_819875 = [{'field819881':'1','field819882':'NOME','field820284':'','field820285':'',".
            "'field819885':'CARGO','field820286':'40','field819898':'Julho/2026','field819994':'Mensal',".
            "'field819887':2026.25,'field819888':158.04,'field819889':1868.21,'field819891':'CENTRO','field827558':'LOCAL',row:0}];";

        $record = (new KbfPayrollParser)->parse($html)[0];

        $this->assertSame(202625, $record['gross_cents']);
        $this->assertSame(15804, $record['deductions_cents']);
        $this->assertSame(186821, $record['net_cents']);
    }

    public function test_it_fails_closed_without_the_expected_grid(): void
    {
        $this->expectException(RuntimeException::class);
        (new KbfPayrollParser)->parse('<html>mudou</html>');
    }
}
