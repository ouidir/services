<?php

namespace App\Service\Images\Gallery;

use App\Service\ApplicationService;
use App\Service\Images\ImageFileReader;

class TimeNavigator implements GalleryNavigationInterface
{
    public function __construct(
        private readonly ImageFileReader $fileReader,
    ) {
    }

    public function getNavigation(
        ApplicationService $applicationService,
        string $application,
        string $scenario,
        ?string $date = null,
        ?string $time = null
    ): array {
        if ($time) {
            return $this->fileReader->getFiles(
                $application . '/' . $scenario . '/' . $date . '/' . $time
            ) ?? [];
        }

        // Pour le cas 'ok' (sans date/time)
        return $this->fileReader->getFiles($application . '/' . $scenario) ?? [];
    }
}
