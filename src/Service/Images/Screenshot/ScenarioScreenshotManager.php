<?php

namespace App\Service\Images\Screenshot;

use App\Entity\Scenario;
use App\Service\Images\FilesystemInterface;
use App\Service\Images\PathResolver;

class ScenarioScreenshotManager
{
    public function __construct(
        private readonly ScreenshotArchiveInterface $archive,
        private readonly FilesystemInterface $filesystem,
        private readonly PathResolver $pathResolver,
    ) {
    }

    public function handleScreenshot(Scenario $scenario, string $action): void
    {
        match ($action) {
            'unzip' => $this->archive->extract($scenario),
            'delete' => $this->archive->delete($scenario),
            default => throw new \InvalidArgumentException("Unknown action: {$action}"),
        };
    }

    public function getScreenshots(Scenario $scenario): array|false
    {
        $directory = $this->pathResolver->getDescriptionPath(
            $scenario->getApplication()->getNom(),
            $scenario->getNom()
        );

        if (!$this->filesystem->exists($directory)) {
            return false;
        }

        $files = [];
        $relativePath = $this->pathResolver->getRelativeDescriptionPath(
            $scenario->getApplication()->getNom(),
            $scenario->getNom()
        );

        foreach ($this->filesystem->listFiles($directory) as $file) {
            $files[] = $relativePath . '/' . $file;
        }

        sort($files, SORT_NATURAL);

        return $files;
    }

    public function checkScreenshots(Scenario $scenario): bool
    {
        return $this->archive->exists($scenario);
    }
}
