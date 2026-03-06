<?php

namespace App\Service\Correction;

use App\Entity\Scenario;

interface CorrectionStrategyInterface
{
    public function correct(Scenario $scenario, DataDto $data): void;
}
