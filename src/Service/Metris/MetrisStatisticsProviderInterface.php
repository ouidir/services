<?php

namespace App\Service\Metris;

interface MetrisStatisticsProviderInterface
{
    /**
     * Récupère les statistiques Metris pour une période donnée
     *
     * @return array<int, array{date: string, appli: string, scenario: string, brut: string, metris_file: string}>
     */
    public function getStatistics(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array;
}
