<?php

namespace App\Services\Setup\Exceptions;

use RuntimeException;
use Throwable;

/**
 * The .env file could not be persisted.
 *
 * Messages are written for the person running the installer — usually someone
 * on shared hosting who needs to know exactly which file to chmod — so they
 * name the path and the fix rather than leaking a PHP-level error.
 */
class EnvironmentWriteException extends RuntimeException
{
    public static function missing(string $path): self
    {
        return new self(
            "No .env file was found at {$path}. Copy .env.example to .env and reload this page."
        );
    }

    public static function notWritable(string $path): self
    {
        return new self(
            "The .env file at {$path} is not writable. Grant the web server write access to it (chmod 664) and try again."
        );
    }

    public static function failed(string $path, Throwable $previous): self
    {
        return new self(
            "The .env file at {$path} could not be saved: {$previous->getMessage()}",
            0,
            $previous
        );
    }
}
