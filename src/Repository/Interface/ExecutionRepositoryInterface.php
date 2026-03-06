<?php

namespace App\Repository\Interface;

use App\Entity\Scenario;

interface ExecutionRepositoryInterface
{
    public function findByScenarioDateField(Scenario $scenario, \DateTime $start, \DateTime $end): array;
}
