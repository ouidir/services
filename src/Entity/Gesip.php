<?php

namespace App\Entity;

class Gesip
{
    private int $id = 0;
    private string $idGesip = '';
    private string $libelle = '';
    private string $detail = '';
    private ?\DateTime $dateDebut = null;
    private ?\DateTime $dateFin = null;
    private mixed $listeAppli = null;
    private ?string $type = null;
    private mixed $principale = null;
    private ?Application $application = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdGesip(): string
    {
        return $this->idGesip;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function getListeAppli(): mixed
    {
        return $this->listeAppli;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getPrincipale(): mixed
    {
        return $this->principale;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }
}
