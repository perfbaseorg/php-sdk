<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\Utils\EnvironmentUtils;

/**
 * @coversDefaultClass \Perfbase\SDK\Utils\EnvironmentUtils
 */
class EnvironmentUtilsTest extends BaseTest
{
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    /**
     * @covers ::getUserIp
     */
    public function testGetUserIpUsesTrustedHeaderOrderWithStandardPhpKeys(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '1.1.1.1';
        $_SERVER['HTTP_FASTLY_CLIENT_IP'] = '2.2.2.2';
        $_SERVER['HTTP_TRUE_CLIENT_IP'] = '3.3.3.3';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '4.4.4.4, 5.5.5.5';
        $_SERVER['HTTP_X_REAL_IP'] = '6.6.6.6';
        $_SERVER['REMOTE_ADDR'] = '7.7.7.7';

        $this->assertSame('1.1.1.1', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     */
    public function testGetUserIpFallsThroughInvalidHeadersToNextValidCandidate(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = 'not-an-ip';
        $_SERVER['HTTP_FASTLY_CLIENT_IP'] = 'still-not-an-ip';
        $_SERVER['HTTP_TRUE_CLIENT_IP'] = '3.3.3.3';
        $_SERVER['REMOTE_ADDR'] = '7.7.7.7';

        $this->assertSame('3.3.3.3', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     */
    public function testGetUserIpReturnsFirstValidXForwardedForEntry(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'invalid-entry, 4.4.4.4, 5.5.5.5';
        $_SERVER['HTTP_X_REAL_IP'] = '6.6.6.6';

        $this->assertSame('4.4.4.4', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     */
    public function testGetUserIpFallsBackToRemoteAddrWhenForwardedHeadersInvalid(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'invalid-entry';
        $_SERVER['HTTP_X_REAL_IP'] = 'still-invalid';
        $_SERVER['REMOTE_ADDR'] = '7.7.7.7';

        $this->assertSame('7.7.7.7', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     */
    public function testGetUserIpReturnsNullWhenNoValidIpExists(): void
    {
        $_SERVER['HTTP_CF_CONNECTING_IP'] = 'invalid';
        $_SERVER['REMOTE_ADDR'] = 'also-invalid';

        $this->assertNull(EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserUserAgent
     */
    public function testGetUserUserAgentReturnsUserAgentIfPresent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

        $this->assertSame('Mozilla/5.0', EnvironmentUtils::getUserUserAgent());
    }

    /**
     * @covers ::getUserUserAgent
     */
    public function testGetUserUserAgentReturnsNullIfNoUserAgentIsPresent(): void
    {
        $_SERVER = [];

        $this->assertNull(EnvironmentUtils::getUserUserAgent());
    }
}
