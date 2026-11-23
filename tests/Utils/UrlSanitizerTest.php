<?php

namespace Perfbase\SDK\Tests\Utils;

use Perfbase\SDK\Tests\BaseTest;
use Perfbase\SDK\Utils\UrlSanitizer;

/**
 * @coversDefaultClass \Perfbase\SDK\Utils\UrlSanitizer
 */
class UrlSanitizerTest extends BaseTest
{
    /**
     * @covers ::sanitize
     */
    public function testKeepsOrdinaryQueryParameters(): void
    {
        $url = 'https://example.com/ajax.php?method=example&page=2&sort=desc';

        $this->assertSame($url, UrlSanitizer::sanitize($url));
    }

    /**
     * @covers ::sanitize
     */
    public function testRedactsCredentialParametersAndKeepsTheRest(): void
    {
        $this->assertSame(
            'https://example.com/ajax.php?method=example&token=REDACTED&page=2',
            UrlSanitizer::sanitize('https://example.com/ajax.php?method=example&token=abc123&page=2')
        );
    }

    /**
     * @covers ::sanitize
     * @covers ::isSensitiveName
     * @dataProvider sensitiveNameProvider
     */
    public function testRedactsSensitiveNames(string $name): void
    {
        $this->assertSame(
            '/x?' . $name . '=REDACTED&keep=1',
            UrlSanitizer::sanitize('/x?' . $name . '=s3cr3t&keep=1'),
            "$name should be redacted"
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public function sensitiveNameProvider(): array
    {
        $names = [
            'token', 'access_token', 'refresh_token', 'id_token', 'accessToken', 'X-Auth-Token', 'csrf_token',
            'password', 'user[password]', 'new_password', 'passwd', 'pwd', 'pass', 'passphrase',
            'secret', 'client_secret', 'clientSecret', 'api_key', 'apiKey', 'apikey', 'x-api-key', 'xapikey',
            'access_key', 'AWSAccessKeyId', 'private_key', 'signature', 'X-Amz-Signature', 'sig',
            'auth', 'authorization', 'auth_code', 'jwt', 'session', 'session_id', 'PHPSESSID', 'sessid',
            'credentials', 'otp', 'bearer', 'ACCESSTOKEN', '_wpnonce', 'wpnonce', 'nonce', 'email', 'user_email',
        ];

        $cases = [];
        foreach ($names as $name) {
            $cases[$name] = [$name];
        }

        return $cases;
    }

    /**
     * @covers ::sanitize
     * @covers ::isSensitiveName
     * @dataProvider safeNameProvider
     */
    public function testLeavesLookalikeNamesAlone(string $name): void
    {
        $url = '/x?' . $name . '=value';

        $this->assertSame($url, UrlSanitizer::sanitize($url), "$name should not be redacted");
    }

    /**
     * @return array<string, array{string}>
     */
    public function safeNameProvider(): array
    {
        $names = [
            'author', 'authority', 'compass', 'bypass', 'passage', 'method', 'action', 'page', 'key',
            'monkey', 'design', 'signal', 'p', 'q', 'rest_route', 'doing_wp_cron',
        ];

        $cases = [];
        foreach ($names as $name) {
            $cases[$name] = [$name];
        }

        return $cases;
    }

    /**
     * @covers ::sanitize
     */
    public function testHandlesEncodedAndValuelessParameters(): void
    {
        $this->assertSame(
            '/x?user%5Bpassword%5D=REDACTED&flag&api+key=REDACTED',
            UrlSanitizer::sanitize('/x?user%5Bpassword%5D=hunter2&flag&api+key=abc')
        );
        $this->assertSame('/x?token=REDACTED', UrlSanitizer::sanitize('/x?token'));
        $this->assertSame('/x?', UrlSanitizer::sanitize('/x?'));
    }

    /**
     * @covers ::sanitize
     */
    public function testStripsUserInfoAndFragment(): void
    {
        $this->assertSame(
            'https://api.example.com:8443/v1/items?method=list',
            UrlSanitizer::sanitize('https://admin:hunter2@api.example.com:8443/v1/items?method=list#access_token=abc')
        );
        $this->assertSame('https://example.com', UrlSanitizer::sanitize('https://user@example.com'));
    }

    /**
     * @covers ::sanitize
     */
    public function testUrlsWithoutQueryAreUnchanged(): void
    {
        $this->assertSame('https://example.com/users/42', UrlSanitizer::sanitize('https://example.com/users/42'));
        $this->assertSame('', UrlSanitizer::sanitize(''));
    }

    /**
     * @covers ::sanitize
     * @covers ::isSensitiveName
     */
    public function testAdditionalSensitiveNames(): void
    {
        $this->assertSame(
            '/x?coupon=REDACTED&order_ref=REDACTED&page=1',
            UrlSanitizer::sanitize('/x?coupon=SUMMER&order_ref=123&page=1', ['coupon', 'Order_Ref'])
        );
    }
}
