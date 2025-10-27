<?php declare(strict_types=1);

namespace Temant\HttpCore\Exceptions;

use Throwable;

/**
 * Exception thrown when a write operation is attempted on a non-writable stream.
 * This indicates that the stream does not support write operations.
 */
class StreamNotWritableException extends StreamException
{
    public function __construct(
        string $message = "Stream is not writable.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}