<?php

namespace Perfbase\SDK\Utils;

/**
 * Removes credentials from URLs before they are attached to a trace.
 *
 * Query strings are kept (`/ajax.php?method=example` is what makes a trace
 * useful), but the value of any parameter whose name looks like a credential
 * is replaced with `REDACTED`. User info (`user:pass@`) and the fragment are
 * dropped. Everything else, including parameter order and encoding, is left
 * untouched.
 *
 * Parameter names are split into words on `_`, `-`, `.`, `[`, `]`, spaces and
 * camelCase boundaries, so `access_token`, `user[password]` and `apiKey` all
 * match while `author` or `compass` do not.
 */
class UrlSanitizer
{
    public const REDACTED = 'REDACTED';

    /**
     * A parameter is sensitive when any word of its name is one of these.
     */
    private const SENSITIVE_WORDS = [
        'auth',
        'authorization',
        'bearer',
        'credential',
        'credentials',
        'email',
        'jwt',
        'nonce',
        'otp',
        'pass',
        'passcode',
        'passphrase',
        'passwd',
        'password',
        'phpsessid',
        'pwd',
        'secret',
        'session',
        'sessionid',
        'sessid',
        'sig',
        'signature',
        'token',
    ];

    /**
     * Adjacent word pairs that name a credential (`api_key`, `X-Access-Key`).
     */
    private const SENSITIVE_WORD_PAIRS = [
        ['api', 'key'],
        ['access', 'key'],
        ['private', 'key'],
        ['secret', 'key'],
        ['client', 'secret'],
    ];

    /**
     * Matched anywhere in the name with separators removed, to catch compounds
     * written as one word (`accesstoken`, `xapikey`). Only long, unambiguous
     * terms belong here.
     */
    private const SENSITIVE_SUBSTRINGS = [
        'accesskey',
        'apikey',
        'credential',
        'nonce',
        'passwd',
        'password',
        'privatekey',
        'secret',
        'sessionid',
        'signature',
        'token',
    ];

    /**
     * @param string $url An absolute or relative URL.
     * @param array<string> $additionalSensitiveNames Extra parameter names (matched as whole words, case-insensitive).
     * @return string
     */
    public static function sanitize(string $url, array $additionalSensitiveNames = []): string
    {
        if ($url === '') {
            return '';
        }

        // The fragment never reaches the server and may carry tokens (OAuth implicit flow).
        $hashPosition = strpos($url, '#');
        if ($hashPosition !== false) {
            $url = self::slice($url, 0, $hashPosition);
        }

        $queryPosition = strpos($url, '?');
        $base = $queryPosition === false ? $url : self::slice($url, 0, $queryPosition);
        $query = $queryPosition === false ? null : self::slice($url, $queryPosition + 1);

        $base = self::stripUserInfo($base);

        if ($query === null) {
            return $base;
        }

        if ($query === '') {
            return $base . '?';
        }

        $extraWords = [];
        foreach ($additionalSensitiveNames as $name) {
            $extraWords[] = strtolower($name);
        }

        $pairs = explode('&', $query);
        foreach ($pairs as $index => $pair) {
            $equalsPosition = strpos($pair, '=');
            $rawName = $equalsPosition === false ? $pair : self::slice($pair, 0, $equalsPosition);

            if (!self::isSensitiveName(rawurldecode(str_replace('+', ' ', $rawName)), $extraWords)) {
                continue;
            }

            $pairs[$index] = $rawName . '=' . self::REDACTED;
        }

        return $base . '?' . implode('&', $pairs);
    }

    /**
     * Whether a query parameter name looks like it carries a credential.
     *
     * @param string $name Decoded parameter name.
     * @param array<string> $extraWords Lowercase extra names.
     */
    public static function isSensitiveName(string $name, array $extraWords = []): bool
    {
        $words = self::splitWords($name);
        if ($words === []) {
            return false;
        }

        $lowerName = strtolower($name);
        foreach ($extraWords as $extraWord) {
            if ($extraWord !== '' && ($lowerName === $extraWord || in_array($extraWord, $words, true))) {
                return true;
            }
        }

        foreach ($words as $word) {
            if (in_array($word, self::SENSITIVE_WORDS, true)) {
                return true;
            }
        }

        $wordCount = count($words);
        for ($i = 0; $i < $wordCount - 1; $i++) {
            foreach (self::SENSITIVE_WORD_PAIRS as $pair) {
                if ($words[$i] === $pair[0] && $words[$i + 1] === $pair[1]) {
                    return true;
                }
            }
        }

        $compact = implode('', $words);
        foreach (self::SENSITIVE_SUBSTRINGS as $substring) {
            if (strpos($compact, $substring) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string> Lowercase words of a parameter name.
     */
    private static function splitWords(string $name): array
    {
        // camelCase / PascalCase boundaries: apiKey -> api Key, XMLToken -> XML Token
        $spaced = preg_replace(['/([a-z0-9])([A-Z])/', '/([A-Z]+)([A-Z][a-z])/'], '$1 $2', $name);
        if (!is_string($spaced)) {
            $spaced = $name;
        }

        $parts = preg_split('/[^A-Za-z0-9]+/', strtolower($spaced), -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return [];
        }

        $words = [];
        foreach ($parts as $part) {
            if (is_string($part)) {
                $words[] = $part;
            }
        }

        return $words;
    }

    /**
     * Drop `user:pass@` from `scheme://user:pass@host/...`.
     */
    private static function stripUserInfo(string $base): string
    {
        $schemePosition = strpos($base, '://');
        if ($schemePosition === false) {
            return $base;
        }

        $authorityStart = $schemePosition + 3;
        $pathPosition = strpos($base, '/', $authorityStart);
        $authority = $pathPosition === false ? self::slice($base, $authorityStart) : self::slice($base, $authorityStart, $pathPosition - $authorityStart);

        $atPosition = strrpos($authority, '@');
        if ($atPosition === false) {
            return $base;
        }

        $rest = $pathPosition === false ? '' : self::slice($base, $pathPosition);

        return self::slice($base, 0, $authorityStart) . self::slice($authority, $atPosition + 1) . $rest;
    }

    /**
     * substr() that always returns a string. On PHP 7.4 substr() returns
     * false instead of '' for an empty slice.
     */
    private static function slice(string $value, int $start, ?int $length = null): string
    {
        // (string) maps PHP 7.4's false to ''; it is a no-op on PHP 8.
        return (string) ($length === null ? substr($value, $start) : substr($value, $start, $length));
    }
}
