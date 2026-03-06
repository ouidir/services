<?php

namespace App\Service\Evolution;

use App\Model\StatusModel;
use App\Repository\ScenarioRepository;

/**
 * Service responsable du traitement et de l'agrégation des données d'évolution
 */
class EvolutionDataProcessor
{
    public function __construct(
        private readonly ScenarioRepository $scenarioRepository
    ) {
    }

    /**
     * Traite les données d'évolution pour une date donnée
     */
    public function processEvolutionForDate(\DateTime $date): array
    {
        $statusScenarios = $this->scenarioRepository->getStatusScenarioAnterieur($date);

        return StatusModel::evolutionAgregate($statusScenarios);
    }

    /**
     * Filtre les données d'évolution par applications
     */
    public function filterByApplications(array $evolutionsData, array $applicationIds): array
    {
        $result = [];

        foreach ($evolutionsData as $evolutionJob) {
            $item = $this->buildEvolutionItem($evolutionJob, $applicationIds);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Construit un item d'évolution filtré par applications
     */
    private function buildEvolutionItem($evolutionJob, array $applicationIds): array
    {
        $item = [
            'id' => $evolutionJob->getId(),
            'date' => $evolutionJob->getDate(),
            'data' => ["C" => 0, "D" => 0, "H" => 0, "I" => 0, "O" => 0, "R" => 0]
        ];

        foreach ($evolutionJob->getDetails() as $status => $applications) {
            $filteredApps = [];
            $count = 0;

            foreach ($applications as $appId => $scenarios) {
                if (in_array($appId, $applicationIds)) {
                    $filteredApps[$appId] = $scenarios;
                    $count += count($scenarios);
                }
            }

            if ($count > 0) {
                $item['data'][$status] = $count;
                $item['details'][$status] = $filteredApps;
            }
        }

        return $item;
    }
}
