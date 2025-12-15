<?php

/**
 * smolUID: https://github.com/joby-lol/smol-uid
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Smol\UID;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UIDTest extends TestCase
{
    public function test_version_0()
    {
        $tid = UID::generate(UID::VERSION_0);
        $this->assertEquals(UID::VERSION_0, $tid->version());
        $this->assertEquals($tid, UID::fromString((string)$tid));
        $this->assertEquals($tid, UID::fromInt($tid->value));
        $this->assertEquals(0, $tid->time());
        $this->assertEquals(59, $tid->entropyBits());
        $this->assertEquals($tid->value >> 4 & ((1 << 59) - 1), $tid->random());
    }

    public function test_version_1_0()
    {
        $tid = UID::generate(UID::VERSION_1_0);
        $this->assertEquals(UID::VERSION_1_0, $tid->version());
        $this->assertEquals($tid, UID::fromString((string)$tid));
        $this->assertEquals($tid, UID::fromInt($tid->value));
        $this->assertGreaterThanOrEqual(time(), $tid->time());
        $this->assertEquals(14, $tid->entropyBits());
        $this->assertEquals($tid->value >> 4 & ((1 << 14) - 1), $tid->random());
    }

    public function test_version_1_1()
    {
        $tid = UID::generate(UID::VERSION_1_1);
        $this->assertEquals(UID::VERSION_1_1, $tid->version());
        $this->assertEquals($tid, UID::fromString((string)$tid));
        $this->assertEquals($tid, UID::fromInt($tid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 256), $tid->time());
        $this->assertEquals(22, $tid->entropyBits());
        $this->assertEquals($tid->value >> 4 & ((1 << 22) - 1), $tid->random());
    }

    public function test_version_1_2()
    {
        $tid = UID::generate(UID::VERSION_1_2);
        $this->assertEquals(UID::VERSION_1_2, $tid->version());
        $this->assertEquals($tid, UID::fromString((string)$tid));
        $this->assertEquals($tid, UID::fromInt($tid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 65536), $tid->time());
        $this->assertEquals(30, $tid->entropyBits());
        $this->assertEquals($tid->value >> 4 & ((1 << 30) - 1), $tid->random());
    }

    public function test_version_1_3()
    {
        $tid = UID::generate(UID::VERSION_1_3);
        $this->assertEquals(UID::VERSION_1_3, $tid->version());
        $this->assertEquals($tid, UID::fromString((string)$tid));
        $this->assertEquals($tid, UID::fromInt($tid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 262144), $tid->time());
        $this->assertEquals(32, $tid->entropyBits());
        $this->assertEquals($tid->value >> 4 & ((1 << 32) - 1), $tid->random());
    }

    public function test_version_1_4()
    {
        $tid = UID::generate(UID::VERSION_1_4);
        $this->assertEquals(UID::VERSION_1_4, $tid->version());
        $this->assertEquals($tid, UID::fromString((string)$tid));
        $this->assertEquals($tid, UID::fromInt($tid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 1048576), $tid->time());
        $this->assertEquals(34, $tid->entropyBits());
        $this->assertEquals($tid->value >> 4 & ((1 << 34) - 1), $tid->random());
    }

    public function test_hmac_generation()
    {
        $tid = UID::hmacGenerate('foo', 'bar');
        $this->assertEquals(UID::VERSION_0, $tid->version());
        $this->assertEquals(4980502661450870528, $tid->value);
        $this->assertEquals('11u80ugb7uj28', (string)$tid);
    }

    public function test_invalid_version_generation()
    {
        $this->expectException(InvalidArgumentException::class);
        UID::generate(99);
    }

    public function test_invalid_version_from_constructor()
    {
        // NOTE: if versions all the way through 15 are supported in the future, this test will need to be updated/removed
        $this->expectException(InvalidArgumentException::class);
        new UID(15);
    }

    public function test_constructor_with_negative_integer()
    {
        $this->expectException(InvalidArgumentException::class);
        new UID(-1);
    }

    public function test_json_serialization()
    {
        $tid = UID::generate(UID::VERSION_1_0);
        $this->assertEquals('"' . (string)$tid . '"', json_encode($tid));
    }
}
