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
use Stringable;

/**
 * Lightweight time-ordered ID class that is an integer under the hood but is stringable for more human-readable output.
 */
readonly class Tid implements Stringable
{
    public int $id;

    public function __construct(int $id = null)
    {
        if (is_null($id)) {
            $this->id = TidHelper::generateInt();
        } else {
            if (!TidHelper::validateInt($id)) throw new InvalidArgumentException('Invalid Tid integer');
            $this->id = $id;
        }
    }

    public static function fromString(string $string): Tid
    {
        $int = TidHelper::toInt($string);
        return new Tid($int);
    }

    public static function fromInt(int $int): Tid
    {
        return new Tid($int);
    }

    public function time(): int
    {
        return TidHelper::earliestTime($this->id);
    }

    public function entropy(): int
    {
        return TidHelper::entropyBits($this->id);
    }

    public function __toString(): string
    {
        return TidHelper::toString($this->id);
    }

    public function compactString(): string
    {
        return str_replace('-', '', $this->__toString());
    }
}
