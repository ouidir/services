<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Injecteur;

class InjecteurService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getAllinjecteurs()
    {
        return $this->entityManager->getRepository(Injecteur::class)->findAll();
    }
}
