<?php

namespace App\Service\Statistics;

use App\Service\Statistics\DTO\TauxCalculationConfig;

interface TauxIndispoServiceInterface
{
    public function getByScenarios(array $scenarios, \DateTime $date): array;

    public function getByApplications(
        array $applications,
        \DateTime $date,
        TauxCalculationConfig $config
    ): array;

    public function getByScenarioItem(int $scenarioId, string $key, \DateTime $date): array;
}
