<?php

namespace App\Service\Images\Gallery;

use App\Service\ApplicationService;
use App\Service\Images\ImageFileReader;

class ScenarioNavigator implements GalleryNavigationInterface
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
        $apps = $applicationService->getCachedApplications();

        foreach ($apps as $app) {
            if ($application !== $app['nom']) {
                continue;
            }

            foreach ($app['scenarios'] as $s) {
                $navigation = [
                    'id' => $s['nom'],
                    'name' => $s['nom'],
                    'type' => 'date',
                    'selected' => ($scenario === $s['nom']) ? 1 : 0,
                    'children' => $this->buildDateChildren($application, $s['nom']),
                    'files' => $this->fileReader->getFiles($application . '/' . $s['nom']) ?? [],
                ];

                $nav[] = $navigation;
            }
        }

        return $nav;
    }

    private function buildDateChildren(string $application, string $scenario): array
    {
        $children = [];
        $folders = $this->fileReader->getFolders($application . '/' . $scenario);

        if (!$folders) {
            return $children;
        }

        foreach ($folders as $folder) {
            if (in_array($folder, ['.', '..'])) {
                continue;
            }

            $children[] = [
                'id' => $folder,
                'name' => $folder,
            ];
        }

        return $children;
    }
}
