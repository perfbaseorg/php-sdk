<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\Utils\EnvironmentUtils;

/**
 * @coversDefaultClass \Perfbase\SDK\Utils\EnvironmentUtils
 */
class EnvironmentUtilsTest extends BaseTest
{
    protected function tearDown(): void
    {
        // Restore $_SERVER array
        $_SERVER = [];
    }

    /**
     * @covers ::getUserIp
     * @return void
     */
    public function testGetUserIpUtilisesCFConnectingIP(): void
    {
        $_SERVER['CF-Connecting-IP'] = '0.0.0.0';
        $_SERVER['Fastly-Client-IP'] = '1.1.1.1';
        $_SERVER['True-Client-IP'] = '2.2.2.2';
        $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
        $_SERVER['X-Real-IP'] = '5.5.5.5';
        $_SERVER['REMOTE_ADDR'] = '6.6.6.6';

        $this->assertSame('0.0.0.0', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     * @return void
     */
    public function testGetUserIpUtilisesFastlyClientIP(): void
    {
        $_SERVER['Fastly-Client-IP'] = '1.1.1.1';
        $_SERVER['True-Client-IP'] = '2.2.2.2';
        $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
        $_SERVER['X-Real-IP'] = '5.5.5.5';
        $_SERVER['REMOTE_ADDR'] = '6.6.6.6';

        $this->assertSame('1.1.1.1', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     * @return void
     */
    public function testGetUserIpUtilisesTrueClientIP(): void
    {
        $_SERVER['True-Client-IP'] = '2.2.2.2';
        $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
        $_SERVER['X-Real-IP'] = '5.5.5.5';
        $_SERVER['REMOTE_ADDR'] = '6.6.6.6';

        $this->assertSame('2.2.2.2', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     * @return void
     */
    public function testGetUserIpUtilisesXForwardedFor(): void
    {
        $_SERVER['X-Forwarded-For'] = '3.3.3.3, 4.4.4.4';
        $_SERVER['X-Real-IP'] = '5.5.5.5';
        $_SERVER['REMOTE_ADDR'] = '6.6.6.6';

        $this->assertSame('3.3.3.3', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     * @return void
     */
    public function testGetUserIpUtilisesXRealIP(): void
    {
        $_SERVER['X-Real-IP'] = '5.5.5.5';
        $_SERVER['REMOTE_ADDR'] = '6.6.6.6';

        $this->assertSame('5.5.5.5', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     * @return void
     */
    public function testGetUserIpFallsBackToRemoteAddr(): void
    {
        $_SERVER['REMOTE_ADDR'] = '6.6.6.6';

        $this->assertSame('6.6.6.6', EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserIp
     * @return void
     */
    public function testGetUserIpReturnsNullIfNoIPIsFound(): void
    {
        $_SERVER = []; // Simulate no server data

        $this->assertNull(EnvironmentUtils::getUserIp());
    }

    /**
     * @covers ::getUserUserAgent
     * @return void
     */
    public function testGetUserUserAgentReturnsUserAgentIfPresent(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

        $this->assertSame('Mozilla/5.0', EnvironmentUtils::getUserUserAgent());
    }

    /**
     * @covers ::getUserUserAgent
     * @return void
     */
    public function testGetUserUserAgentReturnsNullIfNoUserAgentIsPresent(): void
    {
        $_SERVER = []; // Simulate no server data

        $this->assertNull(EnvironmentUtils::getUserUserAgent());
    }

    /**
     * @covers ::getHostname
     * @return void
     */
    public function testGetHostnameReturnsServerHostname(): void
    {
        $hostname = gethostname();

        $this->assertSame($hostname, EnvironmentUtils::getHostname());
    }
}
