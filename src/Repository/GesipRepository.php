<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Gesip;
use Doctrine\ORM\EntityRepository;

class GesipRepository extends EntityRepository
{
    public function getAnomaliesGesipByApplication(Application $application, \DateTime $date): array
    {
        return [];
    }

    public function getAnomaliesGesip(): array
    {
        return [];
    }

    public function rssGesip(): array
    {
        return [];
    }

    public function gesipForHistoriqueByDate(Application $application, \DateTime $start, \DateTime $end): array
    {
        return [];
    }
}
