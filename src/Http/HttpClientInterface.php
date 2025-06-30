<?php

namespace Perfbase\SDK\Http;

interface HttpClientInterface
{
    /**
     * Send a POST request
     * @param string $uri The URI to send the request to
     * @param array<string, mixed> $options Request options including headers, body, etc.
     * @return void
     * @throws \Throwable If the request fails
     */
    public function post(string $uri, array $options = []): void;
}