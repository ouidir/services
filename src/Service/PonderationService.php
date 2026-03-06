<?php

namespace App\Service;

use App\Admin\Form\Data\PonderationDto;
use App\Entity\Selection;
use App\Service\Ponderation\PonderationAggregator;
use App\Service\Ponderation\PonderationPersister;
use App\Service\Ponderation\PonderationStatisticsService;
use App\Service\Ponderation\PonderationValidator;
use App\Service\Ponderation\SelectionManager;

/**
 * Facade service for ponderation operations
 */
final readonly class PonderationService
{
    public function __construct(
        private PonderationValidator $validator,
        private PonderationPersister $persister,
        private PonderationAggregator $aggregator,
        private SelectionManager $selectionManager,
        private PonderationStatisticsService $statisticsService
    ) {
    }

    // Validation
    public function validatePonderationData(PonderationDto $dto): array
    {
        return $this->validator->validate($dto);
    }

    public function isCompatible($scenarioPlaning, array $jours, array $ponderationPlaning): bool
    {
        return $this->validator->isCompatible($scenarioPlaning, $jours, $ponderationPlaning);
    }

    // Persistence
    public function save(?array $scenarios, int $jours, array $ponderationPlaning): void
    {
        $this->persister->saveFromScenarios($scenarios, $jours, $ponderationPlaning);
    }

    public function getFirstExistPonderationFromScenarios(?array $scenarios)
    {
        return $this->persister->findFirstExistingFromScenarios($scenarios);
    }

    // Selection management
    public function getSelectionById(int $id): Selection
    {
        return $this->selectionManager->getById($id);
    }

    public function getSelections(): array
    {
        return $this->selectionManager->getAll();
    }

    public function saveSelections(array $data): int
    {
        return $this->selectionManager->save($data);
    }

    public function removeSelection(int $idSelection): void
    {
        $this->selectionManager->remove($idSelection);
    }

    public function getPonderationsForm(Selection $selection): array
    {
        return $this->selectionManager->getPonderationsForm($selection);
    }

    public function savePonderationsForSelection(Selection $selection, array $ponderationsForm): void
    {
        $this->selectionManager->savePonderations($selection, $ponderationsForm);
    }

    // Aggregation
    public function executions(Selection $selection, \DateTime $dateDebut, \DateTime $dateFin): array
    {
        return $this->aggregator->aggregateExecutions($selection, $dateDebut, $dateFin);
    }

    public function ponderationAppTaux(array $data): array
    {
        return $this->aggregator->calculateApplicationRates($data);
    }

    // Statistics
    public function nbrApplicationsPonderees(): array
    {
        return $this->statisticsService->getApplicationsCount();
    }

    public function nbrScenariosPonderees(): array
    {
        return $this->statisticsService->getScenariosCount();
    }
}
