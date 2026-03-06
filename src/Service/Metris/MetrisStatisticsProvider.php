<?php

namespace App\Service\Metris;

use App\Repository\PeriodesIndisposRepository;

class MetrisStatisticsProvider implements MetrisStatisticsProviderInterface
{
    public function __construct(
        private readonly PeriodesIndisposRepository $periodesIndisposRepository
    ) {
    }

    public function getStatistics(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    {
        return $this->periodesIndisposRepository->statistiquesMetris($dateDebut, $dateFin);
    }
}
