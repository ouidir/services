<?php

namespace App\Service\Statistics\Calculator\Strategy;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\Statistics\Calculator\TauxCalculatorInterface;
use App\Service\Statistics\DTO\TauxCalculationConfig;
use DateInterval;

/**
 * Calculateur de taux mensuel
 * Respecte SRP : Calcul mensuel uniquement
 */
class MensuelleTauxCalculator implements TauxCalculatorInterface
{
    private const FRENCH_MONTHS = [
        'January' => 'JANVIER',
        'February' => 'FÉVRIER',
        'March' => 'MARS',
        'April' => 'AVRIL',
        'May' => 'MAI',
        'June' => 'JUIN',
        'July' => 'JUILLET',
        'August' => 'AOÛT',
        'September' => 'SEPTEMBRE',
        'October' => 'OCTOBRE',
        'November' => 'NOVEMBRE',
        'December' => 'DÉCEMBRE',
    ];

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
        $debutMois = $this->getFirstDayOfMonth($date, $config->periode);
        $finMois = $this->getFirstDayOfNextMonth($date);

        // Initialisation des mois
        $init = $this->initializeMonths($debutMois, $finMois);

        $methodName = $this->moyenne
            ? 'getMensuelleMoyenneTauxIndisposByApplication'
            : 'getMensuelleTauxIndisposByApplication';

        $indispos = $this->repository->$methodName(
            $applications,
            $debutMois,
            $finMois,
            $config->archive
        );

        return $this->aggregateByMonth($indispos, $init);
    }

    private function getFirstDayOfMonth(\DateTime $date, int $periodOffset): \DateTime
    {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');

        return (new \DateTime("{$year}-{$month}-01"))
            ->sub(new DateInterval("P" . ($periodOffset - 1) . "M"));
    }

    private function getFirstDayOfNextMonth(\DateTime $date): \DateTime
    {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');

        return (new \DateTime("{$year}-{$month}-01"))
            ->modify('next month');
    }

    private function initializeMonths(\DateTime $debut, \DateTime $fin): array
    {
        $init = [];
        $current = clone $debut;

        while ($current < $fin) {
            $key = self::FRENCH_MONTHS[$current->format('F')] . ' ' . $current->format('Y');
            $init[$key] = '-';
            $current->add(new DateInterval('P1M'));
        }

        return $init;
    }

    private function aggregateByMonth(array $indispos, array $init): array
    {
        $data = [];

        foreach ($indispos as $item) {
            $monthDate = new \DateTime($item['month']);
            $monthKey = self::FRENCH_MONTHS[$monthDate->format('F')] . ' ' . $monthDate->format('Y');

            $key = $this->moyenne
                ? $item['appId']
                : "{$item['appId']}_{$item['scenarioId']}";

            if (!isset($data[$key])) {
                $data[$key] = $this->initializeDataStructure($item, $init);
            }

            $data[$key][$monthKey] = $item['brut'];
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
