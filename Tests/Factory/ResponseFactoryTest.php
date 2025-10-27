<?php

declare(strict_types=1);

namespace Temant\HttpCore\Factory;

use Interop\Http\Factory\ResponseFactoryTestCase;
use Psr\Http\Message\ResponseFactoryInterface;

class ResponseFactoryTest extends ResponseFactoryTestCase
{
    /**
     * @return ResponseFactoryInterface
     */
    protected function createResponseFactory(): ResponseFactoryInterface
    {
        return new ResponseFactory();
    }
}