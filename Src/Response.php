<?php

declare(strict_types=1);

namespace Temant\HttpCore;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * PSR-7 HTTP Response implementation.
 *
 * Represents an outgoing server-side HTTP response according to the PSR-7 specification.
 * 
 * @link https://www.php-fig.org/psr/psr-7/ PSR-7 Specification
 */
class Response extends Message implements ResponseInterface
{
    /**
     * Map of standard HTTP status code/reason phrases
     *
     * @var array<int, string>
     */
    private const array DEFAULT_REASON_PHRASES = [
        // 1xx: Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        // 2xx: Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        // 3xx: Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        // 4xx: Client Error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        // 5xx: Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var int HTTP status code
     */
    private int $statusCode;

    /**
     * @var string Reason phrase
     */
    private string $reasonPhrase;

    /**
     * Create a new HTTP response.
     *
     * @param int $statusCode HTTP status code (default: 200)
     * @param array<string, string[]> $headers Response headers
     * @param StreamInterface|null $body Response body
     * @param string $protocolVersion HTTP protocol version (default: '1.1')
     * @param string $reasonPhrase Reason phrase (if empty, will use standard phrase)
     * 
     * @throws InvalidArgumentException For invalid status code or protocol version
     * @throws RuntimeException When body stream cannot be created
     */
    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        ?StreamInterface $body = null,
        string $protocolVersion = '1.1',
        string $reasonPhrase = ''
    ) {
        $this->validateStatusCode($statusCode);

        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase !== ''
            ? $this->filterHeaderValue($reasonPhrase)[0]
            : '';

        $this->protocolVersion = $this->filterProtocolVersion($protocolVersion);
        $this->headers = array_change_key_case($headers, CASE_LOWER);

        $this->body = $body ?? $this->createDefaultBodyStream();
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $this->validateStatusCode($code);

        if ($code === $this->statusCode && $reasonPhrase === $this->reasonPhrase) {
            return $this;
        }

        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase !== ''
            ? $this->filterHeaderValue($reasonPhrase)[0]
            : '';

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): string
    {
        if ($this->reasonPhrase !== '') {
            return $this->reasonPhrase;
        }

        return self::DEFAULT_REASON_PHRASES[$this->statusCode] ?? '';
    }

    /**
     * Validate that the status code is within the valid range (100-599)
     *
     * @param int $statusCode
     * @throws InvalidArgumentException For invalid status codes
     */
    private function validateStatusCode(int $statusCode): void
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new InvalidArgumentException(sprintf(
                'Invalid HTTP status code: %d. Must be between 100 and 599.',
                $statusCode
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }
}