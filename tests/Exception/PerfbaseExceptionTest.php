<?php

namespace Perfbase\SDK\Tests\Exception;

use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\Exception\PerfbaseExtensionException;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Exception\PerfbaseInvalidSpanException;
use Perfbase\SDK\Tests\BaseTest;

/**
 * @coversDefaultClass \Perfbase\SDK\Exception\PerfbaseException
 */
class PerfbaseExceptionTest extends BaseTest
{
    /**
     * @covers ::__construct
     */
    public function testPerfbaseExceptionPreservesCodeAndPreviousException(): void
    {
        $previous = new \RuntimeException('previous');
        $exception = new PerfbaseException('message', 42, $previous);

        $this->assertSame('message', $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * @covers \Perfbase\SDK\Exception\PerfbaseExtensionException::__construct
     * @covers \Perfbase\SDK\Exception\PerfbaseInvalidConfigException::__construct
     * @covers \Perfbase\SDK\Exception\PerfbaseInvalidSpanException::__construct
     */
    public function testSpecializedExceptionsInheritPerfbaseExceptionBehavior(): void
    {
        $previous = new \RuntimeException('previous');

        $extensionException = new PerfbaseExtensionException('extension', 10, $previous);
        $configException = new PerfbaseInvalidConfigException('config', 11, $previous);
        $spanException = new PerfbaseInvalidSpanException('span', 12, $previous);

        $this->assertInstanceOf(PerfbaseException::class, $extensionException);
        $this->assertInstanceOf(PerfbaseException::class, $configException);
        $this->assertInstanceOf(PerfbaseException::class, $spanException);

        $this->assertSame(10, $extensionException->getCode());
        $this->assertSame(11, $configException->getCode());
        $this->assertSame(12, $spanException->getCode());
        $this->assertSame($previous, $extensionException->getPrevious());
        $this->assertSame($previous, $configException->getPrevious());
        $this->assertSame($previous, $spanException->getPrevious());
    }
}
