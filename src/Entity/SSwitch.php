<?php

namespace App\Entity;

class SSwitch
{
    private int $id = 0;
    private string $idSwitch = '';
    private string $titre = '';
    private string $texte = '';
    private ?\DateTime $dateDebut = null;
    private ?\DateTime $dateFin = null;
    private ?Application $application = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdSwitch(): string
    {
        return $this->idSwitch;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getTexte(): string
    {
        return $this->texte;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }
}
