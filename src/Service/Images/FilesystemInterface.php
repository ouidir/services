<?php

namespace App\Service\Images;

interface FilesystemInterface
{
    public function exists(string $path): bool;

    public function mkdir(string $path): void;

    public function remove(string $path): void;

    public function dumpFile(string $path, string $content): void;

    public function listFiles(string $directory): array;

    public function listDirectories(string $directory): array;
}
