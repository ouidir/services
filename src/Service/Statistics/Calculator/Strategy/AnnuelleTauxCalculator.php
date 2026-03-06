<?php

namespace App\Service\Statistics\Calculator\Strategy;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\Statistics\Calculator\TauxCalculatorInterface;
use App\Service\Statistics\DTO\TauxCalculationConfig;
use DateInterval;

/**
 * Calculateur de taux annuel
 * Respecte SRP : Calcul annuel uniquement
 */
class AnnuelleTauxCalculator implements TauxCalculatorInterface
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
        $annee = (int)$date->format('Y');
        $debutAnnee = (new \DateTime("{$annee}-01-01"))
            ->sub(new DateInterval("P" . ($config->periode - 1) . "Y"));
        $finAnnee = (new \DateTime("{$annee}-01-01"))->modify('next year');

        // Initialisation des années
        $init = $this->initializeYears($debutAnnee, $finAnnee);

        $methodName = $this->moyenne
            ? 'getAnnuelleMoyenneTauxIndisposByApplication'
            : 'getAnnuelleTauxIndisposByApplication';

        $indispos = $this->repository->$methodName(
            $applications,
            $debutAnnee,
            $finAnnee,
            $config->archive
        );

        return $this->aggregateByYear($indispos, $init);
    }

    private function initializeYears(\DateTime $debut, \DateTime $fin): array
    {
        $init = [];
        $current = clone $debut;

        while ($current < $fin) {
            $init['Année ' . $current->format('Y')] = '-';
            $current->add(new DateInterval('P1Y'));
        }

        return $init;
    }

    private function aggregateByYear(array $indispos, array $init): array
    {
        $data = [];

        foreach ($indispos as $item) {
            $yearDate = new \DateTime($item['year']);
            $yearKey = 'Année ' . $yearDate->format('Y');

            $key = $this->moyenne
                ? $item['appId']
                : "{$item['appId']}_{$item['scenarioId']}";

            if (!isset($data[$key])) {
                $data[$key] = $this->initializeDataStructure($item, $init);
            }

            $data[$key][$yearKey] = $item['brut'];
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
