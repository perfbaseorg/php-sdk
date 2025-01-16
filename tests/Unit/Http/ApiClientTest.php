<?php

use Perfbase\SDK\Http\ApiClient;
use Perfbase\SDK\Config;
use Perfbase\SDK\Exception\PerfbaseApiKeyMissingException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('throws an exception if the API key is missing', function () {
    $config = new Config(); // apiKey is null by default

    expect(fn () => new ApiClient($config))->toThrow(PerfbaseApiKeyMissingException::class);
});

it('initializes with a valid API key', function () {
    $config = new Config();
    $config->apiKey = 'test_api_key';

    $apiClient = new ApiClient($config);

    expect($apiClient)->toBeInstanceOf(ApiClient::class);
});

it('sends a synchronous POST request and returns the response', function () {
    $mock = new MockHandler([
        new Response(200, [], 'Success'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

    $config = new Config();
    $config->apiKey = 'test_api_key';

    $apiClient = new ApiClient($config);
    $reflection = new ReflectionClass($apiClient);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);
    $property->setValue($apiClient, $guzzleClient);

    $response = $apiClient->post('/test-endpoint', ['key' => 'value']);

    expect($response)->toBe('Success');
});

it('sends an asynchronous POST request and does not block', function () {
    $mock = new MockHandler([
        new Response(200, [], 'Success'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

    $config = new Config();
    $config->apiKey = 'test_api_key';

    $apiClient = new ApiClient($config);
    $reflection = new ReflectionClass($apiClient);
    $property = $reflection->getProperty('httpClient');
    $property->setAccessible(true);
    $property->setValue($apiClient, $guzzleClient);

    $response = $apiClient->post('/test-endpoint', ['key' => 'value'], true);

    expect($response)->toBeNull();

    // Check if promises array is populated
    $promisesProperty = $reflection->getProperty('promises');
    $promisesProperty->setAccessible(true);

    /** @var array<PromiseInterface> $promises */
    $promises = $promisesProperty->getValue($apiClient);
    expect($promises)->toBeArray()->toHaveLength(1);
    expect($promises[0])->toBeInstanceOf(PromiseInterface::class);
});
