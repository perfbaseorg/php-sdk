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
            'CF-Connecting-IP',
            'Fastly-Client-IP',
            'True-Client-IP',
            'X-Forwarded-For', // If multiple IPs, the first one is usually the actual client.
            'X-Real-IP',
        ];

        // Check each header in order of priority.
        foreach ($headersToCheck as $header) {
            if (!empty($_SERVER[$header]) && is_string($_SERVER[$header])) {
                if ($header === 'X-Forwarded-For') {
                    // "X-Forwarded-For" can be a comma-separated list of IPs.
                    return trim(explode(',', $_SERVER[$header])[0]);
                }

                return $_SERVER[$header];
            }
        }

        // Fallback to REMOTE_ADDR if none of the above headers are set.
        if (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
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

    /**
     * Attempts to get the hostname of the server.
     * @return string|null
     */
    public static function getHostname(): ?string
    {
        return gethostname() ?: null;
    }
}