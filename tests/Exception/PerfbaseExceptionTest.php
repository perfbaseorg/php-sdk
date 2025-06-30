<?php

namespace Perfbase\SDK\Tests\Exception;

use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\Exception\PerfbaseExtensionException;
use Perfbase\SDK\Exception\PerfbaseInvalidConfigException;
use Perfbase\SDK\Exception\PerfbaseInvalidSpanException;
use Perfbase\SDK\Tests\BaseTest;

/**
 * Test all Perfbase exceptions
 */
class PerfbaseExceptionTest extends BaseTest
{
    /**
     * @covers \Perfbase\SDK\Exception\PerfbaseException::__construct
     */
    public function testPerfbaseExceptionWithMessage(): void
    {
        $message = 'Test exception message';
        $exception = new PerfbaseException($message);
        
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * @covers \Perfbase\SDK\Exception\PerfbaseException::__construct
     */
    public function testPerfbaseExceptionWithEmptyMessage(): void
    {
        $exception = new PerfbaseException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('', $exception->getMessage());
    }

    /**
     * Test PerfbaseExtensionException
     * @covers \Perfbase\SDK\Exception\PerfbaseExtensionException::__construct
     */
    public function testPerfbaseExtensionException(): void
    {
        $message = 'Extension not loaded';
        $exception = new PerfbaseExtensionException($message);
        
        $this->assertInstanceOf(PerfbaseException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * Test PerfbaseInvalidConfigException
     * @covers \Perfbase\SDK\Exception\PerfbaseInvalidConfigException::__construct
     */
    public function testPerfbaseInvalidConfigException(): void
    {
        $message = 'Invalid configuration';
        $exception = new PerfbaseInvalidConfigException($message);
        
        $this->assertInstanceOf(PerfbaseException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * Test PerfbaseInvalidSpanException
     * @covers \Perfbase\SDK\Exception\PerfbaseInvalidSpanException::__construct
     */
    public function testPerfbaseInvalidSpanException(): void
    {
        $message = 'Invalid span name';
        $exception = new PerfbaseInvalidSpanException($message);
        
        $this->assertInstanceOf(PerfbaseException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * Test exception inheritance hierarchy
     * @covers \Perfbase\SDK\Exception\PerfbaseException
     */
    public function testExceptionInheritanceHierarchy(): void
    {
        $baseException = new PerfbaseException('Base');
        $extensionException = new PerfbaseExtensionException('Extension');
        $configException = new PerfbaseInvalidConfigException('Config');
        $spanException = new PerfbaseInvalidSpanException('Span');
        
        // All should inherit from PerfbaseException
        $this->assertInstanceOf(PerfbaseException::class, $extensionException);
        $this->assertInstanceOf(PerfbaseException::class, $configException);
        $this->assertInstanceOf(PerfbaseException::class, $spanException);
        
        // All should inherit from base Exception
        $this->assertInstanceOf(\Exception::class, $baseException);
        $this->assertInstanceOf(\Exception::class, $extensionException);
        $this->assertInstanceOf(\Exception::class, $configException);
        $this->assertInstanceOf(\Exception::class, $spanException);
    }

    /**
     * Test that exceptions can be caught by their parent type
     * @covers \Perfbase\SDK\Exception\PerfbaseException
     */
    public function testExceptionCanBeCaughtByParentType(): void
    {
        $caughtAsPerfbaseException = false;
        $caughtAsBaseException = false;
        
        try {
            throw new PerfbaseInvalidSpanException('Test span error');
        } catch (PerfbaseException $e) {
            $caughtAsPerfbaseException = true;
            $this->assertEquals('Test span error', $e->getMessage());
        }
        
        try {
            throw new PerfbaseExtensionException('Test extension error');
        } catch (\Exception $e) {
            $caughtAsBaseException = true;
            $this->assertEquals('Test extension error', $e->getMessage());
        }
        
        $this->assertTrue($caughtAsPerfbaseException);
        $this->assertTrue($caughtAsBaseException);
    }
}