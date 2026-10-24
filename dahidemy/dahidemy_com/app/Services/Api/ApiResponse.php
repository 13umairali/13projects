<?php

namespace App\Services\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Consistent, framework-agnostic value object wrapping every backend API call.
 *
 * The Mighty School backend answers with the envelope:
 *   { "status": bool, "message": string, "data": mixed, "errors": mixed }
 *
 * This DTO normalises that (plus transport failures) so controllers and
 * services never touch raw HTTP responses.
 */
class ApiResponse implements Arrayable
{
    public function __construct(
        public readonly bool $ok,
        public readonly int $statusCode,
        public readonly string $message,
        public readonly mixed $data = null,
        public readonly mixed $errors = null,
    ) {}

    public static function fromEnvelope(int $statusCode, array $body): self
    {
        return new self(
            ok: (bool) ($body['status'] ?? ($statusCode >= 200 && $statusCode < 300)),
            statusCode: $statusCode,
            message: (string) ($body['message'] ?? ''),
            data: $body['data'] ?? null,
            errors: $body['errors'] ?? null,
        );
    }

    public static function failure(string $message, int $statusCode = 503, mixed $errors = null): self
    {
        return new self(false, $statusCode, $message, null, $errors);
    }

    /** Rehydrate a response previously stored by {@see toArray()} (the cache). */
    public static function fromArray(array $payload): self
    {
        return new self(
            ok: (bool) ($payload['ok'] ?? false),
            statusCode: (int) ($payload['status_code'] ?? 200),
            message: (string) ($payload['message'] ?? ''),
            data: $payload['data'] ?? null,
            errors: $payload['errors'] ?? null,
        );
    }

    public function successful(): bool
    {
        return $this->ok;
    }

    public function failed(): bool
    {
        return ! $this->ok;
    }

    /**
     * Get the payload, or a fallback when the call failed / returned nothing.
     */
    public function data(mixed $default = null): mixed
    {
        if ($this->failed() || $this->data === null) {
            return $default;
        }

        return $this->data;
    }

    /**
     * Dot-notation accessor into the data payload.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->data;

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return is_array($data) ? Arr::get($data, $key, $default) : $default;
    }

    /**
     * Always return the payload as a Collection (handy for list endpoints).
     */
    public function collect(): Collection
    {
        return collect($this->data ?? []);
    }

    /**
     * Flatten Laravel-style validation errors to "field => first message".
     */
    public function validationErrors(): array
    {
        if (! is_array($this->errors)) {
            return [];
        }

        return collect($this->errors)
            ->map(fn ($messages) => is_array($messages) ? Arr::first($messages) : $messages)
            ->all();
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'status_code' => $this->statusCode,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}
