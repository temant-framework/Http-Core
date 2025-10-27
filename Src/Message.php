<?php

declare(strict_types=1);

namespace Temant\HttpCore;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * Base PSR-7 Message implementation.
 *
 * Provides common functionality for HTTP messages (Request and Response) including:
 * - Protocol version handling
 * - Header management (case-insensitive)
 * - Message body management via streams
 *
 * @link https://www.php-fig.org/psr/psr-7/ PSR-7 Specification
 */
abstract class Message implements MessageInterface
{
    /**
     * HTTP headers (lowercased name => array of values)
     *
     * @var array<string, string[]>
     */
    protected array $headers = [];

    /**
     * Message body stream
     *
     * @var StreamInterface|null
     */
    protected ?StreamInterface $body = null;

    /**
     * HTTP protocol version (e.g., '1.0', '1.1', '2')
     *
     * @var string
     */
    public string $protocolVersion = '1.1';

    private const string PROTOCOL_PATTERN = '/^(1\.[01]|2(?:\.0)?)$/';

    private const string HEADER_VALUE_PATTERN = "/[\r\n]/";

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $clone = clone $this;
        $clone->protocolVersion = $this->filterProtocolVersion($version);
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine(string $name): string
    {
        $header = $this->getHeader($name);
        return $header ? implode(', ', $header) : '';
    }

    /**
     * @inheritDoc
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $normalized = strtolower($name);
        $value = $this->filterHeaderValue($value);

        // Prevent unnecessary cloning
        if (isset($this->headers[$normalized]) && $this->headers[$normalized] === $value) {
            return $this;
        }

        $clone = clone $this;
        // Preserve original case of header name for output
        $clone->headers[$normalized] = $value;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $normalized = strtolower($name);
        $value = $this->filterHeaderValue($value);

        $clone = clone $this;
        $clone->headers[$normalized] = isset($clone->headers[$normalized])
            ? array_merge($clone->headers[$normalized], $value)
            : $value;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $normalized = strtolower($name);

        if (!isset($this->headers[$normalized])) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->headers[$normalized]);
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return $this->body ?? throw new RuntimeException('Message body is not set');
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($this->body === $body) {
            return $this;
        }

        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * Filter and validate header values
     *
     * @param string|string[] $value
     * @return string[]
     * @throws InvalidArgumentException For invalid header values
     */
    protected function filterHeaderValue(array|string $value): array
    {
        $values = is_array($value) ? $value : [$value];

        if (empty($values) || $value === '') {
            throw new InvalidArgumentException('Header value cannot be empty');
        }

        foreach ($values as $item) {
            $item = (string) $item;
            if (preg_match(self::HEADER_VALUE_PATTERN, $item)) {
                throw new InvalidArgumentException(
                    'Header values cannot contain CR or LF characters'
                );
            }
        }

        return array_map('strval', $values);
    }

    /**
     * Validate protocol version
     *
     * @param string $version
     * @return string
     * @throws InvalidArgumentException For invalid protocol versions
     */
    protected function filterProtocolVersion(string $version): string
    {
        if (!preg_match(self::PROTOCOL_PATTERN, $version)) {
            throw new InvalidArgumentException(
                'Unsupported HTTP protocol version. Must be one of: 1.0, 1.1, 2, 2.0'
            );
        }

        return $version;
    }

    /**
     * Create the default body stream.
     *
     * @return StreamInterface
     * @throws RuntimeException if stream creation fails
     */
    protected function createDefaultBodyStream(): StreamInterface
    {
        $resource = @fopen('php://temp', 'r+');
        if ($resource === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Failed to create temporary stream');
            // @codeCoverageIgnoreEnd
        }
        return new Stream($resource);
    }
}