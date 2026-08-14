<?php

namespace Tests\Unit;

use App\Collectors\Alcobaca\Kbf\KbfActiveEmployeeParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class KbfActiveEmployeeParserTest extends TestCase
{
    public function test_it_refuses_a_response_without_the_expected_grid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('grade de servidores');
        (new KbfActiveEmployeeParser)->parse('<html>schema alterado</html>');
    }
}
