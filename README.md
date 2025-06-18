[![Build Status](https://github.com/sirn-se/phrity-util-transformer/actions/workflows/acceptance.yml/badge.svg)](https://github.com/sirn-se/phrity-util-transformer/actions)
[![Coverage Status](https://coveralls.io/repos/github/sirn-se/phrity-util-transformer/badge.svg?branch=main)](https://coveralls.io/github/sirn-se/phrity-util-transformer?branch=main)

# Introduction

Type transformers, normalizers and resolvers.

## Installation

Install with [Composer](https://getcomposer.org/);
```
composer require phrity/util-transformer
```

# How to use

All transformers exposes `canTransform()` and `transform()` methods.

This allows us to transform data of a certain type to another type.
A specific transformer may not be able to transform all types.

```php
$transformer = new BasicTypeConverter();
if ($transformer->canTransform($subject)) {
    $transformed = transformer->transform($subject);
}
```

As option, a transformer can take a target type specifier as second argument.

```php
$transformer = new BasicTypeConverter();
if ($transformer->canTransform($subject, Type::ARRAY)) {
    $transformed = transformer->transform($subject, Type::ARRAY);
}
```

Utility resolvers enable stacking multiple transformers and performing other tasks.

```php
$transformer = new RecursionResolver(
    new FirstMatchResolver([
        new EnumConverter(),
        new ReadableConverter(),
        new ThrowableConverter(),
        new StringableConverter(),
        new BasicTypeConverter(),
    ])
);
if ($transformer->canTransform($subject, Type::STRING)) {
    $transformed = transformer->transform($subject, Type::STRING);
}
```

# List of transformers in this library

## Type converters

- **[BasicTypeConverter](docs/converters/BasicType.md)** - Support transforming all PHP types to all other types
- **[EnumConverter](docs/converters/Enum.md)** - Transform Enums to string
- **[ReadableConverter](docs/converters/Readable.md)** - Transform booleans and null to readable strings
- **[ReversedReadableConverter](docs/converters/ReversedReadable.md)** - Transform some strings to boolean and null
- **[StringableConverter](docs/converters/Stringable.md)** - Transform stringable objects to string
- **[ThrowableConverter](docs/converters/Throwable.md)** - Transform throwable to object, array or string

## Utility resolvers

- **[FirstMatchResolver](docs/resolvers/FirstMatch.md)** - Collection of transformers that will use first compatible transformer for transformation
- **[RecursionResolver](docs/resolvers/Recursion.md)** - Will apply transformer recursively

# Versions

| Version | PHP | |
| --- | --- | --- |
| `1.1` | `^8.1` | Additional transformers |
| `1.0` | `^8.1` | Initial version |
