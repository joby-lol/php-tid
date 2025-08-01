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
use Throwable;

/**
 * Class for generating Tid integers and converting them back and forth to
 * strings, without instantiating an object. The object just uses this class to
 * convert itself to a string.
 */
class TidHelper
{
    /**
     * The number of entropy bits to include in a Tid. These are generated randomly and become the least significant
     * bits of the Tid.
     */
    const ENTROPY_BITS = 32;

    /**
     * The number of bits to drop from the right side of the integer, partly to save space, partly to avoid leaking the
     * exact times things were created. The current value of 16 means the Tid will contain time information with a
     * resolution of a little under 73 hours, which is totally sufficient for human-scale web development.
     *
     * Since we add 32 bits of entropy, we're effectively down to 32 bits of room for timestamps, but since we drop 18
     * bits from them, we get back up to 50 bits until the length of a timestamp becomes an issue. That's some tens of
     * millions of years off, though, so I think we're probably fine.
     */
    const DROPPED_BITS = 18;

    /**
     * Generate a new Tid integer using the current time and some random bits.
     */
    public static function generateInt(): int
    {
        $int = time();
        $int = $int >> self::DROPPED_BITS;
        $int = $int << self::ENTROPY_BITS;
        /** @noinspection PhpUnhandledExceptionInspection */
        return $int | random_int(0, 2 ** self::ENTROPY_BITS - 1);
    }

    /**
     * Extract the timestamp portion of the given int. This is the earliest the given Tid could have been created.
     */
    public static function earliestTime(int|Tid $int): int
    {
        $int = is_int($int) ? $int : $int->id;
        $int = $int >> self::ENTROPY_BITS;
        return $int << self::DROPPED_BITS;
    }

    /**
     * Extract the latest possible time this Tid could have been created.
     */
    public static function latestTime(int|Tid $int): int
    {
        $int = is_int($int) ? $int : $int->id;
        $int = static::earliestTime($int);
        return $int | (2 ** self::ENTROPY_BITS - 1);
    }

    /**
     * Extract the entropy bits of the given Tid.
     */
    public static function entropyBits(int|Tid $int): int
    {
        $int = is_int($int) ? $int : $int->id;
        // get the least significant ENTROPY_BITS bits of the integer
        return $int & (2 ** self::ENTROPY_BITS - 1);
    }

    /**
     * Validate that the given string is a valid set of base 36 characters, and that when converted to an integer it is
     * a valid integer.
     */
    public static function validateString(string $string): bool
    {
        $string = str_replace('-', '', $string);
        if (!ctype_alnum($string)) return false;
        try {
            $int = static::toInt($string);
            return static::validateInt($int);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Validate an integer is a valid Tid, which means that it is positive, and that its earliest time is not negative
     * or in the future.
     * @phpstan-assert $int > 0
     */
    public static function validateInt(int $int): bool
    {
        if ($int < 0) return false;
        if (static::earliestTime($int) > time()) return false;
        if (static::earliestTime($int) < 0) return false;
        return true;
    }

    /**
     * Parse a Tid from a string.
     */
    public static function toInt(string $input): int
    {
        $input = str_replace('-', '', $input);
        if (!$input) throw new InvalidArgumentException('Invalid empty Tid string');
        if (!ctype_alnum($input)) throw new InvalidArgumentException('Invalid Tid characters');
        $int = intval(base_convert($input, 36, 10));
        if (!static::validateInt($int)) throw new InvalidArgumentException('Invalid Tid value');
        return $int;
    }

    /**
     * Convert a Tid integer to a string.
     */
    public static function toString(int|Tid $int): string
    {
        $int = is_int($int) ? $int : $int->id;
        return static::formatString(
            base_convert(strval($int), 10, 36)
        );
    }

    /**
     * Format a string with spacer dashes to aid readability.
     */
    public static function formatString(string $input): string
    {
        $input = str_split($input);
        $input = array_filter($input, ctype_alnum(...));
        $input = implode('', $input);
        $chunks = str_split($input, 4);
        if (count($chunks) > 1 && strlen(end($chunks)) < 3) {
            $last = array_pop($chunks);
            $chunks[count($chunks) - 1] .= $last;
        }
        return implode('-', $chunks);
    }
}
