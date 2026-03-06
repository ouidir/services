<?php

namespace App\Entity;

class Scenario
{
    private int $pasExecution = 60;
    private ?\DateTime $dateFinPeriode = null;
    private ?\DateTime $dateActivation = null;
    private bool $flagActif = true;
    private ?PlanningIndispo $planningIndispo = null;

    public function getPasExecution(): int
    {
        return $this->pasExecution;
    }

    public function getDateFinPeriode(): ?\DateTime
    {
        return $this->dateFinPeriode;
    }

    public function setDateFinPeriode(\DateTime $date): void
    {
        $this->dateFinPeriode = $date;
    }

    public function getDateActivation(): ?\DateTime
    {
        return $this->dateActivation;
    }

    public function getFlagActif(): bool
    {
        return $this->flagActif;
    }

    public function getPlanningIndispo(): ?PlanningIndispo
    {
        return $this->planningIndispo;
    }
}
