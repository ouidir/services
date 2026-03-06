<?php

namespace App\Service\Execution;

use App\Entity\Scenario;
use App\Repository\Interface\BriqueExecutionRepositoryInterface;

/**
 * Service responsable de la construction des détails d'exécution avec briques
 */
class ExecutionDetailsBuilder
{
    public function __construct(
        private readonly BriqueExecutionRepositoryInterface $briqueExecutionRepository
    ) {
    }

    /**
     * Construit les détails d'exécution par scénario avec leurs briques
     */
    public function buildExecutionDetails(
        Scenario $scenario,
        \DateTime $debut,
        \DateTime $fin
    ): array {
        $briques = $this->briqueExecutionRepository->findByScenarioDateField(
            $scenario,
            $debut,
            $fin
        );

        return $this->groupBriquesByExecution($briques);
    }

    /**
     * Groupe les briques par exécution
     */
    private function groupBriquesByExecution(array $briques): array
    {
        $data = [];

        foreach ($briques as $brique) {
            $execution = $brique->getIdExecution();
            $executionId = $execution->getId();

            if (!isset($data[$executionId])) {
                $data[$executionId] = [
                    'id' => $executionId,
                    'date' => $execution->getDate(),
                    'duree' => $execution->getDuree(),
                    'briques' => []
                ];
            }

            $data[$executionId]['briques'][] = $brique;
        }

        return $data;
    }
}
