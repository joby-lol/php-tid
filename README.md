# php-tid

A simple and lightweight time-ordered random ID library designed for human-scale applications.

## What is php-tid?

php-tid is a simple, lightweight library for generating unique, time-ordered, human-readable IDs. Unlike full UUIDs or simple auto-incrementing database IDs, Tids are:

- **Human-readable**: String representations as base 36 integers for more compact format for URLs, markup, etc.
- **Time-ordered**: Can be represented as integers or strings, and both are naturally sortable by rough creation time
- **URL-safe**: No special characters that are hard to type or need encoding in URLs
- **Privacy-conscious**: Can drop optional amounts of timestamp precision to avoid leaking exact creation times
- **Ergonomic**: Strings can be easily converted from the concise format back into the underlying integer, or vice versa
- **Variable-length**: Currently all values are 63 bits, meaning that they will fit in the default integer representation of almost any language/environment, but the length is not fixed so future versions may expand that length if needed.

## Why php-tid?

Not every project needs guaranteed globally unique IDs or distributed systems. For many smaller applications, simpler solutions are often better:

- **Human scale**: Designed for applications where IDs might be seen, shared, or even typed by humans
- **Simplicity**: No external dependencies or complex setup
- **Lightweight**: Minimal overhead and easy integration
- **Chronological**: Natural time-based ordering, with options to keep varying amounts of precision hidden

## Installation

```bash
composer require joby/tid
```

## Basic Usage

### Creating a new Tid

```php
use Joby\Tid\Tid;

// Generate a new Tid
// Default is version 0, which is fully random
// Version 1 keeps the full timestamp, and versions 2-4 trim increasing amounts of precision from the timestamp
$tid = Tid::generate(Tid::VERSION_1);
```

### Tid versions

| Version | Time resolution    | Random bits | String length | Availability     |
| ------- | ------------------ | ----------- | ------------- | ---------------- |
| 0       | N/A (no time data) | 58          | 13            | ~288 quadrillion |
| 1.0     | 1 second           | 11          | 10            | ~1.4 billion/day |
| 1.1     | ~4.25 minutes      | 19          | 10            | ~1.4 billion/day |
| 1.2     | ~18 hours          | 27          | 10            | ~1.4 billion/day |
| 1.3     | ~3 days            | 29          | 10            | ~1.4 billion/day |
| 1.4     | ~12 days           | 31          | 10            | ~1.4 billion/day |

### Tid deterministically derived from string

```php
use Joby\Tid\Tid;

// Create a Tid from a string
$tid = Tid::fromString("abcdefgh");
```

### Getting Tid parts

```php
use Joby\Tid\Tid;

$tid = new Tid();

// Get the approximate timestamp when this Tid was created
// This returns the lower bound of when this Tid was created
$timestamp = $tid->time();

// Get the entropy bits (random portion) of the Tid
$entropy = $tid->random();
```

## Advanced Usage

### Using the underlying integer

```php
use Joby\Tid\Tid;

// Create a Tid
$tid = new Tid();

// Get the underlying integer
$int = $tid->value;

// Convert back to a Tid
$sameTid = new Tid($int);
// or
$sameTid = Tid::fromInt($int);
```

### Serialization

Tid objects can be serialized and unserialized:

```php
use Joby\Tid\Tid;

$tid = new Tid();
$serialized = serialize($tid);
$unserialized = unserialize($serialized);

echo $tid === $unserialized; // false (different objects)
echo $tid->id === $unserialized->id; // true (same ID value)
```

### Using with databases

Tids can be stored in your database as either strings or integers:

```php
// Store as a string (more readable)
$db->query("INSERT INTO users (id, name) VALUES (?, ?)", [(string)$tid, "John"]);

// Store as an integer (more efficient)
$db->query("INSERT INTO users (id, name) VALUES (?, ?)", [$tid->id, "John"]);
```

### Deterministic generation

Tids can also be generated deterministically, if you need to use them in a manner similar to a hash. In this case they are produced as version 0 Tids with no time data, and their random data is produced by truncating a sha256 hmac hash of the provided string.

```php
use Joby\Tid\Tid;

// generate from a string
$tid = Tid::hashGenerate('some value to generate from', 'secret key');
```

## How It Works

Each Tid consists of a single integer value with three parts (starting with the least significant bit):

1. 4-bit version identifier, from which the other two parts' lengths are determined
2. 0 or more bits of random data
3. 0 or more bits of time data, with varying amounts of precision dropped by truncating least significant bits

The combination is encoded in base-36 (alphanumeric) when a string representation is required, but can also be stored as an integer.

## Limitations

- Not designed or suitable for distributed systems requiring guaranteed global uniqueness
- Time ordering may be approximate due to the dropped precision bits
- No built-in collision detection (though collisions are extremely unlikely at human scale applications)