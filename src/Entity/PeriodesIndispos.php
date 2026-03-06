<?php

namespace App\Entity;

class PeriodesIndispos
{
    private ?Scenario $idScenario = null;
    private ?\DateTime $debutPeriode = null;
    private ?\DateTime $finPeriode = null;
    private int $duree = 0;
    private int $etatInitial = 0;

    public function setIdScenario(Scenario $scenario): void
    {
        $this->idScenario = $scenario;
    }

    public function setDebutPeriode(\DateTime $date): void
    {
        $this->debutPeriode = $date;
    }

    public function setFinPeriode(\DateTime $date): void
    {
        $this->finPeriode = $date;
    }

    public function setDuree(int $duree): void
    {
        $this->duree = $duree;
    }

    public function setEtatInitial(int $etat): void
    {
        $this->etatInitial = $etat;
    }
}
