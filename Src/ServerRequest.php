<?php

declare(strict_types=1);

namespace Temant\HttpCore;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-7 HTTP Server Request implementation.
 *
 * Represents an incoming HTTP request from a server environment,
 * including server parameters, cookies, query string arguments,
 * uploaded files, parsed body, and custom attributes.
 */
final class ServerRequest extends Request implements ServerRequestInterface
{
    /** @var array<string, mixed> */
    private array $attributes = [];

    /**
     * @param array<mixed> $serverParams
     * @param array<mixed> $cookieParams
     * @param array<mixed> $queryParams
     * @param array<UploadedFileInterface> $uploadedFiles
     * @param array<mixed>|object|null $parsedBody
     */
    public function __construct(
        string $method,
        UriInterface $uri,
        private array $serverParams = [],
        array $headers = [],
        ?StreamInterface $body = null,
        string $protocolVersion = '1.1',
        private array $cookieParams = [],
        private array $queryParams = [],
        private array $uploadedFiles = [],
        private array|object|null $parsedBody = null
    ) {
        parent::__construct($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * @inheritDoc
     * 
     * @return array<mixed>
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @inheritDoc
     * 
     * @return array<mixed>
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @inheritDoc
     * 
     * @param array<string, string> $cookies
     */
    public function withCookieParams(array $cookies): self
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /**
     * @inheritDoc
     * 
     * @return array<mixed>
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     * 
     * @param array<string, mixed> $query
     */
    public function withQueryParams(array $query): self
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * @inheritDoc
     * 
     * @return array<UploadedFileInterface>
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @inheritDoc
     * 
     * @param array<UploadedFileInterface> $uploadedFiles
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * @inheritDoc
     * 
     * @return array<mixed>|object|null
     */
    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody;
    }

    /**
     * @inheritDoc
     * 
     * @param array<string, mixed>|object|null $data
     * 
     * @throws InvalidArgumentException if $data is not an array, object
     */
    public function withParsedBody($data): self
    {
        if ($data === null) {
            return $this;
        }

        /** @phpstan-ignore function.alreadyNarrowedType, booleanAnd.alwaysFalse */
        if (!is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException('Parsed body must be array, object, or null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    /**
     * @inheritDoc
     * 
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute(string $name, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute(string $name): self
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}