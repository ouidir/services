<?php

namespace App\Service\Images\Gallery;

use App\Service\ApplicationService;
use App\Service\Images\ImageFileReader;

class ApplicationNavigator implements GalleryNavigationInterface
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
            $navigation = [
                'id' => $app['nom'],
                'name' => $app['nom'],
                'type' => 'scenario',
                'selected' => ($application === $app['nom']) ? 1 : 0,
                'children' => $this->buildScenarioChildren($app),
                'files' => [],
            ];

            $nav[] = $navigation;
        }

        return $nav;
    }

    private function buildScenarioChildren(array $app): array
    {
        $children = [];

        foreach ($app['scenarios'] as $scenario) {
            $files = $this->fileReader->getFiles(
                $app['nom'] . '/' . $scenario['nom']
            );

            $children[] = [
                'id' => $scenario['nom'],
                'name' => $scenario['nom'],
                'files' => $files ?? [],
            ];
        }

        return $children;
    }
}
