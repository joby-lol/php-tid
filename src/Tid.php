<?php

/**
 * Tid Time-ordered IDs: https://github.com/joby-lol/php-tid
 * (c) 2025 Joby Elliott code@joby.lol
 * MIT License https://opensource.org/licenses/MIT
 */

namespace Joby\Tid;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Lightweight time-ordered ID class that is an integer under the hood but is stringable for more human-readable output. String representations will be 10 characters long for years to come, but will grow as the ID integers increase in size. The IDs are sortable by generation time, in both integer and string forms, and the resolution of that sorting is adjustable by picking versions that trim different numbers of bits from the timestamp in favor of more random bits.
 */
class Tid implements Stringable, JsonSerializable
{

    /**
     * Version 0, reserved for entirely random IDs with no time information in them at all. Random IDs are somewhat different, in that they will always occupy the full 63 bits, their most significant bit will always be one (to stabilize their string length), leaving 58 entirely random bits.
     */
    public const VERSION_0 = 0;

    /**
     * Version 1.1, with full second resolution timestamp and 11 random bits.
     */
    public const VERSION_1_0 = 1;

    /**
     * Version 1.2, trims 8 bits for a resolution of about 4.25 minutes, with 19 random bits.
     */
    public const VERSION_1_1 = 2;

    /**
     * Version 1.3, trims 16 bits for a resolution of about 18 hours, with 27 random bits.
     */
    public const VERSION_1_2 = 3;

    /**
     * Version 1.4, trims 18 bits for a resolution of about 3 days, with 29 random bits.
     */
    public const VERSION_1_3 = 4;

    /**
     * Version 1.5, trims 20 bits for a resolution of about 12 days, with 31 random bits.
     */
    public const VERSION_1_4 = 5;

    /**
     * Configuration for each supported version of Tid, mapping version numbers to arrays of [dropped bits, entropy bits].
     */
    public const VERSION_CONFIGS = [
        0 => [64, 59],
        1 => [0, 14],
        2 => [8, 22],
        3 => [16, 30],
        4 => [18, 32],
        5 => [20, 34],
    ];

    /**
     * @var int<0,max> The integer ID value. Should be a positive integer, and as of 2025 all defined versions fit in 63 bits for ease of use and compatibility.
     */
    public readonly int $value;

    public static function fromString(string $tid): Tid
    {
        $int = base_convert(strtolower($tid), 36, 10);
        return new Tid(intval($int));
    }

    public static function fromInt(int $tid): Tid
    {
        return new Tid($tid);
    }

    public static function generate(int $version = self::VERSION_0): Tid
    {
        // special case for fully-random ones
        if ($version == self::VERSION_0) {
            $int = random_int(0, (1 << 58) - 1) << 4;
            $int = $int | self::VERSION_0;
            $int = $int | (1 << 62);
            return new Tid($int);
        }
        // normal generation
        if (!array_key_exists($version, self::VERSION_CONFIGS)) {
            throw new InvalidArgumentException('Unsupported Tid version');
        }
        $droppedBits = self::VERSION_CONFIGS[$version][0];
        $entropyBits = self::VERSION_CONFIGS[$version][1];
        // start with the time portion
        $int = time();
        $int = $int >> $droppedBits;
        // shift to make room for entropy and add it
        $int = $int << $entropyBits;
        $int = $int | random_int(0, (1 << $entropyBits) - 1);
        // add version in the lowest 4 bits
        $int = $int << 4;
        $int = $int | $version;
        // return finished Tid
        return new Tid($int);
    }

    public static function hmacGenerate(string $source, string $secret): Tid
    {
        $hash = hash_hmac('sha256', $source, $secret);
        $int = (int) base_convert(substr($hash, 0, 16), 16, 10);
        // make room for 4 version bits and top 2 msb values of 01
        $int = $int >> 6;
        // lsb is 00 for version 0
        $int = $int << 4;
        // make 63rd bi 1
        $int = $int | 1 << 62;
        // return finished Tid
        return new Tid($int);
    }

    public function __construct(int $id)
    {
        if ($id < 0) {
            throw new InvalidArgumentException('Tid integer must not be negative');
        }
        $this->value = $id;
        if (!array_key_exists($this->version(), self::VERSION_CONFIGS)) {
            throw new InvalidArgumentException('Unsupported Tid version');
        }
    }

    /**
     * Get the version number, which is the lowest 4 bits.
     */
    public function version(): int
    {
        return $this->value & 0x0F;
    }

    /**
     * Return just the timestamp portion of the Tid, at the low end of the window of the time the Tid was generated in.
     */
    public function time(): int
    {
        $version = $this->version();
        if ($version === self::VERSION_0) {
            return 0;
        }
        assert(array_key_exists($version, self::VERSION_CONFIGS));
        $droppedBits = self::VERSION_CONFIGS[$version][0];
        $entropyBits = self::VERSION_CONFIGS[$version][1];
        $int = $this->value >> 4; // remove version bits
        $int = $int >> $entropyBits; // remove entropy bits
        $int = $int << $droppedBits; // shift back to original position
        return $int;
    }

    /**
     * Return the random portion of the Tid, which is the low bits after the timestamp but before the version.
     */
    public function random(): int
    {
        $version = $this->version();
        if ($version === self::VERSION_0) {
            return $this->value >> 4; // remove version bits
        }
        assert(array_key_exists($version, self::VERSION_CONFIGS));
        $entropyBits = self::VERSION_CONFIGS[$version][1];
        $int = $this->value >> 4; // remove version bits
        $int = $int & ((1 << $entropyBits) - 1); // mask to get only the random bits
        return $int;
    }

    /**
     * Get the number of random bits for this Tid version.
     */
    public function entropyBits(): int
    {
        return self::VERSION_CONFIGS[$this->version()][1];
    }

    public function __toString(): string
    {
        return base_convert((string)$this->value, 10, 36);
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
