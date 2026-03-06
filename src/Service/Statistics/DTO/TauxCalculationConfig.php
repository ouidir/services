<?php

namespace App\Service\Statistics\DTO;

/**
 * Configuration pour le calcul des taux
 * Respecte SRP : Transport de données uniquement
 */
readonly class TauxCalculationConfig
{
    public function __construct(
        public int $vue = 1,
        public int $periode = 4,
        public bool $moyenne = false,
        public bool $archive = false,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->vue < 1 || $this->vue > 5) {
            throw new \InvalidArgumentException("Vue doit être entre 1 et 5");
        }

        if ($this->periode < 1) {
            throw new \InvalidArgumentException("Période doit être positive");
        }
    }

    public function isVueDefault(): bool
    {
        return $this->vue === 1;
    }

    public function isVueQuotidienne(): bool
    {
        return $this->vue === 2;
    }

    public function isVueHebdomadaire(): bool
    {
        return $this->vue === 3;
    }

    public function isVueMensuelle(): bool
    {
        return $this->vue === 4;
    }

    public function isVueAnnuelle(): bool
    {
        return $this->vue === 5;
    }
}
