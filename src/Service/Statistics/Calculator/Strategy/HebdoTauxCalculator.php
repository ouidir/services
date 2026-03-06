<?php

namespace App\Service\Statistics\Calculator\Strategy;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\Statistics\Calculator\TauxCalculatorInterface;
use App\Service\Statistics\DTO\TauxCalculationConfig;

/**
 * Calculateur de taux hebdomadaire
 * Respecte SRP : Calcul hebdomadaire uniquement
 */
class HebdoTauxCalculator implements TauxCalculatorInterface
{
    public function __construct(
        private PeriodesIndisposRepositoryInterface $repository,
        private bool $moyenne = false,
    ) {
    }

    public function calculate(
        array $applications,
        \DateTime $date,
        TauxCalculationConfig $config
    ): array {
        $debutSemaine = $this->getMonday($date);
        $finSemaine = (clone $date)->modify('next monday');

        // Initialisation des semaines
        $init = $this->initializeWeeks($debutSemaine, $config->periode);

        $methodName = $this->moyenne
            ? 'getHebdoMoyenneTauxIndisposByApplication'
            : 'getHebdoTauxIndisposByApplication';

        $indispos = $this->repository->$methodName(
            $applications,
            $debutSemaine,
            $finSemaine,
            $config->archive
        );

        return $this->aggregateByWeek($indispos, $init);
    }

    private function getMonday(\DateTime $date): \DateTime
    {
        $clone = clone $date;
        if ($clone->format('l') !== 'Monday') {
            $clone->modify('last monday');
        }

        return $clone;
    }

    private function initializeWeeks(\DateTime $debutSemaine, int $periode): array
    {
        $init = [];
        $current = clone $debutSemaine;

        for ($i = 0; $i < $periode; $i++) {
            $weekNumber = str_pad($current->format('W'), 2, '0', STR_PAD_LEFT);
            $init["SEMAINE{$weekNumber}"] = '-';
            $current->modify('last monday');
        }

        return $init;
    }

    private function aggregateByWeek(array $indispos, array $init): array
    {
        $data = [];

        foreach ($indispos as $item) {
            $weekNumber = str_pad($item['week'], 2, '0', STR_PAD_LEFT);
            $weekKey = "SEMAINE{$weekNumber}";

            $key = $this->moyenne
                ? $item['appId']
                : "{$item['appId']}_{$item['scenarioId']}";

            if (!isset($data[$key])) {
                $data[$key] = $this->initializeDataStructure($item, $init);
            }

            $data[$key][$weekKey] = $item['brut'];
        }

        return array_values($data);
    }

    private function initializeDataStructure(array $item, array $init): array
    {
        $base = array_merge([
            'a' => $item['appNom'],
            'a_id' => $item['appId'],
        ], $init);

        if (!$this->moyenne) {
            $base['s'] = $item['scenarioNom'];
            $base['s_id'] = $item['scenarioId'];
        }

        return $base;
    }
}
