<?php

declare(strict_types=1);

namespace Temant\HttpCore;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Stringable;

/**
 * Simple PSR-7 compatible URI implementation.
 *
 * This class implements the behaviour expected from PSR-7 UriInterface:
 * - immutable with*() methods returning a cloned instance
 * - getters returning normalized values
 *
 * It intentionally keeps a minimal surface for RFC3986 handling while
 * providing reasonable validation and normalization.
 */
final class Uri implements UriInterface, Stringable
{
    private const array STANDARD_PORTS = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
    ];

    private string $scheme = '';
    private string $userInfo = '';
    private string $host = '';
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);
            if ($parts === false || (!isset($parts['host']) && !isset($parts['path']))) {
                throw new InvalidArgumentException("Invalid URI: {$uri}");
            }

            // @phpstan-ignore greater.alwaysFalse
            if (isset($parts['port']) && ($parts['port'] < 1 || $parts['port'] > 65535)) {
                throw new InvalidArgumentException('Invalid port number in URI');
            }

            $this->applyParts($parts);
        }
    }

    /**
     * Applies parsed URI components to the object properties.
     *
     * @param array{
     *     scheme?: string,
     *     user?: string,
     *     pass?: string,
     *     host?: string,
     *     port?: int,
     *     path?: string,
     *     query?: string,
     *     fragment?: string
     * } $parts Parsed URI components as returned by parse_url().
     *
     * @return void
     * @throws InvalidArgumentException if port is invalid
     */
    private function applyParts(array $parts): void
    {
        $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';

        $user = $parts['user'] ?? null;
        $pass = $parts['pass'] ?? null;
        $this->userInfo = $user !== null
            ? ($pass !== null
                ? rawurlencode($user) . ':' . rawurlencode($pass)
                : rawurlencode($user))
            : '';

        $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port = $parts['port'] ?? null;
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query = $parts['query'] ?? '';
        $this->fragment = $parts['fragment'] ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = $this->userInfo !== '' ? $this->userInfo . '@' : '';
        $authority .= $this->host;

        $port = $this->getPort();
        if ($port !== null) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritDoc}
     */
    public function getPort(): ?int
    {
        return $this->port !== null
            && (!isset(self::STANDARD_PORTS[$this->scheme])
                || $this->port !== self::STANDARD_PORTS[$this->scheme])
            ? $this->port
            : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $scheme scheme component (e.g. "http", "https")
     * @return static
     * @throws InvalidArgumentException for invalid scheme
     */
    public function withScheme(string $scheme): static
    {
        $scheme = strtolower($scheme);
        if ($scheme !== '' && !preg_match('/^[a-z][a-z0-9+\-.]*$/i', $scheme)) {
            throw new InvalidArgumentException('Invalid scheme "' . $scheme . '"');
        }
        if ($this->scheme === $scheme) {
            return $this;
        }

        $clone = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @param string      $user
     * @param string|null $password
     * @return static
     */
    public function withUserInfo(string $user, ?string $password = null): static
    {
        $u = rawurlencode($user);
        $newUserInfo = $password !== null ? $u . ':' . rawurlencode($password) : $u;
        if ($newUserInfo === $this->userInfo) {
            return $this;
        }

        $clone = clone $this;
        $clone->userInfo = $newUserInfo;
        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $host
     * @return static
     */
    public function withHost(string $host): static
    {
        $host = strtolower($host);
        if ($host === $this->host) {
            return $this;
        }

        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @param int|null $port
     * @return static
     * @throws InvalidArgumentException on invalid port value
     */
    public function withPort(?int $port): static
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException('Invalid port number');
        }
        if ($port === $this->port) {
            return $this;
        }

        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $path
     * @return static
     */
    public function withPath(string $path): static
    {
        $filtered = $this->filterPath($path);
        if ($filtered === $this->path) {
            return $this;
        }

        $clone = clone $this;
        $clone->path = $filtered;
        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $query
     * @return static
     */
    public function withQuery(string $query): static
    {
        $trimmed = ltrim($query, '?');
        if ($trimmed === $this->query) {
            return $this;
        }

        $clone = clone $this;
        $clone->query = $trimmed;
        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $fragment
     * @return static
     */
    public function withFragment(string $fragment): static
    {
        $trimmed = ltrim($fragment, '#');
        if ($trimmed === $this->fragment) {
            return $this;
        }

        $clone = clone $this;
        $clone->fragment = $trimmed;
        return $clone;
    }

    public function __toString(): string
    {
        $uri = '';
        $scheme = $this->scheme;
        if ($scheme !== '') {
            $uri = $scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        $uri .= $this->path;

        $query = $this->query;
        if ($query !== '') {
            $uri .= '?' . $query;
        }

        $fragment = $this->fragment;
        if ($fragment !== '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * Minimal path filter: keeps "/" and encodes other characters using rawurlencode
     *
     * This method splits the path by "/" and encodes each segment individually,
     * preserving the "/" separators.
     *
     * @param string $path The path component to filter/encode.
     * @return string The filtered (encoded) path.
     */
    private function filterPath(string $path): string
    {
        if ($path === '' || $path === '/') {
            return $path;
        }

        return str_replace('%2F', '/', rawurlencode($path));
    }
}