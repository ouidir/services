<?php

namespace App\Service;

use App\Entity\Scenario;
use App\Service\Scenario\ScenarioAnomalieProvider;
use App\Service\Scenario\ScenarioDuplicator;
use App\Service\Scenario\ScenarioFinder;
use App\Service\Scenario\ScenarioPeriodManager;
use App\Service\Scenario\ScenarioStatusProvider;
use App\Service\Scenario\SinapsDataExporter;

/**
 * Facade service for scenario operations
 */
class ScenarioService
{
    public function __construct(
        private ScenarioFinder $finder,
        private ScenarioStatusProvider $statusProvider,
        private ScenarioAnomalieProvider $anomalieProvider,
        private SinapsDataExporter $sinapsExporter,
        private ScenarioDuplicator $duplicator,
        private ScenarioPeriodManager $periodManager
    ) {
    }

    // Finder operations
    public function find(int|Scenario $id): ?Scenario
    {
        return $this->finder->findById($id);
    }

    public function findOneByIdentifiant(string $identifiant): ?Scenario
    {
        return $this->finder->findByIdentifiant($identifiant);
    }

    public function getAutoIncrementId(): int
    {
        return $this->finder->getNextAutoIncrementId();
    }

    // Status operations
    public function getStatusByIdScenario(int $scenarioId): int
    {
        return $this->statusProvider->getStatusByScenarioId($scenarioId);
    }

    public function getStatusScenario(): array
    {
        return $this->statusProvider->getAllScenarioStatus();
    }

    // Anomalie operations
    public function getAnomalies(): array
    {
        return $this->anomalieProvider->getAnomalies();
    }

    // Export operations
    public function getSinapsData(): array
    {
        return $this->sinapsExporter->export();
    }

    // Duplication operations
    public function duplicate(Scenario $scenario): Scenario
    {
        return $this->duplicator->duplicate($scenario);
    }

    // Period management operations
    public function deleteFinPeriodeByScenario(Scenario $scenario): void
    {
        $this->periodManager->deleteEndPeriod($scenario);
    }

    public function updateFinPeriode(Scenario $scenario, Scenario $originScenario): void
    {
        $this->periodManager->updateEndPeriod($scenario, $originScenario);
    }
}
