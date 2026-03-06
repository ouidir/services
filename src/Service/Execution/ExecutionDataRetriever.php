<?php

namespace App\Service\Execution;

use App\Entity\Scenario;
use App\Repository\Interface\ExecutionRepositoryInterface;

/**
 * Service responsable de la récupération des données d'exécution
 */
class ExecutionDataRetriever
{
    public function __construct(
        private readonly ExecutionRepositoryInterface $executionRepository
    ) {
    }

    /**
     * Récupère les exécutions par scénario et plage de dates
     */
    public function getExecutionsByScenario(
        Scenario $scenario,
        \DateTime $debut,
        \DateTime $fin,
        mixed $modeHeure
    ): array {
        return $this->executionRepository->findByScenarioDateFieldAnalyse(
            $scenario,
            $debut,
            $fin,
            $modeHeure
        );
    }

    /**
     * Récupère les exécutions pour une plage de dates
     */
    public function getExecutionsByDateRange(\DateTime $debut, \DateTime $fin): array
    {
        return $this->executionRepository->getExecutionsByDate($debut, $fin);
    }

    /**
     * Récupère les exécutions par date pour tendance
     */
    public function getExecutionsForTrend(\DateTime $date): array
    {
        return $this->executionRepository->findByDateField($date);
    }
}
