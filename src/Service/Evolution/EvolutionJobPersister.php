<?php

namespace App\Service\Evolution;

use App\Entity\EvolutionJob;
use App\Repository\Interface\EvolutionJobRepositoryInterface;

/**
 * Service responsable de la persistance des données d'évolution
 */
class EvolutionJobPersister
{
    public function __construct(
        private readonly EvolutionJobRepositoryInterface $evolutionJobRepository
    ) {
    }

    /**
     * Sauvegarde ou met à jour les données d'évolution pour une date donnée
     */
    public function saveEvolutionData(\DateTime $date, array $statusData, array $details): void
    {
        $evolutionJob = $this->evolutionJobRepository->findOneByDate($date);

        if (!$evolutionJob) {
            $evolutionJob = new EvolutionJob();
            $evolutionJob->setDate($date);
        }

        $evolutionJob->setData($statusData);
        $evolutionJob->setDetails($details);

        $this->evolutionJobRepository->save($evolutionJob);
    }

    /**
     * Nettoie les données d'évolution antérieures à une date
     */
    public function clearOldData(\DateTime $beforeDate): void
    {
        $this->evolutionJobRepository->clearEvolutionsData($beforeDate);
    }

    /**
     * Nettoie les données pour recalcul
     */
    public function clearForRecalculation(\DateTime $date): void
    {
        $this->evolutionJobRepository->clearEvolutionsDataForRecalcule($date);
    }
}
