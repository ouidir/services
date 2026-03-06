<?php

namespace App\Service\Ponderation;

use App\Admin\Form\Data\PonderationDto;
use App\Entity\Calendrier;
use App\Repository\CalendrierRepository;

class PonderationValidator
{
    public function __construct(
        private CalendrierRepository $calendrierRepository
    ) {
    }

    public function validate(PonderationDto $dto): array
    {
        $result = ['isValid' => true];

        if (empty($dto->scenarios->getScenarios())) {
            return $result;
        }

        foreach ($dto->scenarios->getScenarios() as $scenario) {
            $calendrier = $this->calendrierRepository
                ->find($scenario->getPlanningExecution()->getId());

            if (
                !$this->isCompatible(
                    $calendrier,
                    $dto->periode->getJoursFromInt(),
                    $dto->periode->getHeures()
                )
            ) {
                $result['warning'] = sprintf(
                    'Attention : certaines pondérations saisies couvrent des plages horaires ' .
                    'en dehors du planning d\'exécution du scénario "%s"',
                    $scenario->getNom()
                );
                break;
            }
        }

        return $result;
    }

    public function isCompatible(?Calendrier $scenarioPlaning, array $jours, array $ponderationPlaning): bool
    {
        if (!$scenarioPlaning || empty($jours)) {
            return false;
        }

        $joursScenario = $this->extractJoursFromCalendrier($scenarioPlaning);

        return $this->validateJours($jours, $joursScenario)
            && $this->validateHeures($ponderationPlaning, $scenarioPlaning->getPeriodes());
    }

    private function extractJoursFromCalendrier(Calendrier $calendrier): array
    {
        $jours = [];
        foreach ($calendrier->getPeriodes() as $periode) {
            $jour = $periode->getJour() - 1;
            if (!in_array($jour, $jours, true)) {
                $jours[] = $jour;
            }
        }
        return $jours;
    }

    private function validateJours(array $jours, array $joursScenario): bool
    {
        foreach ($jours as $jour) {
            if (!in_array($jour, $joursScenario, true)) {
                return false;
            }
        }
        return true;
    }

    private function validateHeures(array $ponderationPlaning, iterable $periodes): bool
    {
        foreach (array_values($ponderationPlaning) as $key => $ponderation) {
            if ($ponderation != 0) {
                $heure = (new \DateTime('1970-01-01'))->setTime($key, 0, 0, 0);
                foreach ($periodes as $periode) {
                    if ($heure < $periode->getHeureDebut() || $heure > $periode->getHeureFin()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
