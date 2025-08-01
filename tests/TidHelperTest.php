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

class TidHelperTest extends TestCase
{
    public function testConsistency()
    {
        // toString and toInt should be inverses of each other
        $int = TidHelper::generateInt();
        $this->assertEquals(
            $int,
            TidHelper::toInt(TidHelper::toString($int))
        );
    }

    public function testFormatString()
    {
        /**
         * String should be formatted with dashes every 4 characters, unless the
         * last chunk is less than 3 characters, in which case it should combine
         * it with its predecessor.
         */
        $this->assertEquals(
            'A',
            TidHelper::formatString('A')
        );
        $this->assertEquals(
            'AA',
            TidHelper::formatString('AA')
        );
        $this->assertEquals(
            'AAA',
            TidHelper::formatString('AAA')
        );
        $this->assertEquals(
            'AAAA',
            TidHelper::formatString('AAAA')
        );
        $this->assertEquals(
            'ABCDE',
            TidHelper::formatString('ABCDE')
        );
        $this->assertEquals(
            'ABCDEF',
            TidHelper::formatString('ABCDEF')
        );
        $this->assertEquals(
            'ABCD-EFG',
            TidHelper::formatString('ABCDEFG')
        );
        $this->assertEquals(
            'ABCD-EFGH',
            TidHelper::formatString('ABCDEFGH')
        );
        $this->assertEquals(
            'ABCD-EFGHI',
            TidHelper::formatString('ABCDEFGHI')
        );
        $this->assertEquals(
            'AAAA-AAAA-AAA',
            TidHelper::formatString('AAAAAAAAAAA')
        );
        $this->assertEquals(
            'AAAA-AAAA-AAAA',
            TidHelper::formatString('AAAAAAAAAAAA')
        );
        // it should also ignore invalid characters in the input, including misplaced dashes
        $this->assertEquals(
            'AAAA-AAAA-AAAA',
            TidHelper::formatString('A-_A!@A%^AAAAAAAAA')
        );
    }

    public function testGenerateInt()
    {
        // Test that generated IDs are different
        $id1 = TidHelper::generateInt();
        $id2 = TidHelper::generateInt();
        $this->assertNotEquals($id1, $id2);

        // Test that generated IDs are valid
        $this->assertTrue(TidHelper::validateInt($id1));
        $this->assertTrue(TidHelper::validateInt($id2));

        // Test that the timestamp part is close to current time, and that current time is within 2^DROPPED_BITS-1 of the earliest time
        $earliestTime = TidHelper::earliestTime($id1);
        $currentTime = time();
        $this->assertLessThanOrEqual($currentTime, $earliestTime);
        $this->assertGreaterThan($currentTime - (2 ** TidHelper::DROPPED_BITS - 1), $earliestTime);
    }

    public function testEarliestTime()
    {
        // Test with a known value
        $time = time();
        $time = $time >> TidHelper::DROPPED_BITS;
        $time = $time << TidHelper::ENTROPY_BITS;
        $entropy = 12345; // Some arbitrary entropy value
        $id = $time | $entropy;

        $expectedEarliestTime = ($time >> TidHelper::ENTROPY_BITS) << TidHelper::DROPPED_BITS;
        $this->assertEquals($expectedEarliestTime, TidHelper::earliestTime($id));

        // Test with a Tid object
        $tidObject = new Tid($id);
        $this->assertEquals($expectedEarliestTime, TidHelper::earliestTime($tidObject));
    }

    public function testLatestTime()
    {
        // Test with a known value
        $time = time();
        $time = $time >> TidHelper::DROPPED_BITS;
        $time = $time << TidHelper::ENTROPY_BITS;
        $entropy = 12345; // Some arbitrary entropy value
        $id = $time | $entropy;

        $earliestTime = ($time >> TidHelper::ENTROPY_BITS) << TidHelper::DROPPED_BITS;
        $expectedLatestTime = $earliestTime | (2 ** TidHelper::ENTROPY_BITS - 1);
        $this->assertEquals($expectedLatestTime, TidHelper::latestTime($id));

        // Test with a Tid object
        $tidObject = new Tid($id);
        $this->assertEquals($expectedLatestTime, TidHelper::latestTime($tidObject));
    }

    public function testEntropyBits()
    {
        // Test with a known value
        $time = time();
        $time = $time >> TidHelper::DROPPED_BITS;
        $time = $time << TidHelper::ENTROPY_BITS;
        $entropy = 12345; // Some arbitrary entropy value
        $id = $time | $entropy;

        $this->assertEquals($entropy, TidHelper::entropyBits($id));

        // Test with a Tid object
        $tidObject = new Tid($id);
        $this->assertEquals($entropy, TidHelper::entropyBits($tidObject));
    }

