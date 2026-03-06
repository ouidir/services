<?php

namespace App\Service\Execution;

use App\Entity\Scenario;
use App\Repository\Interface\ExecutionRepositoryInterface;

/**
 * Service responsable de la mise à jour des statuts d'exécution
 */
class ExecutionStatusUpdater
{
    public function __construct(
        private readonly ExecutionRepositoryInterface $executionRepository
    ) {
    }

    /**
     * Met à jour le statut et le commentaire des exécutions correspondant aux critères
     *
     * @return int Nombre d'exécutions modifiées
     */
    public function updateExecutionStatus(
        Scenario $scenario,
        \DateTime $dateDebut,
        \DateTime $dateFin,
        array $statusToCorrect,
        string $newStatus,
        ?string $newComment = null
    ): int {
        $executions = $this->executionRepository->findByScenarioDateFieldStatus(
            $scenario,
            $dateDebut,
            $dateFin,
            $statusToCorrect
        );

        if (empty($executions)) {
            return 0;
        }

        $this->applyStatusChanges($executions, $newStatus, $newComment);
        $this->executionRepository->saveAll($executions);

        return count($executions);
    }

    /**
     * Applique les changements de statut et commentaire aux exécutions
     */
    private function applyStatusChanges(
        array $executions,
        string $newStatus,
        ?string $newComment
    ): void {
        foreach ($executions as $execution) {
            $execution->setStatus($newStatus);

            if ($newComment !== null) {
                $execution->setCommentaire($newComment);
            }
        }
    }
}
