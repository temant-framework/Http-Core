# Temant HTTP Core

[![Packagist Version](https://img.shields.io/packagist/v/temant-framework/http-core)](https://packagist.org/packages/temant-framework/http-core)
[![Packagist Downloads](https://img.shields.io/packagist/dt/temant-framework/http-core)](https://packagist.org/packages/temant-framework/http-core)
[![CI Status](https://github.com/temant-framework/http-core/actions/workflows/ci.yml/badge.svg)](https://github.com/temant-framework/http-core/actions/workflows/ci.yml)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-Level%209-brightgreen)](https://github.com/temant-framework/temant-http-core)
[![Coverage](https://codecov.io/gh/temant-framework/http-core/branch/main/graph/badge.svg)](https://codecov.io/gh/temant-framework/http-core)


Temant HTTP Core is a lightweight implementation of the PSR-7 HTTP message interfaces and the PSR-17 HTTP message factory interfaces. It provides a clean and modern foundation for working with HTTP requests, responses, streams, URIs, and uploaded files in PHP.

The goal of this library is to offer a simple and standards-compliant package written for modern PHP without unnecessary overhead.

---

## Features

- Fully compliant with [PSR-7](https://www.php-fig.org/psr/psr-7/) and [PSR-17](https://www.php-fig.org/psr/psr-17/)
- Focused and minimal API surface
- Immutable message objects
- Supports all HTTP message components, including headers, bodies, uploaded files and URIs
- Tested and analyzed with PHPUnit and PHPStan

---

## Requirements

- PHP `8.4` or higher
- `psr/http-message` ^2.0
- `psr/http-factory` ^1.1

---

## Installation

Install via Composer:

```bash
composer require temant/http-core
```

---

## Usage Example

```php
<?php

declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use Temant\HttpCore\Factory\RequestFactory;
use Temant\HttpCore\Factory\ResponseFactory;
use Temant\HttpCore\Factory\StreamFactory;

// Create a request instance
$request = new RequestFactory()
    ->createRequest('GET', 'https://example.com');

// Create a response with text content
$stream = new StreamFactory()
    ->createStream('Hello Temant');

$response = new ResponseFactory()
    ->createResponse()
    ->withBody($stream);

echo $response->getStatusCode(); // 200
echo $response->getBody();       // Hello Temant
```

Additional examples for headers, query parameters, uploaded files, and streams will be added soon.

---

## Development

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

This library is compatible with the official `http-interop/http-factory-tests`.

---

## Project Structure

```
Src/
Tests/
composer.json
```

Autoloading follows PSR-4 for both source and test directories.

---

## Contributing

Contributions are welcome. Please ensure that new code includes relevant tests. Bug reports and improvement suggestions are appreciated.

---

## License

Temant HTTP Core is open-sourced software licensed under the MIT license.

---
