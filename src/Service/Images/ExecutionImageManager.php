<?php

namespace App\Service\Images;

class ExecutionImageManager
{
    public function __construct(
        private readonly FilesystemInterface $filesystem,
        private readonly PathResolver $pathResolver,
    ) {
    }

    public function saveExecutionImages(array $executionData): void
    {
        foreach ($executionData as $execution) {
            $this->saveExecution($execution);
        }
    }

    private function saveExecution(array $execution): void
    {
        $destDir = $this->pathResolver->getScreenshotPath(
            $execution['application'],
            $execution['scenario']
        );

        $this->prepareDirectory($destDir);
        $this->saveImages($destDir, $execution['images']);

        if ($execution['status']) {
            $this->archiveImages($destDir, $execution);
        }
    }

    private function prepareDirectory(string $directory): void
    {
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
            return;
        }

        // Nettoyer les anciens fichiers PNG
        $files = glob($directory . "/*.png");
        foreach ($files as $filename) {
            unlink($filename);
        }
    }

    private function saveImages(string $directory, array $images): void
    {
        foreach ($images as $image) {
            $filePath = $directory . '/' . $image['name'];
            $content = base64_decode($image['image']);
            $this->filesystem->dumpFile($filePath, $content);
        }
    }

    private function archiveImages(string $baseDir, array $execution): void
    {
        $date = new \DateTime($execution['date']);
        $archiveDir = sprintf(
            '%s/%s/%s',
            $baseDir,
            $date->format('Y-m-d'),
            $date->format('H:i:s')
        );

        if (!$this->filesystem->exists($archiveDir)) {
            $this->filesystem->mkdir($archiveDir);
        }

        $this->saveImages($archiveDir, $execution['images']);
    }

    public function getExecutionImages(
        string $application,
        string $scenario,
        string $date,
        string $time
    ): array|false {
        $directory = $this->pathResolver->getScreenshotPath(
            $application,
            $scenario,
            $date,
            $time
        );

        if (!$this->filesystem->exists($directory)) {
            return false;
        }

        $files = $this->filesystem->listFiles($directory);
        sort($files);

        return $files;
    }
}
