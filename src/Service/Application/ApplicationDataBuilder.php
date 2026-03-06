<?php

namespace App\Service\Application;

use App\Entity\Application;
use App\Handler\CacheHandler\ApplicationCacheHandler;
use App\Model\DirectionModel;
use App\Repository\ApplicationRepository;

/**
 * Builds application data structures for various use cases.
 */
class ApplicationDataBuilder
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ApplicationCacheHandler $cacheService
    ) {
    }

    /**
     * @return array<int, array>
     */
    public function buildLightApplicationsList(bool $withScenario = true, bool $withSimulateur = false): array
    {
        $applications = $this->applicationRepository->getActiveApplications();

        return array_map(
            fn(Application $app) => $this->buildLightApplication($app, $withScenario, $withSimulateur),
            $applications
        );
    }

    public function buildLightApplication(Application $app, bool $withScenario, bool $withSimulateur): array
    {
        $cachedData = $this->cacheService->getCachedApplication($app);

        $data = [
            'id' => $cachedData['id'] ?? $app->getId(),
            'nom' => $cachedData['nom'] ?? $app->getNom(),
            'domaine' => $cachedData['domaine']['id'] ?? $app->getDomaine()?->getId(),
            'sousDomaine' => $cachedData['sousDomaine']['id'] ?? $app->getSousDomaine()?->getId(),
            'zone' => $cachedData['zone']['id'] ?? $app->getZone()?->getId(),
            'nbrSwitchs' => $cachedData['nbrSwitchs'] ?? 0,
            'nbrGesips' => $cachedData['nbrGesips'] ?? 0,
            'nbrInformations' => $cachedData['nbrInformations'] ?? 0,
            'status' => $cachedData['status'] ?? null,
            'directions' => $cachedData['directions'] ?? DirectionModel::application($app),
            'dynatrace' => $cachedData['flagDynatrace'] ?? $app->getFlagDynatrace(),
            'nubo' => $cachedData['flagNubo'] ?? $app->getFlagNubo(),
        ];

        if ($withScenario && isset($cachedData['scenarios'])) {
            $data['scenarios'] = array_map(
                fn(array $scenario) => $this->buildLightScenario($scenario, $withSimulateur),
                $cachedData['scenarios']
            );
        }

        return $data;
    }

    private function buildLightScenario(array $scenario, bool $withSimulateur): array
    {
        $data = [
            'id' => $scenario['id'] ?? null,
            'nom' => $scenario['nom'] ?? null,
            'status' => $scenario['lastExecution']['statusExecution'] ?? null,
            'scenarioCom' => $scenario['scenarioCom'] ?? false,
            'meteo' => $scenario['meteo'] ?? false,
        ];

        if ($withSimulateur) {
            $data['versionSimulateur'] = $scenario['versionSimulateur']['id'] ?? null;
        }

        return $data;
    }

    /**
     * @return array<int, array>
     */
    public function buildApplicationsWithArchive(): array
    {
        $applications = $this->applicationRepository->getApplicationByScenarioArchived();

        return array_filter(
            array_map(
                fn(Application $app) => $this->buildApplicationWithArchive($app),
                $applications
            ),
            fn(?array $data) => $data !== null
        );
    }

    private function buildApplicationWithArchive(Application $app): ?array
    {
        if ($app->getScenarios()->count() === 0) {
            return null;
        }

        return [
            'id' => $app->getId(),
            'nom' => $app->getNom(),
            'domaine' => $app->getDomaine()->getId(),
            'sousDomaine' => $app->getSousDomaine()->getId(),
            'zone' => $app->getZone()->getId(),
            'status' => $app->getStatus(),
            'directions' => DirectionModel::application($app),
            'dynatrace' => $app->getFlagDynatrace(),
            'nubo' => $app->getFlagNubo(),
            'scenarios' => $this->extractScenarioData($app),
        ];
    }

    /**
     * @return array<int, array>
     */
    private function extractScenarioData(Application $app): array
    {
        $scenarios = [];

        foreach ($app->getScenarios() as $scenario) {
            $scenarios[] = [
                'id' => $scenario->getId(),
                'nom' => $scenario->getNom(),
                'status' => $scenario->getLastExecution()?->getStatusExecution(),
                'versionSimulateur' => $scenario->getVersionSimulateur()?->getId(),
                'scenarioCom' => $scenario->getScenarioCom() ?: false,
                'meteo' => $scenario->getMeteo() ?: false,
            ];
        }

        return $scenarios;
    }
}
