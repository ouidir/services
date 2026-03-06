<?php

namespace App\Service\PeriodesIndispos;

/**
 * Data Transfer Object pour une période d'indisponibilité
 * Respecte SRP : uniquement transport de données
 */
readonly class PeriodeIndispoDto
{
    public function __construct(
        public \DateTime $debut,
        public \DateTime $fin,
        public int $etat,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->debut > $this->fin) {
            throw new \InvalidArgumentException("La date de début doit être antérieure à la date de fin");
        }
    }

    public function getDureeEnSecondes(): int
    {
        return $this->fin->getTimestamp() - $this->debut->getTimestamp();
    }
}
