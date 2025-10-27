<?php

declare(strict_types=1);

namespace Temant\HttpCore\Factory;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Temant\HttpCore\Stream;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * @inheritDoc 
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://memory', 'r+');

        if ($resource === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException("Couldn't create a stream");
            // @codeCoverageIgnoreEnd
        }

        fwrite($resource, $content);
        rewind($resource);

        return new Stream($resource);
    }

    /**
     * @inheritDoc 
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if (empty($filename)) {
            throw new RuntimeException("Filename must not be empty");
        }

        $resource = @fopen($filename, $mode);
        if ($resource === false) {
            throw new RuntimeException("Unable to open file: {$filename}");
        }
        return new Stream($resource);
    }

    /**
     * @inheritDoc 
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}