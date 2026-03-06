<?php

namespace App\Service\PeriodesIndispos;

use App\Entity\Scenario;

interface PeriodeWriterInterface
{
    public function writePeriodes(Scenario $scenario, array $periodes): void;
}
