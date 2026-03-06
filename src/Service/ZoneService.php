<?php

namespace App\Service;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\ZoneRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ZoneService
{
    public function __construct(
        private CacheHandler $cacheHandler,
        private ZoneRepository $zoneRepository,
        private NormalizerInterface $normalizer
    ) {
    }

    /**
     * Method getZones
     *
     * @return iterable
     */
    public function getZones(): array
    {
        $value = $this->cacheHandler->get(
            'zone',
            KeysCacheModel::LIST_ZONE,
            function (): array {
                $zones = $this->zoneRepository->findBy([], ['position' => 'ASC']);
                return $this->normalizer->normalize($zones, null, ['groups' => 'level1']);
            }
        );

        return $value ?? [];
    }

    public function getListZones(): array
    {
        $zones = [];
        $listZones = $this->zoneRepository->findBy([], ['position' => 'ASC']);

        foreach ($listZones as $zone) {
            $zones[$zone->getLibelle()] = $zone->getId();
        }
        return $zones;
    }
}
