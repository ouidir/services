<?php

namespace App\Service;

use App\Handler\CacheHandler\CacheHandler;
use App\Entity\Maintenance;
use App\Model\KeysCacheModel;
use App\Repository\MaintenanceRepository;

class MaintenanceService
{
    public function __construct(
        private MaintenanceRepository $maintenanceRepository,
        private CacheHandler $cacheHandler
    ) {
    }

    /**
    * Method getMaintenance
    *
    *
    * @return ?Maintenance
    */
    public function getMaintenance(): ?Maintenance
    {
        return  $this->cacheHandler->get(
            'maintenance',
            KeysCacheModel::ISAC_MAINTENANCE,
            function (): ?Maintenance {
                return $this->maintenanceRepository->findOneBy([]);
            }
        );
    }
}
