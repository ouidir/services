<?php

namespace App\Service\PeriodesIndispos;

use App\Entity\Scenario;

interface PeriodeCalculatorInterface
{
    public function calculate(Scenario $scenario, \DateTime $dateDebut, \DateTime $dateFin): array;
}
