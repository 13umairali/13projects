<?php

namespace App\Services\Setup;

/**
 * Outcome of a single probe against a candidate backend URL.
 *
 * Immutable value object: the tester returns one of these instead of throwing,
 * because "the backend is unreachable" is an expected answer during setup, not
 * an exceptional condition.
 */
final readonly class ConnectionResult
{
    /**
     * @param  bool  $ok  Whether the URL is a usable Mighty School backend.
     * @param  string  $message  Operator-facing summary. Never contains a stack trace.
     * @param  string|null  $hint  One concrete next action, when we can infer one.
     * @param  int|null  $status  HTTP status observed, null if we never got a response.
     * @param  int|null  $latencyMs  Round-trip time, for display on success.
     */
    public function __construct(
        public bool $ok,
        public string $message,
        public ?string $hint = null,
        public ?int $status = null,
        public ?int $latencyMs = null,
    ) {}

    public static function success(string $message, ?int $status = null, ?int $latencyMs = null): self
    {
        return new self(true, $message, null, $status, $latencyMs);
    }

    public static function failure(string $message, ?string $hint = null, ?int $status = null): self
    {
        return new self(false, $message, $hint, $status);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'ok' => $this->ok,
            'message' => $this->message,
            'hint' => $this->hint,
            'status' => $this->status,
            'latency_ms' => $this->latencyMs,
        ], fn ($value) => $value !== null);
    }
}
