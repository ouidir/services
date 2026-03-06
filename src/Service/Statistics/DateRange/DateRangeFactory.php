<?php

namespace App\Service\Statistics\DateRange;

use DateInterval;

/**
 * Factory pour créer des plages de dates
 * Respecte SRP : Création de dates uniquement
 */
class DateRangeFactory
{
    /**
     * Crée les plages standards (jour/semaine/mois/année)
     */
    public function createStandardRanges(\DateTime $date): array
    {
        return [
            'jour' => $this->createDayRange($date),
            'semaine' => $this->createWeekRange($date),
            'mois' => $this->createMonthRange($date),
            'annee' => $this->createYearRange($date),
        ];
    }

    /**
     * Crée une plage selon une clé
     */
    public function createRangeByKey(string $key, \DateTime $date): array
    {
        return match ($key) {
            'jour' => $this->createExtendedDayRange($date),
            'semaine' => $this->createExtendedWeekRange($date),
            'mois' => $this->createExtendedMonthRange($date),
            'annee' => $this->createExtendedYearRange($date),
            default => $this->createMultiYearRange($date),
        };
    }

    private function createDayRange(\DateTime $date): array
    {
        $debut = clone $date;
        $fin = (clone $date)->add(new DateInterval('P1D'));

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createWeekRange(\DateTime $date): array
    {
        $debut = clone $date;

        if ($debut->format('l') !== 'Monday') {
            $debut->modify('last monday');
        }

        $fin = (clone $date)->modify('next monday');

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createMonthRange(\DateTime $date): array
    {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');

        $debut = new \DateTime("{$year}-{$month}-01");
        $fin = (clone $debut)->modify('next month');

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createYearRange(\DateTime $date): array
    {
        $year = (int)$date->format('Y');

        $debut = new \DateTime("{$year}-01-01");
        $fin = (clone $debut)->modify('next year');

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createExtendedDayRange(\DateTime $date): array
    {
        $debut = clone $date;
        $fin = (clone $date)->add(new DateInterval('P1D'));

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createExtendedWeekRange(\DateTime $date): array
    {
        $debut = (clone $date)->sub(new DateInterval('P14D'));
        $fin = (clone $date)->add(new DateInterval('P1D'));

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createExtendedMonthRange(\DateTime $date): array
    {
        $debut = (clone $date)->sub(new DateInterval('P14W'));
        $fin = (clone $date)->add(new DateInterval('P1D'));

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createExtendedYearRange(\DateTime $date): array
    {
        $debut = (clone $date)->sub(new DateInterval('P14M'));
        $fin = (clone $date)->add(new DateInterval('P1D'));

        return ['debut' => $debut, 'fin' => $fin];
    }

    private function createMultiYearRange(\DateTime $date): array
    {
        $debut = (clone $date)->sub(new DateInterval('P14Y'));
        $fin = (clone $date)->add(new DateInterval('P1D'));

        return ['debut' => $debut, 'fin' => $fin];
    }
}
