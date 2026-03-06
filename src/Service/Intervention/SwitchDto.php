<?php

namespace App\Service\Intervention;

use App\Entity\SSwitch;

final class SwitchDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $idSwitch,
        public readonly string $titre,
        public readonly string $description,
        public readonly mixed $debut,
        public readonly mixed $fin,
        public readonly string $impactee = '',
    ) {
    }

    public static function fromEntity(SSwitch $entity, bool $formatDates = false): self
    {
        return new self(
            id: $entity->getId(),
            idSwitch: $entity->getIdSwitch(),
            titre: $entity->getTitre(),
            description: $entity->getTexte(),
            debut: $formatDates ? $entity->getDateDebut()->format('d/m/Y h:i:s') : $entity->getDateDebut(),
            fin: $formatDates ? $entity->getDateFin()->format('d/m/Y h:i:s') : $entity->getDateFin(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'idSwitch'    => $this->idSwitch,
            'titre'       => $this->titre,
            'description' => $this->description,
            'debut'       => $this->debut,
            'fin'         => $this->fin,
            'impactee'    => $this->impactee,
        ];
    }
}
