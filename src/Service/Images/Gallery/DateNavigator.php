<?php

namespace App\Service\Images\Gallery;

use App\Service\ApplicationService;
use App\Service\Images\ImageFileReader;

class DateNavigator implements GalleryNavigationInterface
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
        $nav = [];
        $folders = $this->fileReader->getFolders($application . '/' . $scenario);

        if (!$folders) {
            return $nav;
        }

        foreach ($folders as $folder) {
            if (in_array($folder, ['.', '..'])) {
                continue;
            }

            $navigation = [
                'id' => $folder,
                'name' => $folder,
                'type' => 'heure',
                'selected' => ($date === $folder) ? 1 : 0,
                'children' => $this->buildTimeChildren($application, $scenario, $folder),
                'files' => [],
            ];

            $nav[] = $navigation;
        }

        return $nav;
    }

    private function buildTimeChildren(
        string $application,
        string $scenario,
        string $date
    ): array {
        $children = [];
        $timeFolders = $this->fileReader->getFolders(
            $application . '/' . $scenario . '/' . $date
        );

        if (!$timeFolders) {
            return $children;
        }

        foreach ($timeFolders as $timeFolder) {
            if (in_array($timeFolder, ['.', '..'])) {
                continue;
            }

            $files = $this->fileReader->getFiles(
                $application . '/' . $scenario . '/' . $date . '/' . $timeFolder
            );

            $children[] = [
                'id' => $timeFolder,
                'name' => $timeFolder,
                'files' => $files ?? [],
            ];
        }

        return $children;
    }
}
