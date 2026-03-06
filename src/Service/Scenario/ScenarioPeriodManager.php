<?php

namespace App\Service\Scenario;

use App\Entity\Scenario;
use App\Repository\ScenarioRepository;
use Doctrine\ORM\EntityManagerInterface;

class ScenarioPeriodManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ScenarioRepository $scenarioRepository
    ) {
    }

    public function deleteEndPeriod(Scenario $scenario): void
    {
        $scenario->setDateFinPeriode(null);
        $this->persist($scenario);
    }

    public function updateEndPeriod(Scenario $scenario, Scenario $originScenario): void
    {
        // Refresh from DB to avoid stale data
        $refreshedScenario = $this->scenarioRepository->find($scenario->getId());

        if (!$refreshedScenario) {
            throw new \InvalidArgumentException(
                sprintf('Scenario with id %s not found', $scenario->getId())
            );
        }

        if ($this->shouldUpdateEndDate($refreshedScenario, $originScenario)) {
            $refreshedScenario->setDateFinPeriode($originScenario->getDateFinPeriode());
            $this->persist($refreshedScenario);
        }
    }

    private function shouldUpdateEndDate(Scenario $scenario, Scenario $origin): bool
    {
        $scenarioEndDate = $scenario->getDateFinPeriode();
        $originEndDate = $origin->getDateFinPeriode();

        return $scenarioEndDate !== null
            && $originEndDate !== null
            && $scenarioEndDate >= $originEndDate;
    }

    private function persist(Scenario $scenario): void
    {
        $this->entityManager->persist($scenario);
        $this->entityManager->flush();
    }
}
