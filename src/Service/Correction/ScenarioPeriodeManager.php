<?php

namespace App\Service\Correction;

use App\Entity\Scenario;
use App\Service\ScenarioService;

class ScenarioPeriodeManager
{
    public function __construct(
        private ScenarioService $scenarioService
    ) {
    }

    public function prepareScenario(Scenario $scenario): Scenario
    {
        $originScenario = clone $scenario;
        $this->scenarioService->deleteFinPeriodeByScenario($scenario);

        return $originScenario;
    }

    public function finalizeScenario(Scenario $scenario, Scenario $originScenario): void
    {
        $this->scenarioService->updateFinPeriode($scenario, $originScenario);
    }
}
