<?php

namespace App\Service\Images;

use App\Service\Images\FilesystemInterface;

class ImageFileReader
{
    public function __construct(
        private readonly FilesystemInterface $filesystem,
        private readonly PathResolver $pathResolver,
    ) {
    }

    public function getFolders(string $relativePath): array|false
    {
        $directory = $this->pathResolver->getScreenshotPath($relativePath);

        if (!$this->filesystem->exists($directory)) {
            return false;
        }

        $directories = $this->filesystem->listDirectories($directory);
        sort($directories);

        return $directories;
    }

    public function getFiles(string $relativePath): array|false
    {
        $directory = $this->pathResolver->getScreenshotPath($relativePath);

        if (!$this->filesystem->exists($directory)) {
            return false;
        }

        $files = [];
        foreach ($this->filesystem->listFiles($directory) as $file) {
            $files[] = $directory . '/' . $file;
        }

        sort($files);

        return $files;
    }

    /**
     * Récupère la structure arborescente des dossiers
     */
    public function getFolderTree(string $folder): array
    {
        $destDir = $this->pathResolver->getScreenshotPath($folder);
        return $this->buildTree($destDir, $folder, '_DATE', '');
    }

    private function buildTree(
        string $directory,
        string $folder,
        string $type,
        string $prefix
    ): array {
        $folders = [];

        if (!$this->filesystem->exists($directory)) {
            return $folders;
        }

        foreach ($this->filesystem->listDirectories($directory) as $targetDir) {
            $fullPath = $directory . '/' . $targetDir;

            $item = [
                'id' => $prefix . $targetDir,
                'text' => $targetDir,
                'type' => $type,
            ];

            $children = $this->buildTree(
                $fullPath,
                $folder,
                '_HEURE',
                $targetDir . '/'
            );

            if (count($children) > 0) {
                $item['children'] = $children;
            } else {
                $files = $this->getFiles($folder . '/' . $prefix . $targetDir);
                $item['text'] .= ' (' . count($files ?: []) . ')';
            }

            $folders[] = $item;
        }

        return $folders;
    }
}