    public function testValidateInt()
    {
        // Valid IDs
        $validId = TidHelper::generateInt();
        $this->assertTrue(TidHelper::validateInt($validId));

        // Invalid IDs
        $this->assertFalse(TidHelper::validateInt(-1)); // Negative

        // ID with future timestamp
        $futureTime = time() + (2 ** TidHelper::DROPPED_BITS);
        $futureTime = $futureTime >> TidHelper::DROPPED_BITS;
        $futureTime = $futureTime << TidHelper::ENTROPY_BITS;
        $futureId = $futureTime | 12345;
        $this->assertFalse(TidHelper::validateInt($futureId));

        // ID with negative timestamp
        $negativeTime = -1000;
        $negativeTime = $negativeTime >> TidHelper::DROPPED_BITS;
        $negativeTime = $negativeTime << TidHelper::ENTROPY_BITS;
        $negativeId = $negativeTime | 12345;
        $this->assertFalse(TidHelper::validateInt($negativeId));
    }

    public function testValidateString()
    {
        // Valid strings
        $validId = TidHelper::generateInt();
        $validString = TidHelper::toString($validId);
        $this->assertTrue(TidHelper::validateString($validString));

        // Invalid strings
        $this->assertFalse(TidHelper::validateString(''));
        $this->assertFalse(TidHelper::validateString('invalid!@#'));
        $this->assertFalse(TidHelper::validateString('99999999999999999999999999999999999999')); // Too large

        // String with future timestamp
        $futureTime = time() + (2 ** TidHelper::DROPPED_BITS);
        $futureTime = $futureTime >> TidHelper::DROPPED_BITS;
        $futureTime = $futureTime << TidHelper::ENTROPY_BITS;
        $futureId = $futureTime | 12345;
        $futureString = TidHelper::toString($futureId);
        $this->assertFalse(TidHelper::validateString($futureString));
    }

    public function testToInt()
    {
        // Test with valid string
        $id = TidHelper::generateInt();
        $string = TidHelper::toString($id);
        $this->assertEquals($id, TidHelper::toInt($string));

        // Test with string containing dashes (should be ignored)
        $stringWithDashes = str_replace('', '-', $string);
        $this->assertEquals($id, TidHelper::toInt($stringWithDashes));
    }

    public function testToIntWithInvalidCharacters()
    {
        $this->expectException(InvalidArgumentException::class);
        TidHelper::toInt('A!B@C#');
    }

    public function testToIntWithEmptyString()
    {
        $this->expectException(InvalidArgumentException::class);
        TidHelper::toInt('');
    }

    public function testToString()
    {
        // Test with integer
        $id = TidHelper::generateInt();
        $string = TidHelper::toString($id);
        $this->assertIsString($string);
        $this->assertEquals($id, TidHelper::toInt($string));

        // Test with Tid object
        $tidObject = new Tid($id);
        $this->assertEquals($string, TidHelper::toString($tidObject));

        // Test with precomputed value
        $int = 28740015009630;
        $string = "a6qz-aw3fi";
        $this->assertEquals($string, TidHelper::toString($int));
    }

    public function testRoundTrip()
    {
        // Test round trip conversion for multiple IDs
        for ($i = 0; $i < 10; $i++) {
            $id = TidHelper::generateInt();
            $string = TidHelper::toString($id);
            $this->assertEquals($id, TidHelper::toInt($string));
        }
    }

    public function testTimeOrderedProperty()
    {
        // Generate IDs with a large enough delay between them to matter
        $id1_time = time();
        $id2_time = $id1_time + (TidHelper::DROPPED_BITS ** 2);
        $id1 = $id1_time >> TidHelper::DROPPED_BITS;
        $id1 = $id1 << TidHelper::ENTROPY_BITS;
        $id2 = $id2_time >> TidHelper::DROPPED_BITS;
        $id2 = $id2 << TidHelper::ENTROPY_BITS;

        // The earliest time of the second ID should be greater than or equal to the first
        $this->assertGreaterThanOrEqual(
            TidHelper::earliestTime($id1),
            TidHelper::earliestTime($id2)
        );

        // When sorted as strings, they should maintain chronological order
        $string1 = TidHelper::toString($id1);
        $string2 = TidHelper::toString($id2);

        // This test might occasionally fail if the IDs are generated within the same time bucket
        // due to the DROPPED_BITS, but it should pass most of the time
        $this->assertLessThanOrEqual($string2, $string1);
    }
}
