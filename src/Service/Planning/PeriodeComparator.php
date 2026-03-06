<?php

namespace App\Service\Planning;

use App\Entity\Periode;

class PeriodeComparator implements PeriodeComparatorInterface
{
    public function isDifferent(array $periodes1, array $periodes2): bool
    {
        if (count($periodes1) !== count($periodes2)) {
            return true;
        }

        foreach ($periodes1 as $periode) {
            if (!$this->findMatchingPeriode($periode, $periodes2)) {
                return true;
            }
        }

        return false;
    }

    private function findMatchingPeriode(array $searchPeriode, array $periodes): bool
    {
        $hDebut = (new \DateTime($searchPeriode['heureDebut']))->format('H:i:s');
        $hFin = (new \DateTime($searchPeriode['heureFin']))->format('H:i:s');

        foreach ($periodes as $periode) {
            if ($this->isPeriodeMatching($periode, $searchPeriode['jour'], $hDebut, $hFin)) {
                return true;
            }
        }

        return false;
    }

    private function isPeriodeMatching(
        Periode $periode,
        int $jour,
        string $heureDebut,
        string $heureFin
    ): bool {
        return $periode->getJour() === $jour
            && $periode->getHeureDebut()->format('H:i:s') === $heureDebut
            && $periode->getHeureFin()->format('H:i:s') === $heureFin;
    }
}
