<?php

namespace App\Service\Evolution;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\Interface\EvolutionJobRepositoryInterface;

/**
 * Service responsable de la récupération des données d'évolution avec cache
 */
class EvolutionDataRetriever
{
    private const CACHE_TTL = 60;

    public function __construct(
        private readonly EvolutionJobRepositoryInterface $evolutionJobRepository,
        private readonly CacheHandler $cacheHandler,
        private readonly EvolutionDateGenerator $dateGenerator
    ) {
    }

    /**
     * Récupère les données d'évolution des 36 dernières heures (avec cache)
     */
    public function getRecentEvolutionsData(): array
    {
        $value = $this->cacheHandler->get(
            'evolution',
            KeysCacheModel::ISAC_EVOLUTION_DATA,
            function () {
                $date = new \DateTime();
                $date->sub($this->dateGenerator->create36HoursInterval());

                return $this->evolutionJobRepository->getEvolutionsData($date);
            },
            self::CACHE_TTL
        );

        return $value ?? [];
    }

    /**
     * Récupère les données d'évolution pour une plage de dates (avec cache)
     */
    public function getEvolutionsByDateRange(\DateTime $debut, \DateTime $fin): array
    {
        $key = KeysCacheModel::ISAC_EVOLUTION_DATA . '_' . $debut->format('Ymd');

        $value = $this->cacheHandler->get(
            'evolution',
            $key,
            fn() => $this->evolutionJobRepository->getEvolutionsDataByDate($debut, $fin)
        );
        return $value ?? [];
    }
}
