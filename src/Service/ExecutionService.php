<?php

namespace App\Service;

use App\Entity\Scenario;
use App\Service\Execution\ExecutionDataRetriever;
use App\Service\Execution\ExecutionDetailsBuilder;
use App\Service\Execution\ExecutionStatusUpdater;
use App\Service\Execution\TrendCalculator;

/**
 * Service principal de coordination pour les exécutions
 * Respecte le principe de Single Responsibility en déléguant aux services spécialisés
 */
class ExecutionService
{
    public function __construct(
        private readonly ExecutionDataRetriever $dataRetriever,
        private readonly ExecutionDetailsBuilder $detailsBuilder,
        private readonly ExecutionStatusUpdater $statusUpdater,
        private readonly TrendCalculator $trendCalculator
    ) {
    }

    /**
     * Récupère les exécutions par scénario et plage de dates
     */
    public function executionByScenario(
        Scenario $scenario,
        \DateTime $debut,
        \DateTime $fin,
        mixed $modeHeure
    ): array {
        return $this->dataRetriever->getExecutionsByScenario(
            $scenario,
            $debut,
            $fin,
            $modeHeure
        );
    }

    /**
     * Récupère les détails d'exécution avec briques par scénario
     */
    public function executionDetailsByScenario(
        Scenario $scenario,
        \DateTime $debut,
        \DateTime $fin
    ): array {
        return $this->detailsBuilder->buildExecutionDetails(
            $scenario,
            $debut,
            $fin
        );
    }

    /**
     * Récupère les tendances pour une date donnée (avec cache)
     */
    public function getTendance(\DateTime $date): mixed
    {
        return $this->trendCalculator->calculateTrend($date);
    }

    /**
     * Récupère les exécutions pour une plage de dates
     */
    public function getExecutionsByDate(\DateTime $debut, \DateTime $fin): array
    {
        return $this->dataRetriever->getExecutionsByDateRange($debut, $fin);
    }

    /**
     * Met à jour le statut et commentaire des exécutions
     *
     * @return int Nombre d'exécutions modifiées
     */
    public function updateExecution(
        Scenario $scenario,
        \DateTime $dateDeb,
        \DateTime $dateFin,
        array $codeACorriger,
        string $codeAAffecter,
        ?string $commentaireModifie = null
    ): int {
        return $this->statusUpdater->updateExecutionStatus(
            $scenario,
            $dateDeb,
            $dateFin,
            $codeACorriger,
            $codeAAffecter,
            $commentaireModifie
        );
    }
}
