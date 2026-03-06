<?php

namespace App\Service\Evolution;

use App\Entity\Application;
use App\Entity\Scenario;
use App\Repository\EvolutionJobRepository;
use App\Service\ApplicationService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service responsable de la construction des détails d'exécution des évolutions
 */
class EvolutionExecutionDetailsBuilder
{
    public function __construct(
        private readonly EvolutionJobRepository $evolutionJobRepository,
        private readonly ApplicationService $applicationService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Récupère les détails d'exécution pour un job d'évolution
     */
    public function buildExecutionDetails(int $jobId): array
    {
        $job = $this->evolutionJobRepository->find($jobId);

        if (!$job) {
            return [];
        }

        $data = [];

        foreach ($job->getDetails() as $status => $applications) {
            foreach ($applications as $applicationId => $scenarios) {
                $scenarioData = $this->getScenarioMetadata($applicationId);
                $applicationData = $this->getApplicationData($applicationId, $scenarios);

                $transverse = (int) $applicationData['transverse'];
                $data[$transverse][$status][$applicationId] = $this->buildApplicationItem(
                    $applicationData,
                    $scenarios,
                    $scenarioData
                );
            }
        }

        return $data;
    }

    /**
     * Récupère les métadonnées du premier scénario trouvé
     */
    private function getScenarioMetadata(int $applicationId): array
    {
        $repoApp = $this->entityManager->getRepository(Application::class);
        $meteo = false;
        $com = false;

        foreach ($repoApp->find($applicationId)->getScenarios() as $scenarioId) {
            $scenario = $this->entityManager->getRepository(Scenario::class)->find($scenarioId);

            if ($scenario) {
                // :? => ($x) ? true : $objetScenario->getXxx()
                $meteo = ($meteo) ?: $scenario->getMeteo();
                $com = ($com) ?: $scenario->getScenarioCom();
            }
        }

        return ['meteo' => $meteo, 'com' => $com];
    }

    /**
     * Récupère les données de l'application depuis le cache
     */
    private function getApplicationData(int $applicationId, array $scenarios): array
    {
        $application = $this->applicationService->getCachedApplicationById($applicationId);
        $application['scenarioCount'] = count($scenarios);

        return $application;
    }

    /**
     * Construit l'item d'application avec toutes ses données
     */
    private function buildApplicationItem(array $application, array $scenarios, array $scenarioData): array
    {
        return [
            'application' => $application['nom'],
            'id' => $application['id'],
            'nbr' => $application['scenarioCount'] . '/' . count($application['scenarios']),
            'nubo' => (bool) $application['flagNubo'],
            'meteo' => $scenarioData['meteo'],
            'com' => $scenarioData['com'],
        ];
    }
}
