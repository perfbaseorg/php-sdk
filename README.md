# Perfbase PHP SDK

![Packagist License](https://img.shields.io/packagist/l/perfbase/php-sdk)
![Packagist Version](https://img.shields.io/packagist/v/perfbase/php-sdk)
![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/perfbaseorg/php-sdk/ci.yml?branch=main)

A comprehensive PHP SDK for application performance monitoring (APM) and profiling with Perfbase. This SDK provides real-time performance insights, distributed tracing, and detailed profiling capabilities for PHP applications.

## Important: Using a PHP Framework?

If you're using a PHP framework, we highly recommend using our dedicated framework integrations for the best experience:

- **Laravel**: Use `perfbase/laravel` for automatic integration with Laravel applications
- **Symfony**: Coming soon
- **Other frameworks**: This SDK provides the foundation for custom integrations

If you're **NOT using a framework** or need **custom integration** - this is the SDK for you!

## Features

- 🚀 **Real-time Performance Profiling** - CPU time, memory usage, and execution tracing
- 📊 **Multi-span Tracing** - Track multiple concurrent operations within a single request
- 🔍 **Database Query Tracking** - Monitor PDO, MongoDB, Elasticsearch queries
- 🌐 **HTTP Request Monitoring** - Track outbound HTTP calls and API requests
- 💾 **Cache Operation Tracking** - Monitor Redis, Memcached, and other cache operations
- ⚡ **Queue System Monitoring** - Track background job performance
- 🏷️ **Custom Attributes** - Add contextual metadata to your traces
- 🔧 **Configurable Feature Flags** - Enable/disable specific profiling features
- 🛡️ **Multi-tenant Support** - Organization and project-level data isolation

## Requirements

- **PHP**: `7.4` to `8.4`
- **Operating System**: Linux or macOS (Windows not supported)
- **Dependencies**: 
  - `ext-curl` (usually enabled by default)
  - `ext-perfbase` (Perfbase PHP extension)
- **Package Manager**: Composer

## Installation

### 1. Install the SDK

```bash
composer require perfbase/php-sdk
```

### 2. Install the Perfbase PHP Extension

The `ext-perfbase` PHP extension is required for the SDK to function. Install it using:

```bash
bash -c "$(curl -fsSL https://cdn.perfbase.com/install.sh)"
```

**Important**: Restart your web server or PHP-FPM service after installation.

### 3. Verify Installation

```php
<?php
use Perfbase\SDK\Perfbase;

// Check if the extension is available
if (Perfbase::isAvailable()) {
    echo "Perfbase extension is ready!";
} else {
    echo "Perfbase extension not found. Please install ext-perfbase.";
}
```

## Quick Start

### Basic Usage

```php
<?php
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\Config;

// Create configuration
$config = Config::fromArray([
    'api_key' => 'your_api_key_here',
    'api_url' => 'https://receiver.perfbase.com', // Optional: defaults to this URL
]);

// Initialize Perfbase
$perfbase = new Perfbase($config);

// Start profiling a span
$perfbase->startTraceSpan('user_registration');

// Your application code here
registerUser($userData);

// Stop the span
$perfbase->stopTraceSpan('user_registration');

// Submit the trace data to Perfbase
$perfbase->submitTrace();
```

### Advanced Usage with Attributes

```php
<?php
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\Config;
use Perfbase\SDK\FeatureFlags;

// Configure with custom settings
$config = Config::fromArray([
    'api_key' => 'your_api_key_here',
    'timeout' => 15,
    'flags' => FeatureFlags::AllFlags, // Enable all profiling features
]);

$perfbase = new Perfbase($config);

// Start span with initial attributes
$perfbase->startTraceSpan('api_request', [
    'endpoint' => '/api/v1/users',
    'method' => 'POST',
    'user_id' => '12345'
]);

// Add attributes during execution
$perfbase->setAttribute('request_size', '1024');
$perfbase->setAttribute('cache_hit', 'false');

try {
    // Your application logic
    $result = processApiRequest($request);
    
    $perfbase->setAttribute('status', 'success');
    $perfbase->setAttribute('response_size', strlen($result));
    
} catch (Exception $e) {
    $perfbase->setAttribute('status', 'error');
    $perfbase->setAttribute('error_message', $e->getMessage());
} finally {
    // Always stop the span
    $perfbase->stopTraceSpan('api_request');
}

// Submit trace data
$perfbase->submitTrace();
```

### Multiple Concurrent Spans

```php
<?php
$perfbase = new Perfbase($config);

// Start multiple spans for different operations
$perfbase->startTraceSpan('database_operations');
$perfbase->startTraceSpan('cache_operations');
$perfbase->startTraceSpan('api_calls');

// Perform operations (can be in any order)
performDatabaseQuery();
$perfbase->stopTraceSpan('database_operations');

makeApiCall();
$perfbase->stopTraceSpan('api_calls');

accessCache();
$perfbase->stopTraceSpan('cache_operations');

// Submit all trace data
$perfbase->submitTrace();
```

## Configuration Options

### Basic Configuration

```php
$config = Config::fromArray([
    'api_key' => 'required_api_key',           // Your Perfbase API key
    'api_url' => 'https://custom.endpoint',    // Custom API endpoint (optional)
    'timeout' => 10,                           // Request timeout in seconds (default: 10)
    'proxy' => 'http://proxy.example.com:8080' // Proxy server (optional)
]);
```

### Feature Flags

Control which profiling features are enabled:

```php
use Perfbase\SDK\FeatureFlags;

// Use default flags (recommended for most applications)
$config = Config::fromArray([
    'api_key' => 'your_api_key',
    'flags' => FeatureFlags::DefaultFlags
]);

// Enable all available features
$config = Config::fromArray([
    'api_key' => 'your_api_key',
    'flags' => FeatureFlags::AllFlags
]);

// Custom combination
$customFlags = FeatureFlags::TrackCpuTime | 
               FeatureFlags::TrackPdo | 
               FeatureFlags::TrackHttp;

$config = Config::fromArray([
    'api_key' => 'your_api_key',
    'flags' => $customFlags
]);

// Change flags at runtime
$perfbase->setFlags(FeatureFlags::TrackCpuTime | FeatureFlags::TrackMemoryAllocation);
```

### Available Feature Flags

| Flag | Description |
|------|-------------|
| `UseCoarseClock` | Faster, less accurate timing (reduces overhead) |
| `TrackCpuTime` | Monitor CPU time usage |
| `TrackMemoryAllocation` | Track memory allocation patterns |
| `TrackPdo` | Monitor database queries via PDO |
| `TrackHttp` | Track outbound HTTP requests |
| `TrackCaches` | Monitor cache operations (Redis, Memcached) |
| `TrackMongodb` | Track MongoDB operations |
| `TrackElasticsearch` | Monitor Elasticsearch queries |
| `TrackQueues` | Track queue/background job operations |
| `TrackAwsSdk` | Monitor AWS SDK operations |
| `TrackFileOperations` | Track file I/O operations |
| `TrackFileCompilation` | Monitor PHP file compilation |
| `TrackFileDefinitions` | Track PHP class/function definitions |
| `TrackExceptions` | Monitor exception handling |

## API Reference

### Core Methods

#### `startTraceSpan(string $spanName, array $attributes = []): void`
Start profiling a named span with optional initial attributes.

```php
$perfbase->startTraceSpan('user_login', [
    'user_id' => '123',
    'login_method' => 'oauth'
]);
```

#### `stopTraceSpan(string $spanName): bool`
Stop profiling a named span. Returns `true` if successful, `false` if span wasn't active.

```php
$success = $perfbase->stopTraceSpan('user_login');
```

#### `setAttribute(string $key, string $value): void`
Add a custom attribute to the current trace.

```php
$perfbase->setAttribute('cache_hit_ratio', '0.85');
```

#### `submitTrace(): void`
Submit collected profiling data to Perfbase and reset the session.

```php
$perfbase->submitTrace();
```

#### `getTraceData(string $spanName = ''): string`
Retrieve raw trace data (useful for debugging or custom processing).

```php
$rawData = $perfbase->getTraceData();
```

#### `reset(): void`
Clear all active spans and reset the profiling session without submitting.

```php
$perfbase->reset();
```

#### `setFlags(int $flags): void`
Change profiling feature flags at runtime.

```php
$perfbase->setFlags(FeatureFlags::TrackCpuTime | FeatureFlags::TrackPdo);
```

#### `isExtensionAvailable(): bool`
Check if the Perfbase extension is loaded and available.

```php
if ($perfbase->isExtensionAvailable()) {
    // Extension is ready
}
```

### Static Methods

#### `Perfbase::isAvailable(): bool`
Static method to check extension availability without instantiating the class.

```php
if (Perfbase::isAvailable()) {
    $perfbase = new Perfbase($config);
}
```

## Error Handling

The SDK is designed to fail gracefully when the extension is not available:

```php
use Perfbase\SDK\Exception\PerfbaseExtensionException;

try {
    $perfbase = new Perfbase($config);
} catch (PerfbaseExtensionException $e) {
    // Extension not available - handle gracefully
    error_log("Perfbase extension not available: " . $e->getMessage());
    // Your application continues normally
}
```

## Best Practices

### 1. Span Naming
Use descriptive, consistent span names:

```php
// Good
$perfbase->startTraceSpan('user_authentication');
$perfbase->startTraceSpan('database_user_lookup');
$perfbase->startTraceSpan('external_api_call');

// Avoid
$perfbase->startTraceSpan('function1');
$perfbase->startTraceSpan('temp_span');
```

### 2. Attribute Usage
Add meaningful context through attributes:

```php
$perfbase->startTraceSpan('database_query', [
    'table' => 'users',
    'operation' => 'SELECT',
    'rows_expected' => '1'
]);

$perfbase->setAttribute('rows_returned', count($results));
$perfbase->setAttribute('query_time_ms', $executionTime);
```

### 3. Error Handling in Spans
Always ensure spans are properly closed:

```php
$perfbase->startTraceSpan('risky_operation');

try {
    performRiskyOperation();
    $perfbase->setAttribute('status', 'success');
} catch (Exception $e) {
    $perfbase->setAttribute('status', 'error');
    $perfbase->setAttribute('error_type', get_class($e));
    throw $e; // Re-throw if needed
} finally {
    $perfbase->stopTraceSpan('risky_operation');
}
```

### 4. Performance Considerations
- Use `FeatureFlags::UseCoarseClock` for high-throughput applications
- Only enable the tracking features you need
- Consider the overhead of frequent `setAttribute()` calls in tight loops

## Troubleshooting

### Extension Not Found
```bash
# Check if extension is loaded
php -m | grep perfbase

# Check PHP configuration
php --ini

# Reinstall extension
bash -c "$(curl -fsSL https://cdn.perfbase.com/install.sh)"
```

### Connection Issues
```php
// Test connectivity
$config = Config::fromArray([
    'api_key' => 'your_api_key',
    'timeout' => 30 // Increase timeout for testing
]);
```

### Debugging
```php
// Get raw trace data for inspection
$traceData = $perfbase->getTraceData();
var_dump($traceData);

// Check extension status
var_dump(Perfbase::isAvailable());
```

## Documentation

Comprehensive documentation is available at [https://docs.perfbase.com](https://docs.perfbase.com), including:

- Data handling policies and security measures
- Legal information and compliance
- Detailed information about data collection and storage
- Advanced configuration options
- Integration guides for various frameworks

## License

This project is licensed under the Apache License 2.0. See the [LICENSE](LICENSE.txt) file for details.

## Support

- **Email**: [support@perfbase.com](mailto:support@perfbase.com)
- **Documentation**: [https://docs.perfbase.com](https://docs.perfbase.com)
- **Issues**: [GitHub Issues](https://github.com/perfbaseorg/php-sdk/issues)

## Contributing

We welcome contributions! Please see our contributing guidelines and feel free to submit pull requests.

---

**Note**: This SDK requires the `ext-perfbase` PHP extension. Without it, the SDK will throw a `PerfbaseExtensionException` during initialization. The extension is currently available for Linux and macOS only.