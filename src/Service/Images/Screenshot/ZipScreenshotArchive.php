<?php

namespace App\Service\Images\Screenshot;

use App\Entity\Scenario;
use App\Service\Images\FilesystemInterface;
use App\Service\Images\PathResolver;

class ZipScreenshotArchive implements ScreenshotArchiveInterface
{
    public function __construct(
        private readonly FilesystemInterface $filesystem,
        private readonly PathResolver $pathResolver,
    ) {
    }

    public function extract(Scenario $scenario): void
    {
        if (!$scenario->getScreenshots()) {
            return;
        }

        $zipFilename = $this->pathResolver->getZipPath($scenario->getScreenshots());
        $destinationDir = $this->getDestinationDirectory($scenario);

        $this->prepareDestinationDirectory($destinationDir);

        $zipArchive = new \ZipArchive();

        if ($zipArchive->open($zipFilename) !== true) {
            return;
        }

        $this->extractImages($zipArchive, $zipFilename, $destinationDir);
        $zipArchive->close();
    }

    public function delete(Scenario $scenario): void
    {
        $zipFilename = $this->pathResolver->getZipPath($scenario->getScreenshots());
        $destinationDir = $this->getDestinationDirectory($scenario);

        if ($this->filesystem->exists($destinationDir)) {
            $this->filesystem->remove($destinationDir);
        }

        $zipArchive = new \ZipArchive();

        if ($zipArchive->open($zipFilename) === true) {
            $zipArchive->close();
            $this->filesystem->remove($zipFilename);
        }
    }

    public function exists(Scenario $scenario): bool
    {
        $destinationDir = $this->getDestinationDirectory($scenario);
        return $this->filesystem->exists($destinationDir);
    }

    private function getDestinationDirectory(Scenario $scenario): string
    {
        return $this->pathResolver->getDescriptionPath(
            $scenario->getApplication()->getNom(),
            $scenario->getNom()
        );
    }

    private function prepareDestinationDirectory(string $directory): void
    {
        if ($this->filesystem->exists($directory)) {
            $this->filesystem->remove($directory);
        }

        $this->filesystem->mkdir($directory);
    }

    private function extractImages(
        \ZipArchive $zipArchive,
        string $zipFilename,
        string $destinationDir
    ): void {
        for ($i = 0; $i < $zipArchive->numFiles; $i++) {
            $filename = $zipArchive->getNameIndex($i);

            if (!$this->isImageFile($zipFilename, $filename)) {
                continue;
            }

            $fileInfo = pathinfo($filename);
            $sourcePath = "zip://{$zipFilename}#{$filename}";
            $destPath = $destinationDir . '/' . $fileInfo['basename'];

            copy($sourcePath, $destPath);
        }
    }

    private function isImageFile(string $zipFilename, string $filename): bool
    {
        $zipPath = "zip://{$zipFilename}#{$filename}";
        return @is_array(getimagesize($zipPath));
    }
}
