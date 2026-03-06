<?php

namespace App\Service\Application;

use App\Entity\Application;
use App\Model\DirectionModel;
use App\Model\StatusModel;
use App\Repository\ApplicationRepository;
use App\Repository\ScenarioRepository;

/**
 * Handles historical application status reconstruction.
 */
class ApplicationHistoryService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ScenarioRepository $scenarioRepository,
    ) {
    }

    /**
     * @return array<int, array>
     */
    public function getApplicationStatusAtDate(\DateTimeInterface $date): array
    {
        $statusScenarios = $this->scenarioRepository->getStatusScenarioAnterieur($date);
        $data = $this->buildHistoricalData($date, $statusScenarios);

        return $this->sortHistoricalData($data);
    }

    /**
     * @return array<int, array>
     */
    private function buildHistoricalData(\DateTimeInterface $date, array $statusScenarios): array
    {
        $applications = $this->applicationRepository->getActiveApplications();
        $data = [];

        foreach ($applications as $app) {
            $id = $app->getId();
            $scenariosData = $this->buildHistoricalScenarios($app, $date, $statusScenarios);

            $data[$id] = [
                'id' => $id,
                'nom' => $app->getNom(),
                'domaine' => $app->getDomaine()->getId(),
                'sousDomaine' => $app->getSousDomaine()->getId(),
                'zone' => $app->getZone()->getId(),
                'status' => StatusModel::statusApplication($scenariosData),
                'directions' => DirectionModel::application($app),
                'dynatrace' => $app->getFlagDynatrace(),
                'nubo' => $app->getFlagNubo(),
                'scenarios' => $scenariosData,
            ];
        }

        return $data;
    }

    /**
     * @return array<int, array>
     */
    private function buildHistoricalScenarios(
        Application $app,
        \DateTimeInterface $date,
        array $statusScenarios
    ): array {
        $scenarios = [];

        foreach ($app->getScenarios() as $scenario) {
            if (!$scenario->getFlagAffichage() || $scenario->getArchive()) {
                continue;
            }

            $status = $this->findScenarioStatus($scenario->getId(), $statusScenarios);

            $scenarios[$scenario->getId()] = [
                'id' => $scenario->getId(),
                'nom' => $scenario->getNom(),
                'versionSimulateur' => $scenario->getVersionSimulateur()?->getId(),
                'scenarioCom' => $scenario->getScenarioCom() ?: false,
                'meteo' => $scenario->getMeteo() ?: false,
                'lastExecution' => [
                    'dateExecution' => $date,
                    'statusExecution' => $status,
                ],
                'statusExecution' => $status,
                'id_scenario' => $scenario->getId(),
            ];
        }

        return $scenarios;
    }

    private function findScenarioStatus(int $scenarioId, array $statusScenarios): int
    {
        foreach ($statusScenarios as $status) {
            if (($status['id_scenario'] ?? null) === $scenarioId) {
                return $status['statusExecution'] ?? StatusModel::ITEM_INCONNU;
            }
        }

        return StatusModel::ITEM_INCONNU;
    }

    /**
     * @param array<int, array> $data
     * @return array<int, array>
     */
    private function sortHistoricalData(array $data): array
    {
        // Sort scenarios by name within each application
        array_walk($data, function (array &$app): void {
            $scenarios = array_values($app['scenarios']);
            usort($scenarios, static fn($a, $b) => $a['nom'] <=> $b['nom']);
            $app['scenarios'] = $scenarios;
        });

        // Sort applications by name (descending)
        usort($data, static fn($a, $b) => $b['nom'] <=> $a['nom']);

        return $data;
    }
}
