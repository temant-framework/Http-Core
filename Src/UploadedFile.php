<?php

declare(strict_types=1);

namespace Temant\HttpCore;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * PSR-7 Uploaded File implementation.
 *
 * Represents an uploaded file according to the PSR-7 specification.
 * Provides functionality for handling files uploaded through HTTP requests,
 * including stream access and file movement operations.
 *
 * @link https://www.php-fig.org/psr/psr-7/ PSR-7 Specification
 */
final class UploadedFile implements UploadedFileInterface
{
    private StreamInterface $stream;
    private bool $moved = false;

    /**
     * Construct a new UploadedFile instance.
     *
     * @param StreamInterface|string $stream Underlying stream or file path
     * @param ?int $size The file size in bytes
     * @param int $error PHP file upload error code
     * @param ?string $clientFilename The filename sent by the client
     * @param ?string $clientMediaType The media type sent by the client
     *
     * @throws InvalidArgumentException If the error code is invalid or stream cannot be created
     */
    public function __construct(
        StreamInterface|string $stream,
        private ?string $clientFilename,
        private ?string $clientMediaType,
        private ?int $size,
        private int $error
    ) {
        if ($error < \UPLOAD_ERR_OK || $error > \UPLOAD_ERR_EXTENSION) {
            throw new InvalidArgumentException('Invalid upload error code.');
        }

        if (\is_string($stream)) {
            $resource = @\fopen($stream, 'rb');
            if ($resource === false) {
                throw new InvalidArgumentException("Unable to open file: {$stream}");
            }
            $this->stream = new Stream($resource);
        } else {
            $this->stream = $stream;
        }
    }

    /**
     * @inheritDoc
     */
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('Uploaded file has already been moved.');
        }
        if ($this->error !== \UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error.');
        }
        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new RuntimeException('Uploaded file has already been moved.');
        }
        if (\trim($targetPath) === '') {
            throw new InvalidArgumentException('Invalid target path.');
        }
        if ($this->error !== \UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot move file due to upload error.');
        }

        $stream = $this->getStream();
        $stream->rewind();

        $dest = @\fopen($targetPath, 'wb');
        if ($dest === false) {
            throw new RuntimeException("Unable to open destination: {$targetPath}");
        }

        while (!$stream->eof()) {
            \fwrite($dest, $stream->read(8192));
        }
        \fclose($dest);

        $this->moved = true;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * @inheritDoc
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}