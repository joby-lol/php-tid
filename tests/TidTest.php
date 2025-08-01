<?php

/**
 * Tid Time-ordered IDs: https://go.joby.lol/php-tid/
 * MIT License: Copyright (c) 2025 Joby Elliott
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Joby\Tid;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TidTest extends TestCase
{
    public function testConstructorWithoutParameter()
    {
        // Test that a new Tid is created with a valid ID when no parameter is provided
        $tid = new Tid();
        $this->assertIsInt($tid->id);
        $this->assertTrue(TidHelper::validateInt($tid->id));
    }

    public function testConstructorWithParameter()
    {
        // Test that a Tid can be created with a valid ID
        $id = TidHelper::generateInt();
        $tid = new Tid($id);
        $this->assertEquals($id, $tid->id);
    }

    public function testConstructorWithInvalidParameter()
    {
        // Test that an exception is thrown when an invalid ID is provided
        $this->expectException(InvalidArgumentException::class);
        new Tid(-1); // Negative IDs are invalid
    }

    public function testFromString()
    {
        // Test that a Tid can be created from a valid string
        $id = TidHelper::generateInt();
        $string = TidHelper::toString($id);
        $tid = Tid::fromString($string);
        $this->assertEquals($id, $tid->id);
    }

    public function testFromInt()
    {
        // Test that a Tid can be created from a valid integer
        $id = TidHelper::generateInt();
        $tid = Tid::fromInt($id);
        $this->assertEquals($id, $tid->id);
    }

    public function testTime()
    {
        // Test that the time method returns the correct timestamp
        $id = TidHelper::generateInt();
        $tid = new Tid($id);
        $this->assertEquals(TidHelper::earliestTime($id), $tid->time());
    }

    public function testEntropy()
    {
        // Test that the entropy method returns the correct entropy bits
        $id = TidHelper::generateInt();
        $tid = new Tid($id);
        $this->assertEquals(TidHelper::entropyBits($id), $tid->entropy());
    }

    public function testToString()
    {
        // Test that the __toString method returns the correct string representation
        $id = TidHelper::generateInt();
        $tid = new Tid($id);
        $this->assertEquals(TidHelper::toString($id), (string)$tid);
    }

    public function testCompactString()
    {
        // Test that the compactString method returns the string without dashes
        $id = TidHelper::generateInt();
        $tid = new Tid($id);
        $this->assertEquals(str_replace('-', '', TidHelper::toString($id)), $tid->compactString());
    }

    public function testIntegerValue()
    {
        // Test that the id property returns the correct integer
        $id = TidHelper::generateInt();
        $tid = new Tid($id);
        $this->assertEquals($id, $tid->id);
    }

    public function testSerialization()
    {
        // Test that a Tid can be serialized and unserialized
        $id = TidHelper::generateInt();
        $tid = new Tid($id);

        $serialized = serialize($tid);
        $unserialized = unserialize($serialized);

        $this->assertEquals($tid->id, $unserialized->id);
        $this->assertEquals((string)$tid, (string)$unserialized);
    }

    public function testRoundTrip()
    {
        // Test round-trip conversion from Tid to string to Tid
        $tid1 = new Tid();
        $string = (string)$tid1;
        $tid2 = Tid::fromString($string);

        $this->assertEquals($tid1->id, $tid2->id);
    }

    public function testEquality()
    {
        // Test that two Tids with the same ID are equal when converted to strings
        $id = TidHelper::generateInt();
        $tid1 = new Tid($id);
        $tid2 = new Tid($id);

        $this->assertEquals((string)$tid1, (string)$tid2);
    }
}