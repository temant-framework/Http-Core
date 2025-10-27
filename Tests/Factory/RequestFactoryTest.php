<?php

declare(strict_types=1);

namespace Temant\HttpCore\Factory;

use Interop\Http\Factory\RequestFactoryTestCase;
use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;

class RequestFactoryTest extends RequestFactoryTestCase
{
    /**
     * @return RequestFactoryInterface
     */
    protected function createRequestFactory(): RequestFactoryInterface
    {
        return new RequestFactory();
    }

    /**
     * @param string $uri
     *
     * @return UriInterface
     */
    protected function createUri($uri): UriInterface
    {
        return new UriFactory()->createUri($uri);
    }

    public function testInValidUriThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->factory->createRequest('GET', 123); /** @phpstan-ignore argument.type */
    }
}