<?php declare(strict_types=1);

namespace Temant\HttpCore\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception class for stream-related errors.
 * This exception serves as a general base class for all exceptions
 * related to stream operations within the Temant\HttpCore library.
 */
class StreamException extends RuntimeException
{
    public function __construct(string $message = "Stream exception occurred", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}