<?php

namespace App\Service\Statistics\Calculator\Strategy;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\Statistics\Calculator\TauxCalculatorInterface;
use App\Service\Statistics\DTO\TauxCalculationConfig;
use DateInterval;

/**
 * Calculateur de taux quotidien
 * Respecte SRP : Calcul quotidien uniquement
 */
class QuotidienTauxCalculator implements TauxCalculatorInterface
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
        $debut = (clone $date)->sub(new DateInterval("P{$config->periode}D"))->add(new DateInterval('P1D'));
        $fin = (clone $date)->add(new DateInterval('P1D'));

        // Initialisation des jours
        $init = $this->initializeDays($debut, $fin);

        $methodName = $this->moyenne
            ? 'getQuotidienMoyenneTauxIndisposByApplication'
            : 'getQuotidienTauxIndisposByApplication';

        $indispos = $this->repository->$methodName(
            $applications,
            $debut,
            $fin,
            $config->archive
        );

        return $this->aggregateByDay($indispos, $init);
    }

    private function initializeDays(\DateTime $debut, \DateTime $fin): array
    {
        $init = [];
        $current = clone $debut;

        while ($current < $fin) {
            $init[$current->format('d/m/Y')] = '-';
            $current->add(new DateInterval('P1D'));
        }

        return $init;
    }

    private function aggregateByDay(array $indispos, array $init): array
    {
        $data = [];

        foreach ($indispos as $item) {
            $day = new \DateTime($item['day']);
            $key = $this->moyenne
                ? $item['appId']
                : "{$item['appId']}_{$item['scenarioId']}";

            if (!isset($data[$key])) {
                $data[$key] = $this->initializeDataStructure($item, $init);
            }

            $data[$key][$day->format('d/m/Y')] = $item['brut'];
        }

        return array_values($data);
    }

    private function initializeDataStructure(array $item, array $init): array
    {
        $base = array_merge($init, [
            'a' => $item['appNom'],
            'a_id' => $item['appId'],
        ]);

        if (!$this->moyenne) {
            $base['s'] = $item['scenarioNom'];
            $base['s_id'] = $item['scenarioId'];
        }

        return $base;
    }
}
