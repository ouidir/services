<?php

namespace App\Service\Ponderation;

use App\Entity\Ponderation;
use App\Repository\PonderationRepository;
use App\Repository\ScenarioRepository;
use Doctrine\ORM\EntityManagerInterface;

class PonderationPersister
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PonderationRepository $ponderationRepository,
        private ScenarioRepository $scenarioRepository
    ) {
    }

    public function saveFromScenarios(?array $scenarios, int $jours, array $ponderationPlaning): void
    {
        if (!$scenarios) {
            return;
        }

        foreach ($scenarios as $scenario) {
            $this->saveForScenario($scenario, $jours, $ponderationPlaning);
        }

        $this->entityManager->flush();
    }

    private function saveForScenario(object $scenario, int $jours, array $ponderationPlaning): void
    {
        $ponderation = $this->ponderationRepository
            ->findOneBy(['idScenario' => $scenario->getId()]);

        $scenarioEntity = $this->scenarioRepository->find($scenario->getId());

        if (!$ponderation) {
            $ponderation = new Ponderation();
            $ponderation->setIdScenario($scenarioEntity);
            $ponderation->setDateCreation(new \DateTime());
        }

        $ponderation->setJours($jours);
        $ponderation->setHeures(array_values($ponderationPlaning));
        $ponderation->setDateDerniereMaj(new \DateTime());

        $this->entityManager->persist($ponderation);
    }

    public function findFirstExistingFromScenarios(?array $scenarios): ?Ponderation
    {
        if (!$scenarios) {
            return null;
        }

        foreach ($scenarios as $scenario) {
            $ponderation = $this->ponderationRepository
                ->findOneBy(['idScenario' => $scenario]);
            if ($ponderation) {
                return $ponderation;
            }
        }

        return null;
    }
}
