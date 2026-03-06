<?php

namespace App\Entity;

class PlanningIndispo
{
    private array $periodes = [];

    public function getPeriodes(): array
    {
        return $this->periodes;
    }

    public function setPeriodes(array $periodes): void
    {
        $this->periodes = $periodes;
    }
}
