<?php

namespace App\Service\Evolution;

use DateInterval;
use DatePeriod;

/**
 * Service responsable de la génération des dates pour les évolutions
 */
class EvolutionDateGenerator
{
    private const INTERVAL_MINUTES = 10;

    /**
     * Génère une liste de dates arrondies à la dizaine de minutes inférieure
     */
    public function generateDateList(\DateTime $start, ?\DateTime $end = null): array
    {
        $roundedStart = $this->roundToLowerTenMinutes(clone $start);
        $end = $end ?? new \DateTime();

        $interval = new DateInterval('PT' . self::INTERVAL_MINUTES . 'M');
        $period = new DatePeriod($roundedStart, $interval, $end->add($interval));

        $timestamps = [];
        foreach ($period as $dt) {
            $timestamps[] = $dt->format('Y-m-d H:i:s');
        }

        return $timestamps;
    }

    /**
     * Arrondit une date à la dizaine de minutes inférieure
     */
    private function roundToLowerTenMinutes(\DateTime $date): \DateTime
    {
        $minutes = (int) $date->format('i');
        $roundedMinutes = (int) floor($minutes / 10) * 10;

        $date->setTime(
            (int) $date->format('H'),
            $roundedMinutes,
            0
        );

        return $date;
    }

    /**
     * Crée un interval de 36 heures
     */
    public function create36HoursInterval(): DateInterval
    {
        return new DateInterval('PT36H');
    }
}
