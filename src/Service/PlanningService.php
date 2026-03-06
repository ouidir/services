<?php

namespace App\Service;

use App\Entity\Calendrier;
use App\Repository\CalendrierRepository;
use App\Repository\PeriodeRepository;
use App\Service\Planning\CalendrierFactoryInterface;
use App\Service\Planning\PeriodeComparatorInterface;
use App\Service\Planning\SynchronisationIdGenerator;

class PlanningService
{
    public function __construct(
        private readonly CalendrierRepository $calendrierRepository,
        private readonly PeriodeRepository $periodeRepository,
        private readonly PeriodeComparatorInterface $periodeComparator,
        private readonly CalendrierFactoryInterface $calendrierFactory,
        private readonly SynchronisationIdGenerator $synchronisationIdGenerator
    ) {
    }

    public function getPlanningByPeriodesForImport(array $planning): ?Calendrier
    {
        $existingCalendrier = $this->calendrierRepository->find($planning['id']);

        if (!$existingCalendrier) {
            return null;
        }

        $existingPeriodes = $existingCalendrier->getPeriodes()->toArray();

        if (!$this->periodeComparator->isDifferent($planning['periodes'], $existingPeriodes)) {
            return $existingCalendrier;
        }

        $calendrierByPeriodes = $this->findCalendrierByPeriodes($planning['periodes']);

        return $calendrierByPeriodes ?? $this->createCalendrier($planning);
    }

    public function createCalendrier(array $planning): Calendrier
    {
        $calendrier = $this->calendrierFactory->createFromPlanning($planning);
        $calendrier->setIdSynchro($this->synchronisationIdGenerator->generateNextId());

        $this->calendrierRepository->save($calendrier);

        return $calendrier;
    }

    private function findCalendrierByPeriodes(array $periodes): ?Calendrier
    {
        $calendriers = $this->periodeRepository->findByPeriodes($periodes);

        if (empty($calendriers)) {
            return null;
        }

        return $this->calendrierRepository->find($calendriers[0]['calendrier_id']);
    }

    public function createWithDefaultPeriodes()
    {
        return $this->calendrierFactory->createWithDefaultPeriodes();
    }
}
