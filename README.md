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

## Requirements
- PHP version `7.4` → `8.4`.
- Linux or MacOS
- Composer 
- `ext-curl` (Note: this is usually enabled by default)
- `ext-perfbase` PHP extension installed and enabled.

## SDK Installation

Install the package via composer:

```bash
composer require perfbase/php-sdk
```

## Install the Perfbase PHP extension.
The `ext-perfbase` PHP extension is required for the SDK to function properly. 
To install the module, you must be running Linux or MacOS. The extension is not available for Windows or ZTS at this time.

You can install it using the following command:
```bash
bash -c "$(curl -fsSL https://cdn.perfbase.com/install.sh)"
```
This command will download and install the `ext-perfbase` extension for your PHP installation. Make sure to restart your web server or PHP-FPM service after installation.

## SDK Quick Start
```php
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\Config;

// Firstly, we need to create a configuration object
// Set the API key. The API key is required for authentication
$config = Config::fromArray([
    'api_key' => 'your_api_key_here',
]);

// Create a new instance of the Perfbase SDK
$perfbase = new Perfbase($config);

// Start a trace span, this will begin collecting performance data
// The span name is used to identify the trace in the Perfbase dashboard
// You can use any name you like, and you can run multiple spans at the same time.
// This is useful for profiling different parts of your application.
$perfbase->startTraceSpan('test_span');

// !!!! Your code goes here !!!! //

// Stop the trace span, this will stop collecting performance data
$perfbase->stopTraceSpan('test_span');

// Now we can submit the trace data to the Perfbase API
$perfbase->submitTrace();

// Complete!
```

## License

This project is licensed under the Apache License 2.0 - see the [LICENSE](LICENSE.txt) file for details.

## Support

For support, please contact [support@perfbase.com](support@perfbase.com) or visit our [documentation](https://docs.perfbase.com).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
