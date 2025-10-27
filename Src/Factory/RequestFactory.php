<?php

declare(strict_types=1);

namespace Temant\HttpCore\Factory;

use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Temant\HttpCore\Request;

class RequestFactory implements RequestFactoryInterface
{
    /**
     * @param StreamFactoryInterface $streamFactory
     * @param UriFactoryInterface $uriFactory
     */
    public function __construct(
        private StreamFactoryInterface $streamFactory = new StreamFactory(),
        private UriFactoryInterface $uriFactory = new UriFactory()
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }

        /** @phpstan-ignore instanceof.alwaysTrue */
        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException(
                'Parameter 2 of RequestFactory::createRequest() must be a string or a compatible UriInterface.'
            );
        }

        $body = $this->streamFactory->createStream();

        return new Request($method, $uri, [], $body);
    }
}