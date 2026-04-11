<?php

namespace Perfbase\SDK\Utils;

class EnvironmentUtils
{
    /**
     * Attempts to determine the real client IP address by checking common headers.
     * @return string The resolved IP address or "0.0.0.0" as a fallback.
     */
    public static function getUserIp(): ?string
    {
        // Common headers set by proxies/CDNs.
        $headersToCheck = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_FASTLY_CLIENT_IP',
            'HTTP_TRUE_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR', // If multiple IPs, the first valid one is usually the actual client.
            'HTTP_X_REAL_IP',
        ];

        // Check each header in order of priority.
        foreach ($headersToCheck as $header) {
            if (!empty($_SERVER[$header]) && is_string($_SERVER[$header])) {
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    // "X-Forwarded-For" can be a comma-separated list of IPs.
                    $candidates = array_map('trim', explode(',', $_SERVER[$header]));

                    foreach ($candidates as $candidate) {
                        if (filter_var($candidate, FILTER_VALIDATE_IP) !== false) {
                            return $candidate;
                        }
                    }

                    continue;
                }

                if (filter_var($_SERVER[$header], FILTER_VALIDATE_IP) !== false) {
                    return $_SERVER[$header];
                }
            }
        }

        // Fallback to REMOTE_ADDR if none of the above headers are set.
        if (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) {
            if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) !== false) {
                return $_SERVER['REMOTE_ADDR'];
            }
        }

        // No IP address found
        return null;
    }

    /**
     * Attempts to get the user agent of the client.
     * @return string|null
     */
    public static function getUserUserAgent(): ?string
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        // No user agent found
        return null;
    }

}
