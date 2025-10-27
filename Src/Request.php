<?php

declare(strict_types=1);

namespace Temant\HttpCore;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

/**
 * PSR-7 HTTP Request implementation.
 *
 * Represents an HTTP request message, including the HTTP method,
 * target URI, headers, body, and protocol version.
 */
class Request extends Message implements RequestInterface
{
    private string $method;
    private UriInterface $uri;
    private string $requestTarget = '';
    private const string METHOD_PATTERN = '/^[!#$%&\'*+.^_`|~0-9a-z-]+$/i';
    private const string REQUEST_TARGET_PATTERN = '/\s/';

    /**
     * @param string $method HTTP request method (e.g., GET, POST)
     * @param UriInterface $uri URI of the request
     * @param array<string, array<string>> $headers Request headers
     * @param StreamInterface|null $body Request body
     * @param string $protocolVersion HTTP protocol version
     * 
     * @throws InvalidArgumentException For invalid status code or protocol version
     */
    public function __construct(
        string $method,
        UriInterface $uri,
        array $headers = [],
        ?StreamInterface $body = null,
        string $protocolVersion = '1.1'
    ) {
        $this->validateMethod($method);
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->protocolVersion = $this->filterProtocolVersion($protocolVersion);
        $this->body = $body ?? $this->createDefaultBodyStream();

        if (!isset($this->headers['host']) && $uri->getHost() !== '') {
            $this->updateHostHeader();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== '') {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }

        $query = $this->uri->getQuery();
        if ($query !== '') {
            $target .= '?' . $query;
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        if (preg_match(self::REQUEST_TARGET_PATTERN, $requestTarget)) {
            throw new InvalidArgumentException('Request target cannot contain whitespace');
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod(string $method): RequestInterface
    {
        $this->validateMethod($method);
        $method = strtoupper($method);

        if ($this->method === $method) {
            return $this;
        }

        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $clone = clone $this;
        $clone->uri = $uri;

        if (!$preserveHost || !isset($clone->headers['host'])) {
            $clone->updateHostHeader();
        }

        return $clone;
    }

    /**
     * Updates the Host header based on the current URI
     */
    private function updateHostHeader(): void
    {
        $host = $this->uri->getHost();
        if ($host === '') {
            return;
        }

        $port = $this->uri->getPort();
        $this->headers['host'] = [$port !== null ? $host . ':' . $port : $host];
    }

    /**
     * Validates the HTTP method
     *
     * @param string $method
     * @throws InvalidArgumentException If method is invalid
     */
    private function validateMethod(string $method): void
    {
        if ($method === '') {
            throw new InvalidArgumentException('HTTP method cannot be empty');
        }

        if (!preg_match(self::METHOD_PATTERN, $method)) {
            throw new InvalidArgumentException("Invalid HTTP method: $method");
        }
    }
}