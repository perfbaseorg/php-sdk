<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\SubmitResult;

/**
 * @coversDefaultClass \Perfbase\SDK\SubmitResult
 */
class SubmitResultTest extends BaseTest
{
    /**
     * @covers ::success
     * @covers ::isSuccess
     * @covers ::getStatus
     * @covers ::getStatusCode
     * @covers ::getMessage
     */
    public function testSuccessResult(): void
    {
        $result = SubmitResult::success(202, 'Accepted');

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isRetryable());
        $this->assertFalse($result->isPermanentFailure());
        $this->assertSame(SubmitResult::STATUS_SUCCESS, $result->getStatus());
        $this->assertSame(202, $result->getStatusCode());
        $this->assertSame('Accepted', $result->getMessage());
    }

    /**
     * @covers ::success
     */
    public function testSuccessDefaultValues(): void
    {
        $result = SubmitResult::success();

        $this->assertTrue($result->isSuccess());
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('', $result->getMessage());
    }

    /**
     * @covers ::retryableFailure
     * @covers ::isRetryable
     */
    public function testRetryableFailure(): void
    {
        $result = SubmitResult::retryableFailure(503, 'Service Unavailable');

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isRetryable());
        $this->assertFalse($result->isPermanentFailure());
        $this->assertSame(503, $result->getStatusCode());
        $this->assertSame('Service Unavailable', $result->getMessage());
    }

    /**
     * @covers ::retryableFailure
     */
    public function testRetryableFailureWithNullStatusCode(): void
    {
        $result = SubmitResult::retryableFailure(null, 'Connection refused');

        $this->assertTrue($result->isRetryable());
        $this->assertNull($result->getStatusCode());
    }

    /**
     * @covers ::permanentFailure
     * @covers ::isPermanentFailure
     */
    public function testPermanentFailure(): void
    {
        $result = SubmitResult::permanentFailure(401, 'Unauthorized');

        $this->assertFalse($result->isSuccess());
        $this->assertFalse($result->isRetryable());
        $this->assertTrue($result->isPermanentFailure());
        $this->assertSame(401, $result->getStatusCode());
        $this->assertSame('Unauthorized', $result->getMessage());
    }
}
