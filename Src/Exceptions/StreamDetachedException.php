<?php declare(strict_types=1);

namespace Temant\HttpCore\Exceptions;

use Throwable;

/**
 * Exception thrown when a stream operation is attempted on a detached stream.
 * This indicates that the stream is no longer available for operations.
 */
class StreamDetachedException extends StreamException
{
    public function __construct(
        string $message = "Stream is detached and cannot be used.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}