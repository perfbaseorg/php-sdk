<?php

namespace Perfbase\SDK;

/**
 * Represents the outcome of a trace submission attempt.
 *
 * Callers should check the status to decide whether to retry, reset, or surface an error.
 */
class SubmitResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_RETRYABLE_FAILURE = 'retryable_failure';
    public const STATUS_PERMANENT_FAILURE = 'permanent_failure';

    /** @var string One of the STATUS_* constants */
    private string $status;

    /** @var int|null HTTP status code, if a response was received */
    private ?int $statusCode;

    /** @var string Human-readable description of what happened */
    private string $message;

    private function __construct(string $status, ?int $statusCode, string $message)
    {
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->message = $message;
    }

    public static function success(?int $statusCode = 200, string $message = ''): self
    {
        return new self(self::STATUS_SUCCESS, $statusCode, $message);
    }

    public static function retryableFailure(?int $statusCode = null, string $message = ''): self
    {
        return new self(self::STATUS_RETRYABLE_FAILURE, $statusCode, $message);
    }

    public static function permanentFailure(?int $statusCode = null, string $message = ''): self
    {
        return new self(self::STATUS_PERMANENT_FAILURE, $statusCode, $message);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isRetryable(): bool
    {
        return $this->status === self::STATUS_RETRYABLE_FAILURE;
    }

    public function isPermanentFailure(): bool
    {
        return $this->status === self::STATUS_PERMANENT_FAILURE;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
