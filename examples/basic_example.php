<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Perfbase\SDK\Perfbase;
use Perfbase\SDK\Config;

// This is a simple example of how to use the Perfbase SDK
// it demonstrates how to start and stop a trace span, and how to submit the trace
// data to the Perfbase API


// Firstly, we need to create a configuration object
// Set the API key. The API key is required for authentication
$config = Config::fromArray([
    'api_key' => 'your_api_key_here',
]);

// Create a new instance of the Perfbase SDK
$perfbase = new Perfbase($config);

// This is a simple method that we will use to demonstrate the SDK
function exampleMethod($size)
{
    $data = str_repeat('a', $size);
    $hash = md5($data);
    return strlen($hash);
}

// Start a trace span, this will begin collecting performance data
// The span name is used to identify the trace in the Perfbase dashboard
// You can use any name you like, and you can run multiple spans at the same time.
// This is useful for profiling different parts of your application.
$perfbase->startTraceSpan('test_span');

// Simulate some work by calling the exampleMethod multiple times
for($i = 0 ; $i < 64; $i++) {
    $output = exampleMethod(1000000);
    unset($output);
}

// Stop the trace span, this will stop collecting performance data
$perfbase->stopTraceSpan('test_span');

// Now we can submit the trace data to the Perfbase API
$perfbase->submitTrace();