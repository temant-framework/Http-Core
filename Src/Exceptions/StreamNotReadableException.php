<?php declare(strict_types=1);

namespace Temant\HttpCore\Exceptions;
 
use Throwable;

/**
 * Exception thrown when a read operation is attempted on a non-readable stream.
 * This exception is thrown to indicate that the stream is not in a state
 * that allows reading operations to be performed.
 */
class StreamNotReadableException extends StreamException
{
    public function __construct(
        string $message = "Stream is not readable.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}