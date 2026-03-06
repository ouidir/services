<?php

namespace App\Service\Correction;

final readonly class DataDto
{
    public function __construct(
        public array $scenarios,
        public \DateTimeImmutable $dateDebut,
        public \DateTimeImmutable $dateFin,
        public array $codeACorriger,
        public int $codeAAffecter,
        public ?string $commentaireModifie = null
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->scenarios)) {
            throw new \InvalidArgumentException('La liste des scénarios ne peut pas être vide');
        }

        if ($this->dateFin < $this->dateDebut) {
            throw new \InvalidArgumentException('La date de fin doit être postérieure à la date de début');
        }

        if (empty($this->codeACorriger)) {
            throw new \InvalidArgumentException('Les codes à corriger ne peuvent pas être vides');
        }
    }

    public function getDateDebutMutable(): \DateTime
    {
        return \DateTime::createFromImmutable($this->dateDebut);
    }

    public function getDateFinMutable(): \DateTime
    {
        return \DateTime::createFromImmutable($this->dateFin);
    }
}
