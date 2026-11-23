<p align="center">
  <a href="https://perfbase.com">
    <img src="https://cdn.perfbase.com/img/logo-full.svg" alt="Perfbase" width="300">
  </a>
</p>

<h3 align="center">Perfbase PHP SDK</h3>
<p align="center">
  The official PHP SDK for <a href="https://perfbase.com">Perfbase</a> — application performance monitoring and profiling for PHP applications.
</p>

<p align="center">
  <a href="https://packagist.org/packages/perfbase/php-sdk"><img src="https://img.shields.io/packagist/v/perfbase/php-sdk" alt="Packagist Version"></a>
  <a href="https://github.com/perfbaseorg/php-sdk/blob/main/LICENSE.txt"><img src="https://img.shields.io/packagist/l/perfbase/php-sdk" alt="License"></a>
  <a href="https://github.com/perfbaseorg/php-sdk/actions/workflows/ci.yml"><img src="https://img.shields.io/github/actions/workflow/status/perfbaseorg/php-sdk/ci.yml?branch=main" alt="CI"></a>
  <img src="https://img.shields.io/badge/php-7.4%2B-blue" alt="PHP Version">
</p>

## Using a PHP framework?

If you're using a PHP framework, we recommend using our dedicated framework integrations for the best experience:

- **Laravel**: [`perfbase/laravel`](https://packagist.org/packages/perfbase/laravel)
- **Symfony**: [`perfbase/symfony`](https://packagist.org/packages/perfbase/symfony)
- **WordPress**: [`perfbase/wordpress`](https://packagist.org/packages/perfbase/wordpress)
- **CakePHP**: [`perfbase/cakephp`](https://packagist.org/packages/perfbase/cakephp)
- **Drupal**: [`perfbase/drupal`](https://packagist.org/packages/perfbase/drupal)
- **Slim**: [`perfbase/slim`](https://packagist.org/packages/perfbase/slim)
- **Joomla**: [`perfbase/joomla`](https://packagist.org/packages/perfbase/joomla)
- **CodeIgniter 4**: [`perfbase/codeigniter4`](https://packagist.org/packages/perfbase/codeigniter4)
- **Yii 1.1**: [`perfbase/yii1`](https://packagist.org/packages/perfbase/yii1)
- **Yii 2**: [`perfbase/yii2`](https://packagist.org/packages/perfbase/yii2)
- **Yii 3**: [`perfbase/yii3`](https://packagist.org/packages/perfbase/yii3)

If you're not using a framework or need a custom integration, this SDK is for you.

## Features

- **Performance profiling** — wall time, optional CPU time and memory, full call trees
- **Multi-span tracing** — track multiple concurrent operations within a single request
- **Database query tracking** — monitor PDO, MongoDB, Elasticsearch queries
- **HTTP request monitoring** — track outbound HTTP calls and API requests
- **Cache operation tracking** — monitor Redis, Memcached, and other cache operations
- **Custom attributes** — add contextual metadata to your traces
- **Configurable feature flags** — enable/disable specific profiling features
- **Runtime events** — PHP errors, exceptions, file compilation, OPcache statistics

## Requirements

- **PHP**: `7.4` to `8.6`
- **Operating System**: Linux or macOS (Windows not supported)
- **Dependencies**: 
  - `ext-curl` (usually enabled by default)
  - `ext-perfbase` (Perfbase PHP extension)
- **Package Manager**: Composer

## Installation

### 1. Install the SDK

```bash
composer require perfbase/php-sdk:^1.0
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
    'api_url' => 'https://ingress.perfbase.cloud', // Optional: defaults to this URL
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
$result = $perfbase->submitTrace();
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
    'flags' => FeatureFlags::AllFlags, // Extension default set (all but the opt-in flags)
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
    $perfbase->setAttribute('response_size', (string) strlen($result));
    
} catch (Exception $e) {
    $perfbase->setAttribute('status', 'error');
    $perfbase->setAttribute('error_message', $e->getMessage());
} finally {
    // Always stop the span
    $perfbase->stopTraceSpan('api_request');
}

// Submit trace data
$result = $perfbase->submitTrace();
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
$result = $perfbase->submitTrace();
```

## Configuration Options

### Basic Configuration

```php
$config = Config::fromArray([
    'api_key' => 'required_api_key',           // Your Perfbase API key
    'api_url' => 'https://custom.endpoint',    // Custom API endpoint (optional, HTTPS only)
    'timeout' => 10,                           // Request timeout in seconds (default: 10)
    'proxy' => 'http://proxy.example.com:8080' // Proxy server (optional: http, https, socks5, socks5h)
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

// The extension's default set (everything except the opt-in flags)
$config = Config::fromArray([
    'api_key' => 'your_api_key',
    'flags' => FeatureFlags::AllFlags
]);

// The extension's default set plus per-function CPU time
$config = Config::fromArray([
    'api_key' => 'your_api_key',
    'flags' => FeatureFlags::TrackCpuTime
]);

// Exactly these features (CPU time, PDO and HTTP, nothing else)
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

Flags mirror the extension's `PERFBASE_FLAG_*` constants. Wall time is always tracked.

- `FeatureFlags::DefaultFlags` (the default) — every flag below except `TrackCpuTime` and `TrackMemoryAllocation`.
- `FeatureFlags::AllFlags` (`0`) — every feature except the opt-in flags `UsePreciseClock` and `TrackCpuTime`.
- A mask of **only** opt-in flags means "the extension's default set plus those", so `FeatureFlags::TrackCpuTime` on its own enables everything plus CPU time.
- Any mask containing a regular flag enables **exactly** those features. `AllFlags | TrackPdo` is just `TrackPdo`.

| Flag | Description |
|------|-------------|
| `UsePreciseClock` | **Opt-in.** Guarantee high-resolution wall timing on hosts where the default would be the coarse kernel-tick clock |
| `TrackCpuTime` | **Opt-in.** Per-function CPU time (a syscall per read on Linux, twice per call) |
| `TrackMemoryAllocation` | Per-function memory allocation and deallocation |
| `TrackExceptions` | Count and sample exception throws (PHP 8.0+) |
| `TrackFileCompilation` | Count and time file compilation (`include` / `require`) |
| `TrackPdo` | PDO, mysqli and legacy mysql queries (normalised SQL) |
| `TrackMongodb` | MongoDB operations |
| `TrackElasticsearch` | Elasticsearch operations |
| `TrackCaches` | Redis and Memcached calls |
| `TrackHttp` | cURL, Guzzle and AWS SDK calls |
| `TrackMail` | `mail()` calls |
| `TrackFileOperations` | `fopen`, `fread`, `fwrite`, and related file I/O |
| `TrackProc` | `exec`, `shell_exec`, `proc_open`, `popen` and the `sleep` family |
| `TrackProcessList` | Capture the top running processes on the host at trace end |
| `TrackErrors` | Count and sample PHP errors, warnings, and notices (PHP 8.0+) |
| `TrackMagicMethods` | Count magic method invocations (`__call`, `__get`, ...) |
| `TrackOpcache` | OPcache hit/miss deltas and memory statistics |
| `TrackStreams` | PHP 8.6+: stream errors and `Io\Poll\Context::wait` metadata (inert on older PHP) |

`TrackWallTime`, `TrackArguments`, `TrackFileDefinitions`, `TrackSessions`, `TrackSerialization` and `TrackRegex` are deprecated: they never had any effect and the extension ignores their bits. They are still accepted so existing configuration keeps working; remove them when convenient.

## API Reference

### Core Methods

#### `startTraceSpan(string $spanName, array $attributes = []): void`
Start profiling a named span with optional initial attributes.

Span names must be 1-64 characters and may only contain letters, numbers, hyphens, and underscores. Empty or whitespace-only names normalize to `default`.

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

#### `submitTrace(): SubmitResult`
Submit collected profiling data to Perfbase. Resets the session on success; preserves trace state on failure so callers can decide whether to retry or discard.

The SDK sends the exact raw bytes returned by `perfbase_get_data()` as the request body. It adds the payload encoding version and client-created timestamp as HTTP headers; it does not JSON-wrap or base64-encode the trace payload.

The returned `SubmitResult` provides:
- `isSuccess(): bool` — delivery confirmed
- `isRetryable(): bool` — transient failure (network error, 429, 5xx)
- `isPermanentFailure(): bool` — non-retryable (401, 403, 400, etc.)
- `getStatusCode(): ?int` — HTTP status code if a response was received
- `getMessage(): string` — human-readable description

```php
$result = $perfbase->submitTrace();

if (!$result->isSuccess()) {
    // Handle failure — trace state is preserved for retry
    error_log("Trace submission failed: " . $result->getMessage());
}
```

#### `getTraceData(string $spanName = ''): string`
Retrieve the raw Brotli-compressed MessagePack trace payload produced by the extension (useful for debugging or custom processing).

The current extension only supports whole-trace retrieval. Pass the default empty argument; the `spanName` parameter is kept only for backwards compatibility, and non-empty values are rejected.

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

### URL sanitisation

`Perfbase\SDK\Utils\UrlSanitizer` prepares a URL for a trace attribute such as `http_url`. It keeps the query string (`/ajax.php?method=example` is often what identifies the operation) and replaces the value of any parameter whose name looks like a credential with `REDACTED`. It also drops `user:pass@` and the `#fragment`.

```php
use Perfbase\SDK\Utils\UrlSanitizer;

UrlSanitizer::sanitize('https://example.com/ajax.php?method=example&access_token=abc');
// https://example.com/ajax.php?method=example&access_token=REDACTED

// Add application-specific names (matched as whole words, case-insensitive)
UrlSanitizer::sanitize($url, ['coupon', 'order_ref']);
```

Parameter names are split into words (`_`, `-`, `.`, brackets, camelCase), so `token`, `access_token`, `user[password]`, `apiKey`, `X-Amz-Signature`, `_wpnonce`, `email` or `PHPSESSID` are redacted while `author`, `compass` or `key` are not.

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

// Delivery failures are returned as SubmitResult, not thrown.
// submitTrace() only throws PerfbaseException when the extension hands back
// no trace data or an invalid version, so wrap it if profiling must never
// interrupt your application.
$result = $perfbase->submitTrace();
if ($result->isRetryable()) {
    // Transient failure — safe to retry later
} elseif ($result->isPermanentFailure()) {
    // Non-retryable — check API key, payload, etc.
    error_log("Permanent submission failure: " . $result->getMessage());
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

Span names may only use letters, numbers, hyphens, and underscores, and must not exceed 64 characters.

### 2. Attribute Usage
Add meaningful context through attributes:

```php
$perfbase->startTraceSpan('database_query', [
    'table' => 'users',
    'operation' => 'SELECT',
    'rows_expected' => '1'
]);

$perfbase->setAttribute('rows_returned', (string) count($results));
$perfbase->setAttribute('query_time_ms', (string) $executionTime);
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

### 5. Forwarded Header Trust
`EnvironmentUtils::getUserIp()` is a best-effort helper. It reads common proxy/CDN headers when present, but it does not implement a trusted-proxy policy. Only use forwarded-header-derived IPs when your deployment sanitizes those headers at the edge.

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

Full documentation is available at [perfbase.com/docs](https://perfbase.com/docs).

- **Docs**: [perfbase.com/docs](https://perfbase.com/docs)
- **Issues**: [github.com/perfbaseorg/php-sdk/issues](https://github.com/perfbaseorg/php-sdk/issues)
- **Support**: [support@perfbase.com](mailto:support@perfbase.com)

## License

Apache-2.0. See [LICENSE.txt](LICENSE.txt).
