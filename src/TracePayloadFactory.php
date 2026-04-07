<?php

namespace Perfbase\SDK;

use Perfbase\SDK\Exception\PerfbaseException;

/**
 * Builds the submission payload from extension output.
 *
 * The extension returns JSON like {"v":1,"p":"<base64>"}. This factory
 * owns the outer envelope — it validates structure, injects the client
 * timestamp, and produces the final JSON body for the receiver.
 */
class TracePayloadFactory
{
    /**
     * Build a submission payload from raw extension output.
     *
     * @param string $extensionOutput Raw JSON from perfbase_get_data()
     * @return string JSON payload ready for HTTP submission
     * @throws PerfbaseException If the extension output is malformed
     */
    public static function build(string $extensionOutput): string
    {
        if ($extensionOutput === '') {
            throw new PerfbaseException('Extension returned empty trace data');
        }

        /** @var mixed $decoded */
        $decoded = json_decode($extensionOutput, true);

        if (!is_array($decoded)) {
            throw new PerfbaseException('Extension returned invalid JSON');
        }

        if (!isset($decoded['v']) || !is_int($decoded['v'])) {
            throw new PerfbaseException('Extension output missing version field (v)');
        }

        if (!isset($decoded['p']) || !is_string($decoded['p'])) {
            throw new PerfbaseException('Extension output missing payload field (p)');
        }

        // Inject client timestamp so the receiver knows when the trace was created
        $decoded['d'] = gmdate('Y-m-d\TH:i:s\Z');

        $json = json_encode($decoded);
        if ($json === false) {
            throw new PerfbaseException('Failed to encode submission payload');
        }

        return $json;
    }
}
