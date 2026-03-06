<?php

namespace App\Service\Application;

use App\Entity\Application;
use App\Entity\Scenario;
use App\Model\DirectionModel;
use App\Model\StatusModel;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes application entities with computed data.
 */
class ApplicationNormalizer
{
    public function __construct(
        private EntityManagerInterface $em,
        private NormalizerInterface $normalizer,
        private ApplicationRepository $applicationRepository,
        private AnomalyCounter $anomalyCounter,
    ) {
    }

    public function normalizeApplication(int $applicationId): array
    {
        $application = $this->applicationRepository->find($applicationId);

        if (!$application) {
            throw new \InvalidArgumentException("Application with ID {$applicationId} not found.");
        }

        $now = new \DateTime();

        // Fetch scenario statuses
        $statusScenarios = $this->em->getRepository(Scenario::class)
            ->getStatusScenario($now, false, $applicationId);

        // Process scenarios and compute data
        [$scenariosToSerialize, $transverseImpacts] = $this->processScenarios(
            $application,
            $statusScenarios
        );

        // Update computed fields
        $this->updateComputedFields($application, $statusScenarios, $transverseImpacts, $now);

        // Normalize
        $normalized = $this->normalizer->normalize($application, null, ['groups' => 'level1']);

        // Replace scenarios with processed ones
        $normalized['scenarios'] = array_map(
            fn(Scenario $scenario) => $this->normalizer->normalize($scenario, null, ['groups' => 'level1']),
            $scenariosToSerialize
        );

        return $normalized;
    }

    /**
     * @return array{0: array<Scenario>, 1: array<string, string>}
     */
    private function processScenarios(Application $application, array $statusScenarios): array
    {
        $scenariosToSerialize = [];
        $transverseImpacts = [];

        foreach ($application->getScenarios() as $scenario) {
            if ($scenario->getArchive() || !$scenario->getFlagAffichage()) {
                continue;
            }

            $this->updateScenarioStatus($scenario, $statusScenarios);
            $this->collectTransverseImpacts($scenario, $transverseImpacts);

            $scenariosToSerialize[] = $scenario;
        }

        return [$scenariosToSerialize, $transverseImpacts];
    }

    private function updateScenarioStatus(Scenario $scenario, array $statusScenarios): void
    {
        $status = StatusModel::ITEM_INCONNU;

        foreach ($statusScenarios as $statusData) {
            if (($statusData['id_scenario'] ?? null) === $scenario->getId()) {
                $status = $statusData['statusExecution'] ?? StatusModel::ITEM_INCONNU;
                break;
            }
        }

        $lastExecution = $scenario->getLastExecution();
        if ($lastExecution !== null) {
            $lastExecution->setStatusExecution($status);
        }
    }

    /**
     * @param array<string, string> $impacts
     */
    private function collectTransverseImpacts(Scenario $scenario, array &$impacts): void
    {
        foreach ($scenario->getBriquesTransverses() as $briquette) {
            $impacts[(string) $briquette->getId()] = $briquette->getNom();
        }
    }

    private function updateComputedFields(
        Application $application,
        array $statusScenarios,
        array $transverseImpacts,
        \DateTime $now
    ): void {
        $application->setStatus(StatusModel::statusApplication($statusScenarios));
        $application->setDirections(DirectionModel::application($application));
        $application->setTransverseImpactee($transverseImpacts);

        $this->anomalyCounter->computeAnomalies($application, $now);
    }
}
