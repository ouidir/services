<?php

namespace App\Service\Planning;

interface PeriodeComparatorInterface
{
    /**
     * Compare deux ensembles de périodes
     *
     * @return bool true si différent, false si identique
     */
    public function isDifferent(array $periodes1, array $periodes2): bool;
}
