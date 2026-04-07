<?php

namespace Perfbase\SDK\Tests;

use Perfbase\SDK\Exception\PerfbaseException;
use Perfbase\SDK\TracePayloadFactory;

/**
 * @coversDefaultClass \Perfbase\SDK\TracePayloadFactory
 */
class TracePayloadFactoryTest extends BaseTest
{
    /**
     * @covers ::build
     */
    public function testBuildInjectsTimestamp(): void
    {
        $input = json_encode(['v' => 1, 'p' => 'dGVzdA==']);

        $output = TracePayloadFactory::build($input);
        $decoded = json_decode($output, true);

        $this->assertSame(1, $decoded['v']);
        $this->assertSame('dGVzdA==', $decoded['p']);
        $this->assertArrayHasKey('d', $decoded);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $decoded['d']);
    }

    /**
     * @covers ::build
     */
    public function testBuildPreservesExistingFields(): void
    {
        $input = json_encode(['v' => 1, 'p' => 'abc123']);

        $decoded = json_decode(TracePayloadFactory::build($input), true);

        $this->assertSame(1, $decoded['v']);
        $this->assertSame('abc123', $decoded['p']);
    }

    /**
     * @covers ::build
     */
    public function testBuildThrowsOnEmptyInput(): void
    {
        $this->expectException(PerfbaseException::class);
        $this->expectExceptionMessage('empty trace data');

        TracePayloadFactory::build('');
    }

    /**
     * @covers ::build
     */
    public function testBuildThrowsOnInvalidJson(): void
    {
        $this->expectException(PerfbaseException::class);
        $this->expectExceptionMessage('invalid JSON');

        TracePayloadFactory::build('not json');
    }

    /**
     * @covers ::build
     */
    public function testBuildThrowsOnMissingVersion(): void
    {
        $this->expectException(PerfbaseException::class);
        $this->expectExceptionMessage('version field');

        TracePayloadFactory::build(json_encode(['p' => 'abc']));
    }

    /**
     * @covers ::build
     */
    public function testBuildThrowsOnMissingPayload(): void
    {
        $this->expectException(PerfbaseException::class);
        $this->expectExceptionMessage('payload field');

        TracePayloadFactory::build(json_encode(['v' => 1]));
    }

    /**
     * @covers ::build
     */
    public function testBuildOutputIsValidJson(): void
    {
        $input = json_encode(['v' => 1, 'p' => 'dGVzdA==']);
        $output = TracePayloadFactory::build($input);

        $this->assertJson($output);
    }
}
