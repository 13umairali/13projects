<?php

namespace App\Services\Setup;

use App\Services\Setup\Exceptions\EnvironmentWriteException;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Reads and rewrites the application's .env file.
 *
 * Deliberately knows nothing about the backend API or HTTP — its single
 * responsibility is safe key/value persistence in the env file. Probing lives
 * in {@see BackendConnectionTester}, orchestration in {@see SetupManager}.
 *
 * Rewrites are line-oriented rather than "parse to array and dump" so that
 * comments, blank lines, grouping and quoting style all survive untouched. A
 * dumped .env would strip the documentation this project keeps in .env.example.
 */
class EnvironmentService
{
    public function __construct(
        protected string $path
    ) {}

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return File::exists($this->path);
    }

    /**
     * Every key/value pair currently in the file.
     *
     * Comments and blank lines are skipped. Values are returned raw (quotes
     * stripped); this is a reader for display and assertions, not a resolver —
     * it does not expand "${VAR}" references the way Dotenv does.
     *
     * @return array<string, string>
     */
    public function readEnv(): array
    {
        $pairs = [];

        foreach ($this->split($this->read()) as $line) {
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (! str_contains($trimmed, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $trimmed, 2);

            $key = trim($key);

            if ($key === '') {
                continue;
            }

            $pairs[$key] = $this->unquote(trim($value));
        }

        return $pairs;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->readEnv()[$key] ?? $default;
    }

    /**
     * True when the key is present AND carries a non-empty value.
     *
     * "Present but blank" (FRONTEND_API_URL=) is how a fresh install ships, so
     * it must read as "not set" rather than "set to an empty string".
     */
    public function has(string $key): bool
    {
        return trim((string) $this->get($key, '')) !== '';
    }

    /**
     * Write the given pairs, updating keys that exist and appending those that
     * do not. Existing keys are rewritten in place, so a key can never end up
     * duplicated — a duplicate would silently win or lose depending on Dotenv's
     * parse order and produce a config that does not match the file.
     *
     * @param  array<string, string|int|bool|null>  $values
     *
     * @throws EnvironmentWriteException
     */
    public function updateEnv(array $values): void
    {
        if ($values === []) {
            return;
        }

        $this->guardWritable();

        $contents = $this->read();
        $lines = $this->split($contents);
        $pending = $values;

        foreach ($lines as $index => $line) {
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
                continue;
            }

            $key = trim(explode('=', $trimmed, 2)[0]);

            if (! array_key_exists($key, $pending)) {
                continue;
            }

            $lines[$index] = $key.'='.$this->format($pending[$key]);

            unset($pending[$key]);
        }

        foreach ($pending as $key => $value) {
            $lines[] = $key.'='.$this->format($value);
        }

        $this->put($lines, $this->detectEol($contents));
    }

    /* -----------------------------------------------------------------
     |  Internals
     | ----------------------------------------------------------------- */

    protected function read(): string
    {
        return $this->exists() ? File::get($this->path) : '';
    }

    /**
     * Split into lines with line endings stripped.
     *
     * CRLF is normalised away here and reapplied on write, so a \r can never be
     * carried into a value — Dotenv would treat it as part of the string, and a
     * URL ending in "\r" fails every request with an error that names a host
     * looking exactly like the one you typed.
     *
     * @return list<string>
     */
    protected function split(string $contents): array
    {
        if ($contents === '') {
            return [];
        }

        return explode("\n", str_replace(["\r\n", "\r"], "\n", $contents));
    }

    /**
     * Reuse whatever the file already uses, rather than PHP_EOL.
     *
     * PHP_EOL is the *host's* convention, not the file's: on a Windows dev box
     * it would rewrite all of a Unix .env to CRLF, turning a one-key change into
     * a whole-file diff. New files get LF, which is what these deployments run.
     */
    protected function detectEol(string $contents): string
    {
        return str_contains($contents, "\r\n") ? "\r\n" : "\n";
    }

    /**
     * Atomic write: render to a temp file in the same directory, then rename
     * over the original. rename(2) is atomic within a filesystem, so a crash or
     * a concurrent read can never observe a half-written .env — which would
     * take the whole application down, not just the setup wizard.
     *
     * @param  list<string>  $lines
     *
     * @throws EnvironmentWriteException
     */
    protected function put(array $lines, string $eol): void
    {
        $directory = dirname($this->path);
        $temp = tempnam($directory, 'env');

        if ($temp === false) {
            throw EnvironmentWriteException::notWritable($this->path);
        }

        // Exactly one trailing newline. Trimming both characters matters: on a
        // CRLF file, trimming only "\n" leaves the "\r" and appending the
        // terminator again yields "\r\r\n" — a bare CR the next reader sees as
        // an extra line.
        $contents = rtrim(implode($eol, $lines), "\r\n").$eol;

        try {
            if (File::put($temp, $contents) === false) {
                throw EnvironmentWriteException::notWritable($this->path);
            }

            // Preserve the original file's permissions; tempnam() creates 0600,
            // which would lock the web user out of its own .env on some hosts.
            if ($this->exists() && ($mode = @fileperms($this->path)) !== false) {
                @chmod($temp, $mode & 0777);
            }

            // rename() over an existing file is atomic on POSIX but fails on
            // Windows, where the destination must not exist. File::move()
            // handles the difference; the copy path is still crash-safe because
            // the fully-rendered temp file is the source.
            if (! @rename($temp, $this->path) && ! File::move($temp, $this->path)) {
                throw EnvironmentWriteException::notWritable($this->path);
            }
        } catch (EnvironmentWriteException $e) {
            @unlink($temp);

            throw $e;
        } catch (Throwable $e) {
            @unlink($temp);

            throw EnvironmentWriteException::failed($this->path, $e);
        }
    }

    /**
     * @throws EnvironmentWriteException
     */
    protected function guardWritable(): void
    {
        if (! $this->exists()) {
            throw EnvironmentWriteException::missing($this->path);
        }

        if (! File::isWritable($this->path) || ! File::isWritable(dirname($this->path))) {
            throw EnvironmentWriteException::notWritable($this->path);
        }
    }

    /**
     * Quote only when the value would otherwise not survive a round-trip:
     * whitespace, '#' (comment), or Dotenv's own metacharacters. Unquoted
     * simple values keep the file looking hand-written.
     */
    protected function format(string|int|bool|null $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        if (preg_match('/[\s#"\'\\\\$]/', $value) === 1) {
            return '"'.addcslashes($value, '\\"$').'"';
        }

        return $value;
    }

    protected function unquote(string $value): string
    {
        // Strip an inline comment from an unquoted value (FOO=bar # note).
        if (! str_starts_with($value, '"') && ! str_starts_with($value, "'")) {
            $value = trim(preg_split('/\s+#/', $value, 2)[0]);
        }

        if (strlen($value) >= 2) {
            $first = $value[0];

            if (($first === '"' || $first === "'") && str_ends_with($value, $first)) {
                return stripcslashes(substr($value, 1, -1));
            }
        }

        return $value;
    }
}
