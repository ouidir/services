<?php

namespace App\Service;

use App\Entity\Scenario;
use App\Service\Images\ExecutionImageManager;
use App\Service\Images\Gallery\GalleryNavigatorFactory;
use App\Service\Images\ImageFileReader;
use App\Service\Images\Screenshot\ScenarioScreenshotManager;

/**
 * Service principal pour la gestion des images
 * Délègue les responsabilités aux services spécialisés
 */
class ImagesService
{
    public function __construct(
        private readonly ExecutionImageManager $executionImageManager,
        private readonly ImageFileReader $fileReader,
        private readonly GalleryNavigatorFactory $navigatorFactory,
        private readonly ScenarioScreenshotManager $screenshotManager,
    ) {
    }

    /**
     * Ajoute des images d'exécution depuis des données JSON
     */
    public function addImages(string $jsonData): void
    {
        $executionData = json_decode($jsonData, true);

        if (!is_array($executionData)) {
            throw new \InvalidArgumentException('Invalid JSON data');
        }

        $this->executionImageManager->saveExecutionImages($executionData);
    }

    /**
     * Récupère la structure arborescente des dossiers
     */
    public function getFolder(string $folder): array
    {
        return $this->fileReader->getFolderTree($folder);
    }

    /**
     * Récupère les images d'une exécution spécifique
     */
    public function getExecutionImages(
        string $application,
        string $scenario,
        string $date,
        string $time
    ): array|false {
        return $this->executionImageManager->getExecutionImages(
            $application,
            $scenario,
            $date,
            $time
        );
    }

    /**
     * Récupère la navigation de la galerie selon le type
     */
    public function getGalerieNavigation(
        ApplicationService $applicationService,
        string $application,
        string $scenario,
        ?string $date = null,
        ?string $time = null,
        string $type = 'application'
    ): array {
        $navigator = $this->navigatorFactory->create($type);

        return $navigator->getNavigation(
            $applicationService,
            $application,
            $scenario,
            $date,
            $time
        );
    }

    /**
     * Gère les screenshots d'un scénario (extraction ou suppression)
     */
    public function screenshotScenario(Scenario $scenario, string $action): void
    {
        $this->screenshotManager->handleScreenshot($scenario, $action);
    }

    /**
     * Récupère les screenshots d'un scénario
     */
    public function getScreenShots(Scenario $scenario): array|false
    {
        return $this->screenshotManager->getScreenshots($scenario);
    }

    /**
     * Vérifie si des screenshots existent pour un scénario
     */
    public function checkScreenshots(Scenario $scenario): bool
    {
        return $this->screenshotManager->checkScreenshots($scenario);
    }
}
