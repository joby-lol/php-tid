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
        $uid = UID::generate(UID::VERSION_0);
        $this->assertEquals(UID::VERSION_0, $uid->version());
        $this->assertEquals($uid, UID::fromString((string)$uid));
        $this->assertEquals($uid, UID::fromInt($uid->value));
        $this->assertEquals(0, $uid->time());
        $this->assertEquals(59, $uid->entropyBits());
        $this->assertEquals($uid->value >> 4 & ((1 << 59) - 1), $uid->random());
    }

    public function test_version_1_0()
    {
        $uid = UID::generate(UID::VERSION_1_0);
        $this->assertEquals(UID::VERSION_1_0, $uid->version());
        $this->assertEquals($uid, UID::fromString((string)$uid));
        $this->assertEquals($uid, UID::fromInt($uid->value));
        $this->assertGreaterThanOrEqual(time(), $uid->time());
        $this->assertEquals(14, $uid->entropyBits());
        $this->assertEquals($uid->value >> 4 & ((1 << 14) - 1), $uid->random());
    }

    public function test_version_1_1()
    {
        $uid = UID::generate(UID::VERSION_1_1);
        $this->assertEquals(UID::VERSION_1_1, $uid->version());
        $this->assertEquals($uid, UID::fromString((string)$uid));
        $this->assertEquals($uid, UID::fromInt($uid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 256), $uid->time());
        $this->assertEquals(22, $uid->entropyBits());
        $this->assertEquals($uid->value >> 4 & ((1 << 22) - 1), $uid->random());
    }

    public function test_version_1_2()
    {
        $uid = UID::generate(UID::VERSION_1_2);
        $this->assertEquals(UID::VERSION_1_2, $uid->version());
        $this->assertEquals($uid, UID::fromString((string)$uid));
        $this->assertEquals($uid, UID::fromInt($uid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 65536), $uid->time());
        $this->assertEquals(30, $uid->entropyBits());
        $this->assertEquals($uid->value >> 4 & ((1 << 30) - 1), $uid->random());
    }

    public function test_version_1_3()
    {
        $uid = UID::generate(UID::VERSION_1_3);
        $this->assertEquals(UID::VERSION_1_3, $uid->version());
        $this->assertEquals($uid, UID::fromString((string)$uid));
        $this->assertEquals($uid, UID::fromInt($uid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 262144), $uid->time());
        $this->assertEquals(32, $uid->entropyBits());
        $this->assertEquals($uid->value >> 4 & ((1 << 32) - 1), $uid->random());
    }

    public function test_version_1_4()
    {
        $uid = UID::generate(UID::VERSION_1_4);
        $this->assertEquals(UID::VERSION_1_4, $uid->version());
        $this->assertEquals($uid, UID::fromString((string)$uid));
        $this->assertEquals($uid, UID::fromInt($uid->value));
        $this->assertGreaterThanOrEqual(intdiv(time(), 1048576), $uid->time());
        $this->assertEquals(34, $uid->entropyBits());
        $this->assertEquals($uid->value >> 4 & ((1 << 34) - 1), $uid->random());
    }

    public function test_hmac_generation()
    {
        $uid = UID::hmacGenerate('foo', 'bar');
        $this->assertEquals(UID::VERSION_0, $uid->version());
        $this->assertEquals(4980502661450870528, $uid->value);
        $this->assertEquals('11u80ugb7uj28', (string)$uid);
    }

    public function test_hash_generation()
    {
        $uid = UID::hashGenerate('foo');
        $this->assertEquals(UID::VERSION_0, $uid->version());
        $this->assertEquals(5407043158477369760, $uid->value);
        $this->assertEquals('152vwtziw5eow', (string) $uid);
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
        $uid = UID::generate(UID::VERSION_1_0);
        $this->assertEquals('"' . (string)$uid . '"', json_encode($uid));
    }
}
