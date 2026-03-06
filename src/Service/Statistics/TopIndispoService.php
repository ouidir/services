<?php

namespace App\Service\Statistics;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use DateInterval;

/**
 * Service pour récupérer le top des indisponibilités
 * Respecte SRP : Top uniquement
 */
class TopIndispoService implements TopIndispoServiceInterface
{
    public function __construct(
        private PeriodesIndisposRepositoryInterface $repository,
    ) {
    }

    public function getTopByApplications(
        array $applications,
        \DateTime $date,
        int $vue = 3,
        array $options = []
    ): array {
        $range = $this->createRangeByVue($date, $vue);

        return $this->repository->topIndispoBrutByApplications(
            $applications,
            $range['debut'],
            $range['fin'],
            $options
        );
    }

    public function getTopBrut(\DateTime $date): array
    {
        $debut = (clone $date)->modify('first day of this month');
        $fin = (clone $date)->modify('next month')->modify('first day of this month');

        return $this->repository->topIndispoBrut($debut, $fin);
    }

    private function createRangeByVue(\DateTime $date, int $vue): array
    {
        return match ($vue) {
            1 => $this->createDayRange($date),
            2 => $this->createWeekRange($date),
            default => $this->createMonthRange($date),
        };
    }

    private function createDayRange(\DateTime $date): array
    {
        return [
            'debut' => clone $date,
            'fin' => (clone $date)->add(new DateInterval('P1D')),
        ];
    }

    private function createWeekRange(\DateTime $date): array
    {
        $debut = clone $date;

        if ($debut->format('l') !== 'Monday') {
            $debut->modify('last monday');
        }

        return [
            'debut' => $debut,
            'fin' => (clone $date)->modify('next monday'),
        ];
    }

    private function createMonthRange(\DateTime $date): array
    {
        return [
            'debut' => (clone $date)->modify('first day of this month'),
            'fin' => (clone $date)->modify('next month')->modify('first day of this month'),
        ];
    }
}
