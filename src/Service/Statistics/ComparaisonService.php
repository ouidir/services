<?php

namespace App\Service\Statistics;

use App\Handler\CacheHandler\CacheHandler;
use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use DateInterval;

/**
 * Service de comparaison des indisponibilités
 * Respecte SRP : Comparaison uniquement
 */
class ComparaisonService implements ComparaisonServiceInterface
{
    public function __construct(
        private PeriodesIndisposRepositoryInterface $repository,
        private CacheHandler $cacheHandler,
    ) {
    }

    public function getComparaisonData(\DateTime $date, array|false $applications = false): array
    {
        $debut = clone $date;
        $fin = (clone $date)->add(new DateInterval('P1D'));
        $cacheKey = 'isac.indispos_comparaison_' . $debut->format('Y-m-d');

        $value = $this->cacheHandler->get(
            'indispos',
            $cacheKey,
            fn(): array => $this->repository->getComparaisonData($debut, $fin, $applications)
        );

        return $value ?? [];
    }

    public function getComparaisonMiniData(\DateTime $date): array
    {
        $debut = (clone $date)->sub(new DateInterval('P1D'));
        $fin = clone $date;

        return $this->repository->getComparaisonMiniData($debut, $fin);
    }
}
