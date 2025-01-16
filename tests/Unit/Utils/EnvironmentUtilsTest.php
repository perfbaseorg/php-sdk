<?php

use Perfbase\SDK\Utils\EnvironmentUtils;

afterEach(function () {
    // Restore $_SERVER array
    $_SERVER = [];
});

test('getUserIp utilises CF-Connecting-IP', function () {
    $_SERVER['CF-Connecting-IP'] = '0.0.0.0';
    $_SERVER['Fastly-Client-IP'] = '1.1.1.1';
    $_SERVER['True-Client-IP'] = '2.2.2.2';
    $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
    $_SERVER['X-Real-IP'] = '5.5.5.5';
    $_SERVER['REMOTE_ADDR'] = '6.6.6.6';
    expect(EnvironmentUtils::getUserIp())->toBe('0.0.0.0');
});

test('getUserIp utilises Fastly-Client-IP', function () {
    $_SERVER['Fastly-Client-IP'] = '1.1.1.1';
    $_SERVER['True-Client-IP'] = '2.2.2.2';
    $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
    $_SERVER['X-Real-IP'] = '5.5.5.5';
    $_SERVER['REMOTE_ADDR'] = '6.6.6.6';
    expect(EnvironmentUtils::getUserIp())->toBe('1.1.1.1');
});

test('getUserIp utilises True-Client-IP', function () {
    $_SERVER['True-Client-IP'] = '2.2.2.2';
    $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
    $_SERVER['X-Real-IP'] = '5.5.5.5';
    $_SERVER['REMOTE_ADDR'] = '6.6.6.6';
    expect(EnvironmentUtils::getUserIp())->toBe('2.2.2.2');
});

test('getUserIp utilises X-Forwarded-For', function () {
    $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
    $_SERVER['X-Real-IP'] = '5.5.5.5';
    $_SERVER['REMOTE_ADDR'] = '6.6.6.6';
    expect(EnvironmentUtils::getUserIp())->toBe('3.3.3.3');
});

test('getUserIp utilises X-Real-IP', function () {
    $_SERVER['X-Real-IP'] = '5.5.5.5';
    $_SERVER['REMOTE_ADDR'] = '6.6.6.6';
    expect(EnvironmentUtils::getUserIp())->toBe('5.5.5.5');
});

test('getUserIp falls back to REMOTE_ADDR', function () {
    $_SERVER['REMOTE_ADDR'] = '6.6.6.6';
    expect(EnvironmentUtils::getUserIp())->toBe('6.6.6.6');
});

test('getUserIp returns null if no IP is found', function () {
    $_SERVER = []; // Simulate no server data
    expect(EnvironmentUtils::getUserIp())->toBeNull();
});

test('getUserUserAgent returns the user agent if present', function () {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
    expect(EnvironmentUtils::getUserUserAgent())->toBe('Mozilla/5.0');
});

test('getUserUserAgent returns null if no user agent is present', function () {
    $_SERVER = []; // Simulate no server data
    expect(EnvironmentUtils::getUserUserAgent())->toBeNull();
});

test('getHostname returns the hostname of the server', function () {
    $hostname = gethostname();
    expect(EnvironmentUtils::getHostname())->toBe($hostname);
});