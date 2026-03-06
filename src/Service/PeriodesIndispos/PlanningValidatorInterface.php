<?php

namespace App\Service\PeriodesIndispos;

use App\Entity\Scenario;

/**
 * Interface pour valider les plannings
 * Respecte ISP
 */
interface PlanningValidatorInterface
{
    /**
     * Détermine si le calcul doit être effectué pour cette date
     */
    public function shouldCalculate(Scenario $scenario, \DateTime $date): bool;
}
