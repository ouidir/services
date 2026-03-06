<?php

namespace App\Service\Images;

use Symfony\Component\Filesystem\Filesystem;

class SymfonyFilesystemAdapter implements FilesystemInterface
{
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function exists(string $path): bool
    {
        return $this->filesystem->exists($path);
    }

    public function mkdir(string $path): void
    {
        $this->filesystem->mkdir($path);
    }

    public function remove(string $path): void
    {
        $this->filesystem->remove($path);
    }

    public function dumpFile(string $path, string $content): void
    {
        $this->filesystem->dumpFile($path, $content);
    }

    public function listFiles(string $directory): array
    {
        if (!$this->exists($directory)) {
            return [];
        }

        $files = [];
        foreach (scandir($directory) as $item) {
            $fullPath = $directory . '/' . $item;
            if (is_file($fullPath)) {
                $files[] = $item;
            }
        }

        sort($files);
        return $files;
    }

    public function listDirectories(string $directory): array
    {
        if (!$this->exists($directory)) {
            return [];
        }

        $directories = [];
        foreach (scandir($directory, SCANDIR_SORT_DESCENDING) as $item) {
            $fullPath = $directory . '/' . $item;
            if (is_dir($fullPath) && !in_array($item, ['.', '..'])) {
                $directories[] = $item;
            }
        }

        return $directories;
    }
}
