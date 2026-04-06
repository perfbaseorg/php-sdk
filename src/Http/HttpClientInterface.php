<?php

namespace Perfbase\SDK\Http;

use Perfbase\SDK\SubmitResult;

interface HttpClientInterface
{
    /**
     * Send a POST request and return a structured result.
     *
     * @param string $uri The URI to send the request to
     * @param array<string, mixed> $options Request options including headers, body, etc.
     * @return SubmitResult
     */
    public function post(string $uri, array $options = []): SubmitResult;
}
