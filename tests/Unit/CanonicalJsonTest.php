<?php

namespace Tests\Unit;

use App\Collectors\Support\CanonicalJson;
use PHPUnit\Framework\TestCase;

class CanonicalJsonTest extends TestCase
{
    public function test_object_key_order_does_not_change_the_canonical_checksum_input(): void
    {
        $first = ['name' => 'Teste', 'amount' => 10.0, 'nested' => ['z' => 2, 'a' => 1]];
        $second = ['nested' => ['a' => 1, 'z' => 2], 'amount' => 10.0, 'name' => 'Teste'];

        $this->assertSame(CanonicalJson::encode($first), CanonicalJson::encode($second));
    }

    public function test_list_order_remains_significant(): void
    {
        $this->assertNotSame(
            CanonicalJson::encode(['items' => [1, 2]]),
            CanonicalJson::encode(['items' => [2, 1]]),
        );
    }
}
