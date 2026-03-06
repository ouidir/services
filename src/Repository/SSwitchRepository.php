<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\SSwitch;
use Doctrine\ORM\EntityRepository;

class SSwitchRepository extends EntityRepository
{
    public function getAnomaliesSwitchByApplication(Application $application, \DateTime $date): array
    {
        return [];
    }

    public function getAnomaliesSwitch(): array
    {
        return [];
    }

    public function rssSwitch(): array
    {
        return [];
    }

    public function switchForHistoriqueByDate(Application $application, \DateTime $start, \DateTime $end): array
    {
        return [];
    }
}
