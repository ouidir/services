<?php

namespace App\Service;

use App\Service\Evolution\EvolutionDataProcessor;
use App\Service\Evolution\EvolutionDataRetriever;
use App\Service\Evolution\EvolutionExecutionDetailsBuilder;
use App\Service\Evolution\EvolutionJobPersister;
use App\Service\Evolution\EvolutionMessageDispatcher;

/**
 * Service principal de coordination pour les évolutions
 * Respecte le principe de Single Responsibility en déléguant aux services spécialisés
 */
class EvolutionService
{
    public function __construct(
        private readonly EvolutionJobPersister $persister,
        private readonly EvolutionDataProcessor $processor,
        private readonly EvolutionDataRetriever $retriever,
        private readonly EvolutionExecutionDetailsBuilder $detailsBuilder,
        private readonly EvolutionMessageDispatcher $messageDispatcher
    ) {
    }

    /**
     * Traite et sauvegarde les données d'évolution pour une date
     */
    public function consume(string $dateString): void
    {
        $date = new \DateTime($dateString);
        $data = $this->processor->processEvolutionForDate($date);

        $this->persister->saveEvolutionData(
            $date,
            $data['S'] ?? [],
            $data['details'] ?? []
        );
    }

    /**
     * Dispatch les messages d'évolution
     */
    public function dispatchEvolutionJobs(\DateTime $startDate): void
    {
        $this->messageDispatcher->dispatchEvolutionMessages($startDate);
    }

    /**
     * Récupère les détails d'exécution pour un job
     */
    public function getExecutionDetailsById(int $jobId): array
    {
        return $this->detailsBuilder->buildExecutionDetails($jobId);
    }

    /**
     * Récupère les données d'évolution récentes (36h)
     */
    public function getRecentEvolutions(): array
    {
        return $this->retriever->getRecentEvolutionsData();
    }

    /**
     * Récupère les données d'évolution par plage de dates
     */
    public function getEvolutionsByDateRange(\DateTime $debut, \DateTime $fin): array
    {
        return $this->retriever->getEvolutionsByDateRange($debut, $fin);
    }

    /**
     * Récupère les données d'évolution filtrées par applications
     */
    public function getEvolutionsForApplications(array $applicationIds): array
    {
        $evolutionsData = $this->retriever->getRecentEvolutionsData();

        return $this->processor->filterByApplications($evolutionsData, $applicationIds);
    }

    /**
     * Nettoie les anciennes données d'évolution (36h)
     */
    public function clearOldEvolutions(): void
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT36H'));

        $this->persister->clearOldData($date);
    }

    /**
     * Nettoie les données pour recalcul
     */
    public function clearForRecalculation(\DateTime $date): void
    {
        $this->persister->clearForRecalculation($date);
    }

    /**
     * Sauvegarde manuelle des données d'évolution
     */
    public function saveEvolutionExecution(\DateTime $date, array $status, array $details): void
    {
        $date->setTime((int) $date->format('H'), (int) $date->format('i'), 0);

        $this->persister->saveEvolutionData($date, $status, $details);
    }
}
