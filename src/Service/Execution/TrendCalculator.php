<?php

namespace App\Service\Execution;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Model\TendanceModel;

/**
 * Service responsable du calcul des tendances avec cache
 */
class TrendCalculator
{
    private const CACHE_POOL = 'tendance';

    public function __construct(
        private readonly ExecutionDataRetriever $dataRetriever,
        private readonly CacheHandler $cacheHandler
    ) {
    }

    /**
     * Calcule et met en cache les tendances pour une date donnée
     */
    public function calculateTrend(\DateTime $date): mixed
    {
        return $this->cacheHandler->get(
            self::CACHE_POOL,
            KeysCacheModel::ISAC_TENDANCE_DATA,
            function () use ($date) {
                $executions = $this->dataRetriever->getExecutionsForTrend($date);
                return TendanceModel::agregate($executions);
            }
        );
    }
}
