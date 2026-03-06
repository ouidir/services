<?php

namespace App\Service\Statistics\Calculator\Strategy;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\Statistics\Calculator\TauxCalculatorInterface;
use App\Service\Statistics\DateRange\DateRangeFactory;
use App\Service\Statistics\DTO\TauxCalculationConfig;

/**
 * Calculateur de taux par défaut (jour/semaine/mois/année)
 * Respecte SRP : Un seul mode de calcul
 */
class DefaultTauxCalculator implements TauxCalculatorInterface
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
        $ranges = (new DateRangeFactory())->createStandardRanges($date);
        $data = [];

        // Calcul pour chaque période
        foreach (['jour', 'semaine', 'mois', 'annee'] as $periode) {
            $this->calculatePeriode(
                $applications,
                $ranges[$periode]['debut'],
                $ranges[$periode]['fin'],
                $periode,
                $config->archive,
                $data
            );
        }

        return $this->formatResults($data);
    }

    private function calculatePeriode(
        array $applications,
        \DateTime $debut,
        \DateTime $fin,
        string $periodeName,
        bool $archive,
        array &$data
    ): void {
        $methodName = $this->moyenne
            ? 'getMoyenneTauxIndisposByApplication'
            : 'getTauxIndisposByApplication';

        $indispos = $this->repository->$methodName(
            $applications,
            $debut,
            $fin,
            $archive
        );

        $this->aggregateData($indispos, $periodeName, $data);
    }

    private function aggregateData(array $indispos, string $periodeName, array &$data): void
    {
        foreach ($indispos as $item) {
            $key = $this->moyenne
                ? $item['appId']
                : "{$item['appId']}_{$item['scenarioId']}";

            if (!isset($data[$key])) {
                $data[$key] = $this->initializeDataStructure($item);
            }

            $data[$key][$periodeName] = $item['brut'];
        }
    }

    private function initializeDataStructure(array $item): array
    {
        $base = [
            'a' => $item['appNom'],
            'a_id' => $item['appId'],
            'jour' => '-',
            'semaine' => '-',
            'mois' => '-',
            'annee' => '-',
        ];

        if (!$this->moyenne) {
            $base['s'] = $item['scenarioNom'];
            $base['s_id'] = $item['scenarioId'];
        }

        return $base;
    }

    private function formatResults(array $data): array
    {
        return array_values($data);
    }
}
