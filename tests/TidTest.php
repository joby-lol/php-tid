<?php

/**
 * Tid Time-ordered IDs: https://github.com/joby-lol/php-tid
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Tid;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TidTest extends TestCase
{
    public function test_version_0()
    {
        $tid = Tid::generate(Tid::VERSION_0);
        $this->assertEquals(Tid::VERSION_0, $tid->version());
        $this->assertEquals($tid, Tid::fromString((string)$tid));
        $this->assertEquals($tid, Tid::fromInt($tid->id));
        $this->assertEquals(0, $tid->time());
        $this->assertEquals(59, $tid->entropyBits());
        $this->assertEquals($tid->id >> 4 & ((1 << 59) - 1), $tid->random());
    }

    public function test_version_1()
    {
        $tid = Tid::generate(Tid::VERSION_1);
        $this->assertEquals(Tid::VERSION_1, $tid->version());
        $this->assertEquals($tid, Tid::fromString((string)$tid));
        $this->assertEquals($tid, Tid::fromInt($tid->id));
        $this->assertGreaterThanOrEqual(time(), $tid->time());
        $this->assertEquals(14, $tid->entropyBits());
        $this->assertEquals($tid->id >> 4 & ((1 << 14) - 1), $tid->random());
    }

    public function test_version_2()
    {
        $tid = Tid::generate(Tid::VERSION_2);
        $this->assertEquals(Tid::VERSION_2, $tid->version());
        $this->assertEquals($tid, Tid::fromString((string)$tid));
        $this->assertEquals($tid, Tid::fromInt($tid->id));
        $this->assertGreaterThanOrEqual(intdiv(time(), 256), $tid->time());
        $this->assertEquals(22, $tid->entropyBits());
        $this->assertEquals($tid->id >> 4 & ((1 << 22) - 1), $tid->random());
    }

    public function test_version_3()
    {
        $tid = Tid::generate(Tid::VERSION_3);
        $this->assertEquals(Tid::VERSION_3, $tid->version());
        $this->assertEquals($tid, Tid::fromString((string)$tid));
        $this->assertEquals($tid, Tid::fromInt($tid->id));
        $this->assertGreaterThanOrEqual(intdiv(time(), 65536), $tid->time());
        $this->assertEquals(30, $tid->entropyBits());
        $this->assertEquals($tid->id >> 4 & ((1 << 30) - 1), $tid->random());
    }

    public function test_version_4()
    {
        $tid = Tid::generate(Tid::VERSION_4);
        $this->assertEquals(Tid::VERSION_4, $tid->version());
        $this->assertEquals($tid, Tid::fromString((string)$tid));
        $this->assertEquals($tid, Tid::fromInt($tid->id));
        $this->assertGreaterThanOrEqual(intdiv(time(), 262144), $tid->time());
        $this->assertEquals(32, $tid->entropyBits());
        $this->assertEquals($tid->id >> 4 & ((1 << 32) - 1), $tid->random());
    }

    public function test_hmac_generation()
    {
        $tid = Tid::hmacGenerate('foo', 'bar');
        $this->assertEquals(Tid::VERSION_0, $tid->version());
        $this->assertEquals(4980502661450870528, $tid->id);
        $this->assertEquals('11u80ugb7uj28', (string)$tid);
    }

    public function test_invalid_version_generation()
    {
        $this->expectException(InvalidArgumentException::class);
        Tid::generate(99);
    }

    public function test_invalid_version_from_constructor()
    {
        // NOTE: if versions all the way through 15 are supported in the future, this test will need to be updated/removed
        $this->expectException(InvalidArgumentException::class);
        new Tid(15);
    }

    public function test_constructor_with_negative_integer()
    {
        $this->expectException(InvalidArgumentException::class);
        new Tid(-1);
    }

    public function test_json_serialization()
    {
        $tid = Tid::generate(Tid::VERSION_1);
        $this->assertEquals('"' . (string)$tid . '"', json_encode($tid));
    }
}
