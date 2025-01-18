# Perfbase - PHP SDK

![Packagist License](https://img.shields.io/packagist/l/perfbase/php-sdk)
![Packagist Version](https://img.shields.io/packagist/v/perfbase/php-sdk)
![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/perfbaseorg/php-sdk/ci.yml?branch=main)

A PHP SDK designed to facilitate seamless application profiling and performance monitoring with Perfbase. This SDK offers extensive configurability and enhanced profiling features for integration into your PHP applications.

## Important: Using a PHP Framework?
If you are already using a PHP framework, we highly recommend utilizing one of our dedicated framework integrations. We provide out-of-the-box support for popular frameworks, including Laravel, Symfony, and others.

If you're NOT using a framework - this is the SDK for you!

## Documentation
Comprehensive documentation for all Perfbase libraries is available at: [https://docs.perfbase.com](https://docs.perfbase.com), including detailed information about data handling policies, security measures, legalities, and specifics on what data is transferred and how it is stored.

## Installation

Install the package via composer:

```bash
composer require perfbase/php-sdk
```

## Requirements

- PHP version `7.4` → `8.4`.
- Composer 
- `ext-curl`, `ext-json`, `ext-zlib` (Note: these are usually enabled by default)
- The `perfbase` PHP extension installed and enabled.

## Quick Start

```php
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\Config;

// Initialize the client with configuration
$config = new Config('YOUR_API_KEY');

// Create a new instance of the Perfbase SDK
$perfbase = new Perfbase($config);

// Create a new profiling instance
$instance = $perfbase->createInstance($config);
$instance->startProfiling();

// Your code here
// ...

// Stop profiling and send data to Perfbase
$instance->stopProfiling();

// Complete!
```

## Configuration

You can configure the SDK using direct initialization or an array:

```php
// Direct initialization
$config = new Config(
    api_key: 'YOUR_API_KEY',
    track_file_operations: true
    // ...
);

// Using an array
$config = Config::fromArray([
    'api_key' => 'YOUR_API_KEY',
    'track_pdo' => true,
    'track_caches' => true
    // ...
]);
```

### Configuration Options

| Option                    | Type    | Default  | Description                                  |
|---------------------------|---------|----------|----------------------------------------------|
| `api_key`                 | string  | required | Your Perfbase API key                        |
| `ignored_functions`       | array   | `[]`     | List of functions to ignore during profiling |
| `use_coarse_clock`        | boolean | `false`  | Use faster but less accurate timing          |
| `track_file_compilation`  | boolean | `true`   | Track PHP file compilation times             |
| `track_memory_allocation` | boolean | `false`  | Track memory allocation/deallocation         |
| `track_cpu_time`          | boolean | `true`   | Track CPU usage                              |
| `track_pdo`               | boolean | `true`   | Track PDO database operations                |
| `track_http`              | boolean | `true`   | Track HTTP requests                          |
| `track_caches`            | boolean | `true`   | Track caching mechanisms                     |
| `track_mongodb`           | boolean | `true`   | Track MongoDB operations                     |
| `track_elasticsearch`     | boolean | `true`   | Track Elasticsearch operations               |
| `track_queues`            | boolean | `true`   | Track queue operations                       |
| `track_aws_sdk`           | boolean | `true`   | Track AWS SDK operations                     |
| `track_file_operations`   | boolean | `true`   | Track file operations                        |
| `proxy`                   | string  | `null`   | HTTP/HTTPS proxy for Perfbase API calls      |
| `timeout`                 | int     | `10`     | Timeout seconds for Perfbase API calls       |

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE.txt) file for details.

## Support

For support, please contact [support@perfbase.com](support@perfbase.com) or visit our [documentation](https://docs.perfbase.com).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
