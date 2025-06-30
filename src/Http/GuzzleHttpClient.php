<?php

namespace Perfbase\SDK\Http;

use GuzzleHttp\Client as GuzzleClient;
use Throwable;

class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClient $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function post(string $uri, array $options = []): void
    {
        try {
            $this->client->post($uri, $options);
        } catch (Throwable $e) {
            // Silent failure as per original implementation
            // Could be made configurable in the future
        }
    }
}