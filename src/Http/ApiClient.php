<?php

namespace Perfbase\SDK\Http;

use Perfbase\SDK\Config;
use Perfbase\SDK\Perfbase;
use Perfbase\SDK\SubmitResult;

class ApiClient
{
    /**
     * Configuration object for the SDK
     * @var Config
     */
    private Config $config;

    /**
     * Default headers to send with each request
     * @var array<string, string>
     */
    private array $defaultHeaders;

    /**
     * HTTP client to send requests.
     * @var HttpClientInterface
     */
    private HttpClientInterface $httpClient;

    public function __construct(Config $config, ?HttpClientInterface $httpClient = null)
    {
        $this->config = $config;
        $this->defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->config->getApiKey(),
            'Accept' => 'application/json',
            'User-Agent' => sprintf('Perfbase-PHP-SDK/%s', Perfbase::VERSION),
            'Connection' => 'keep-alive',
        ];

        if ($httpClient !== null) {
            $this->httpClient = $httpClient;
        } else {
            $this->httpClient = new CurlHttpClient(
                $config->getApiUrl(),
                $config->getTimeout(),
                $config->getProxy()
            );
        }
    }

    /**
     * Submits a trace to the Perfbase API.
     *
     * @param string $perfData Raw trace payload (Brotli-compressed MessagePack bytes)
     * @param string $extensionVersion Extension release version (e.g. `0.1.0`). Sent as `X-Perfbase-Version`.
     * @param int $wireVersion Wire/encoding format version. Sent as `X-Perfbase-Protocol`.
     * @param string|null $clientCreatedAt Trace creation timestamp in ISO 8601 UTC
     * @return SubmitResult
     */
    public function submitTrace(string $perfData, string $extensionVersion, int $wireVersion, ?string $clientCreatedAt = null): SubmitResult
    {
        return $this->submit('/v1/submit', $perfData, $extensionVersion, $wireVersion, $clientCreatedAt);
    }

    /**
     * Sends a POST request to the specified API endpoint.
     *
     * @param string $endpoint API endpoint to send the request to
     * @param string $perfData Data to send in the request body
     * @param string $extensionVersion Extension release version
     * @param int $wireVersion Wire/encoding format version
     * @param string|null $clientCreatedAt Trace creation timestamp in ISO 8601 UTC
     * @return SubmitResult
     */
    private function submit(string $endpoint, string $perfData, string $extensionVersion, int $wireVersion, ?string $clientCreatedAt = null): SubmitResult
    {
        $headers = $this->defaultHeaders;
        $headers['Content-Type'] = 'application/octet-stream';
        $headers['X-Perfbase-Version'] = $extensionVersion;
        $headers['X-Perfbase-Protocol'] = (string) $wireVersion;

        if ($clientCreatedAt !== null) {
            $headers['X-Perfbase-Client-Created-At'] = $clientCreatedAt;
        }

        $options = [
            'headers' => $headers,
            'body' => $perfData,
        ];

        return $this->httpClient->post($endpoint, $options);
    }
}
