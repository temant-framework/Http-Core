<?php

declare(strict_types=1);

namespace Temant\HttpCore\Factory;

use Psr\Http\Message\ResponseFactoryInterface;
use Temant\HttpCore\Response;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): \Psr\Http\Message\ResponseInterface
    {
        return new Response(
            $code,
            [],
            null,
            '1.1',
            $reasonPhrase
        );
    }
}