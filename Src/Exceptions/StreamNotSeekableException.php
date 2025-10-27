<?php declare(strict_types=1);

namespace Temant\HttpCore\Exceptions;
 
use Throwable;

/**
 * Exception thrown when a seek operation is attempted on a non-seekable stream.
 * This exception is thrown to indicate that the stream is not in a state
 * that allows seeking operations to be performed.
 */
class StreamNotSeekableException extends StreamException
{
    public function __construct(
        string $message = "Stream is not seekable.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}