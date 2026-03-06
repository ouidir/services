<?php

namespace App\Service\Ponderation;

use App\Repository\ApplicationRepository;
use App\Repository\PonderationRepository;
use App\Repository\ScenarioRepository;

class PonderationStatisticsService
{
    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
        private readonly ScenarioRepository $scenarioRepository,
        private readonly PonderationRepository $ponderationRepository
    ) {
    }

    public function getApplicationsCount()
    {
        $countApplications = $this->applicationRepository->getCountActiveApplicationWithActiveScenario();

        $countPonderations = $this->applicationRepository->getCountPonderations();

        return \compact(['countPonderations', 'countApplications']);
    }

    public function getScenariosCount()
    {
        $countScenarios = count($this->scenarioRepository->findBy(['archive' => false]));

        $countPonderations = count($this->ponderationRepository->findAll());

        return \compact(['countPonderations', 'countScenarios']);
    }
}
