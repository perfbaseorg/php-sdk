<?php

namespace Perfbase\SDK\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Perfbase\SDK\SubmitResult;
use Throwable;

class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClient $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function post(string $uri, array $options = []): SubmitResult
    {
        try {
            $response = $this->client->post($uri, $options);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                return SubmitResult::success($statusCode);
            }

            // Non-2xx that Guzzle didn't throw on (http_errors disabled)
            return $this->classifyHttpStatus($statusCode);
        } catch (ConnectException $e) {
            // Network-level failure: DNS, timeout, connection refused — always retryable
            return SubmitResult::retryableFailure(null, $e->getMessage());
        } catch (RequestException $e) {
            // HTTP error response (4xx/5xx)
            $response = $e->getResponse();
            $statusCode = $response !== null ? $response->getStatusCode() : null;
            return $this->classifyHttpStatus($statusCode, $e->getMessage());
        } catch (Throwable $e) {
            // Unexpected failure — treat as retryable
            return SubmitResult::retryableFailure(null, $e->getMessage());
        }
    }

    /**
     * Classify an HTTP status code into a submit result.
     *
     * 429 and 5xx are retryable. 4xx (except 429) are permanent.
     */
    private function classifyHttpStatus(?int $statusCode, string $message = ''): SubmitResult
    {
        if ($statusCode === null) {
            return SubmitResult::retryableFailure(null, $message);
        }

        // Rate limited or server errors — retryable
        if ($statusCode === 429 || $statusCode >= 500) {
            return SubmitResult::retryableFailure($statusCode, $message);
        }

        // Client errors (400, 401, 403, 404, etc.) — permanent
        return SubmitResult::permanentFailure($statusCode, $message);
    }
}
