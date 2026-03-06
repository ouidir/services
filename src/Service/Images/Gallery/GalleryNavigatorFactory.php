<?php

namespace App\Service\Images\Gallery;

use App\Service\Images\ImageFileReader;

class GalleryNavigatorFactory
{
    private array $navigators = [];

    public function __construct(
        private readonly ImageFileReader $fileReader,
    ) {
    }

    public function create(string $type): GalleryNavigationInterface
    {
        if (isset($this->navigators[$type])) {
            return $this->navigators[$type];
        }

        $navigator = match ($type) {
            'application' => new ApplicationNavigator($this->fileReader),
            'scenario' => new ScenarioNavigator($this->fileReader),
            'date' => new DateNavigator($this->fileReader),
            'heure', 'ok' => new TimeNavigator($this->fileReader),
            default => throw new \InvalidArgumentException("Unknown navigator type: {$type}"),
        };

        $this->navigators[$type] = $navigator;

        return $navigator;
    }
}
