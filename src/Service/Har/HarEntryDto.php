<?php

namespace App\Service\Har;

class HarEntryDto
{
    public function __construct(
        public readonly int $time,
        public readonly string $method,
        public readonly string $url,
        public readonly string $path,
        public readonly int $status,
        public readonly string $statusText,
        public readonly array $headers,
        public readonly int $size,
        public readonly string $mimeType,
    ) {
    }
}
