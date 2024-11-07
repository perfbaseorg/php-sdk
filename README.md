# Perfbase PHP SDK

A PHP SDK for sending application profiling data to Perfbase. This SDK allows you to easily integrate performance monitoring into your PHP applications.

## Installation

Install the package via composer:

```bash
composer require perfbase/php-sdk
```

## Requirements

- PHP 8.0 or higher
- Composer

## Quick Start

```php
use Perfbase\SDK\Client;
use Perfbase\SDK\Config;

// Initialize the client
$client = new Client(new Config(
    apiKey: 'your-api-key'
));

// Start profiling
$client->startProfiling();

// Your code here
// ...

// Stop profiling and send data
$client->stopProfiling();
```

## Configuration

You can configure the SDK using either direct initialization or an array:

```php
// Direct initialization
$config = new Config(
    apiKey: 'your-api-key',
    apiUrl: 'https://api.perfbase.com/v1', // Optional
    enabled: true,                         // Optional
    timeout: 1                             // Optional
);

// Or using an array
$config = Config::fromArray([
    'api_key' => 'your-api-key',
    'api_url' => 'https://api.perfbase.com/v1',
    'enabled' => true,
    'timeout' => 1
]);
```

### Configuration Options

| Option    | Type    | Default                       | Description                    |
| --------- | ------- | ----------------------------- | ------------------------------ |
| `api_key` | string  | required                      | Your Perfbase API key          |
| `api_url` | string  | `https://api.perfbase.com/v1` | API endpoint URL               |
| `enabled` | boolean | `true`                        | Enable/disable profiling       |
| `timeout` | integer | `1`                           | API request timeout in seconds |

## Collected Data

The SDK collects the following metrics:

- Timestamp
- Duration
- Memory peak usage
- Stack traces with:
  - File and line information
  - Function names
  - Execution time
  - Memory usage

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE.txt) file for details.

## Support

For support, please contact support@perfbase.com or visit our [documentation](https://docs.perfbase.com).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
