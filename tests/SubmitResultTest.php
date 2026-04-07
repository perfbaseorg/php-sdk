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

    /**
     * @covers ::retryableFailure
     */
    public function testRetryableFailureDefaultValues(): void
    {
        $result = SubmitResult::retryableFailure();

        $this->assertTrue($result->isRetryable());
        $this->assertNull($result->getStatusCode());
        $this->assertSame('', $result->getMessage());
    }

    /**
     * @covers ::permanentFailure
     */
    public function testPermanentFailureDefaultValues(): void
    {
        $result = SubmitResult::permanentFailure();

        $this->assertTrue($result->isPermanentFailure());
        $this->assertNull($result->getStatusCode());
        $this->assertSame('', $result->getMessage());
    }

    /**
     * @covers ::isSuccess
     * @covers ::isRetryable
     * @covers ::isPermanentFailure
     */
    public function testStatusMethodsMutuallyExclusive(): void
    {
        $success = SubmitResult::success();
        $this->assertTrue($success->isSuccess());
        $this->assertFalse($success->isRetryable());
        $this->assertFalse($success->isPermanentFailure());

        $retryable = SubmitResult::retryableFailure();
        $this->assertFalse($retryable->isSuccess());
        $this->assertTrue($retryable->isRetryable());
        $this->assertFalse($retryable->isPermanentFailure());

        $permanent = SubmitResult::permanentFailure();
        $this->assertFalse($permanent->isSuccess());
        $this->assertFalse($permanent->isRetryable());
        $this->assertTrue($permanent->isPermanentFailure());
    }

    /**
     * @covers ::getStatus
     */
    public function testGetStatusReturnsCorrectConstants(): void
    {
        $this->assertSame(SubmitResult::STATUS_SUCCESS, SubmitResult::success()->getStatus());
        $this->assertSame(SubmitResult::STATUS_RETRYABLE_FAILURE, SubmitResult::retryableFailure()->getStatus());
        $this->assertSame(SubmitResult::STATUS_PERMANENT_FAILURE, SubmitResult::permanentFailure()->getStatus());
    }
}
