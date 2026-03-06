<?php

namespace App\Service\Images;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Path;

class PathResolver
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%app.path.screenshot_executions%')]
        private readonly string $screenshotDirectory,
        #[Autowire('%app.path.screenshot_zip%')]
        private readonly string $screenshotZipPath,
        #[Autowire('%app.path.screenshot_descriptions%')]
        private readonly string $screenshotDescriptionPath,
    ) {
    }

    public function getScreenshotPath(string ...$segments): string
    {
        return Path::join(
            $this->projectDir,
            'public',
            $this->screenshotDirectory,
            ...$segments
        );
    }

    public function getZipPath(string $filename): string
    {
        return Path::join(
            $this->projectDir,
            'public',
            $this->screenshotZipPath,
            $filename
        );
    }

    public function getDescriptionPath(string ...$segments): string
    {
        return Path::join(
            $this->projectDir,
            'public',
            $this->screenshotDescriptionPath,
            ...$segments
        );
    }

    public function getRelativeDescriptionPath(string ...$segments): string
    {
        return Path::join(
            $this->screenshotDescriptionPath,
            ...$segments
        );
    }
}
