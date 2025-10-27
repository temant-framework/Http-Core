<?php

declare(strict_types=1);

namespace Temant\HttpCore\Factory;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Temant\HttpCore\Uri;

class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }

    /**
     * Create a URI from the global server variables.
     *
     * This method constructs a URI string based on the $_SERVER superglobal array,
     * following the same logic as most PHP frameworks and PSR-7 implementations.
     *
     * @param mixed[] $server The server array (typically $_SERVER)
     * @return UriInterface
     */
    public static function createUriFromGlobals(array $server): UriInterface
    {
        $scheme = match (true) {
            isset($server['HTTPS']) && $server['HTTPS'] !== 'off' => 'https',
            isset($server['REQUEST_SCHEME']) && is_string($server['REQUEST_SCHEME']) => strtolower($server['REQUEST_SCHEME']),
            isset($server['HTTP_X_FORWARDED_PROTO']) && is_string($server['HTTP_X_FORWARDED_PROTO']) => strtolower($server['HTTP_X_FORWARDED_PROTO']),
            default => 'http',
        };

        $host = 'localhost';
        if (isset($server['HTTP_HOST']) && is_string($server['HTTP_HOST'])) {
            $host = $server['HTTP_HOST'];
        } elseif (isset($server['SERVER_NAME']) && is_string($server['SERVER_NAME'])) {
            $host = $server['SERVER_NAME'];
        }
        $host = strtolower($host);

        $port = null;
        if (isset($server['SERVER_PORT'])) {
            $rawPort = $server['SERVER_PORT'];
            if (is_int($rawPort) || is_string($rawPort)) {
                $validated = filter_var((string) $rawPort, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 65535],
                ]);
                $port = $validated !== false ? (int) $validated : null;
            }
        }

        // Remove port from host if present and extract it
        $extractedPort = null;
        if (preg_match('/^(\[[0-9a-f:.]+\]|[^:]+):(\d+)$/i', $host, $matches)) {
            $host = $matches[1];
            $extractedPort = (int) $matches[2];
        }

        // Use extracted port from HTTP_HOST if available, otherwise use SERVER_PORT
        $port = $extractedPort ?? $port;

        $path = '/';
        if (isset($server['REQUEST_URI']) && is_string($server['REQUEST_URI'])) {
            $path = strtok($server['REQUEST_URI'], '?') ?: '/';
        }

        $query = '';
        if (isset($server['QUERY_STRING']) && is_string($server['QUERY_STRING'])) {
            $query = $server['QUERY_STRING'];
        }

        $user = isset($server['PHP_AUTH_USER']) && is_string($server['PHP_AUTH_USER'])
            ? $server['PHP_AUTH_USER'] : '';
        $pass = isset($server['PHP_AUTH_PW']) && is_string($server['PHP_AUTH_PW'])
            ? $server['PHP_AUTH_PW'] : '';

        $uriString = $scheme . '://';

        if ($user !== '') {
            $uriString .= rawurlencode($user);
            if ($pass !== '') {
                $uriString .= ':' . rawurlencode($pass);
            }
            $uriString .= '@';
        }

        $uriString .= $host;

        if ($port !== null) {
            $defaultPort = $scheme === 'https' ? 443 : 80;
            if ($port !== $defaultPort) {
                $uriString .= ':' . (string) $port;
            }
        }

        $uriString .= $path;

        if ($query !== '') {
            $uriString .= '?' . $query;
        }

        return new Uri($uriString);
    }
}