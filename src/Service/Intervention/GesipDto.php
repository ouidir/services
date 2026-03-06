<?php

namespace App\Service\Intervention;

use App\Entity\Gesip;

final class GesipDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $idGesip,
        public readonly string $titre,
        public readonly string $description,
        public readonly mixed $debut,
        public readonly mixed $fin,
        public readonly mixed $impactee,
        public readonly ?string $etat = null,
        public readonly mixed $principale = null,
    ) {
    }

    public static function fromEntity(Gesip $entity, bool $formatDates = false): self
    {
        return new self(
            id: $entity->getId(),
            idGesip: $entity->getIdGesip(),
            titre: $entity->getLibelle(),
            description: $entity->getDetail(),
            debut: $formatDates ? $entity->getDateDebut()->format('d/m/Y h:i:s') : $entity->getDateDebut(),
            fin: $formatDates ? $entity->getDateFin()->format('d/m/Y h:i:s') : $entity->getDateFin(),
            impactee: $entity->getListeAppli(),
            etat: $entity->getType(),
            principale: $entity->getPrincipale(),
        );
    }

    public function toArray(): array
    {
        $data = [
            'id'          => $this->id,
            'idGesip'     => $this->idGesip,
            'titre'       => $this->titre,
            'description' => $this->description,
            'debut'       => $this->debut,
            'fin'         => $this->fin,
            'impactee'    => $this->impactee,
            'principale'  => $this->principale,
        ];

        if ($this->etat !== null) {
            $data['etat'] = $this->etat;
        }

        return $data;
    }
}
