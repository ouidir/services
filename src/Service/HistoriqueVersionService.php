<?php

namespace App\Service;

use App\Handler\CacheHandler\CacheHandler;
use App\Repository\HistoriqueVersionRepository;
use App\Model\KeysCacheModel;

class HistoriqueVersionService
{
    public function __construct(
        private HistoriqueVersionRepository $historiqueVersionRepository,
        private CacheHandler $cacheHandler
    ) {
    }

    public function getAll(): array
    {
        $value = $this->cacheHandler->get(
            'historique_version',
            KeysCacheModel::ISAC_HISTORIQUE_VERSION,
            function (): array {
                return $this->historiqueVersionRepository->findBy([], ['date' => 'desc']);
            }
        );

        return $value ?? [];
    }
}
