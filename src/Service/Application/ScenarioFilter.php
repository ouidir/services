<?php

namespace App\Service\Application;

use App\Entity\Application;
use App\Entity\Scenario;

/**
 * Filters scenarios based on visibility rules.
 */
class ScenarioFilter
{
    public function filterVisibleScenarios(Application $application): Application
    {
        $toRemove = [];

        foreach ($application->getScenarios() as $scenario) {
            if ($this->shouldRemoveScenario($scenario)) {
                $toRemove[] = $scenario;
            }
        }

        foreach ($toRemove as $scenario) {
            $application->removeScenario($scenario);
        }

        return $application;
    }

    private function shouldRemoveScenario(Scenario $scenario): bool
    {
        return $scenario->getArchive() || !$scenario->getFlagAffichage();
    }
}
