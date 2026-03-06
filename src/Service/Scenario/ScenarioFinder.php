<?php

namespace App\Service\Scenario;

use App\Entity\Scenario;
use App\Repository\ScenarioRepository as DoctrineScenarioRepository;

class ScenarioFinder
{
    public function __construct(
        private DoctrineScenarioRepository $scenarioRepository
    ) {
    }

    public function findById(int|Scenario $id): ?Scenario
    {
        return $this->scenarioRepository->find($id);
    }

    public function findByIdentifiant(string $identifiant): ?Scenario
    {
        return $this->scenarioRepository->findOneBy(['identifiant' => $identifiant]);
    }

    public function getNextAutoIncrementId(): int
    {
        return $this->scenarioRepository->getAutoIncrementId();
    }
}
