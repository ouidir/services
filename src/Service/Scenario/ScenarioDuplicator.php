<?php

namespace App\Service\Scenario;

use App\Entity\Scenario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class ScenarioDuplicator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function duplicate(Scenario $scenario): Scenario
    {
        $duplicate = clone $scenario;
        $this->prepareForDuplication($duplicate, $scenario);

        $this->entityManager->persist($duplicate);
        $this->entityManager->flush();

        return $duplicate;
    }

    private function prepareForDuplication(Scenario $duplicate, Scenario $original): void
    {
        $duplicate->setNom('Copie ' . $original->getNom());
        $duplicate->setIdentifiant((string) Uuid::v6());
        $duplicate->setInjecteur(null);
        $duplicate->setIdSynchro(null);
        $duplicate->setArchive(false);
        $duplicate->setFlagActif(false);
    }
}
