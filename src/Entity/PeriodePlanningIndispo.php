<?php

namespace App\Entity;

class PeriodePlanningIndispo
{
    private int $jour = 1;
    private ?\DateTime $heureDebut = null;
    private ?\DateTime $heureFin = null;

    public function getJour(): int
    {
        return $this->jour;
    }

    public function getHeureDebut(): ?\DateTime
    {
        return $this->heureDebut;
    }

    public function getHeureFin(): ?\DateTime
    {
        return $this->heureFin;
    }
}
