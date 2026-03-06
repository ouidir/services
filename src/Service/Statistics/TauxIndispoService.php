<?php

namespace App\Service\Statistics;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\Statistics\Calculator\TauxCalculatorFactory;
use App\Service\Statistics\DateRange\DateRangeFactory;
use App\Service\Statistics\DTO\TauxCalculationConfig;
use App\Service\Statistics\Formatter\TauxFormatterInterface;

/**
 * Service de calcul des taux d'indisponibilité
 * Respecte SRP : Calcul des taux uniquement
 */
class TauxIndispoService implements TauxIndispoServiceInterface
{
    public function __construct(
        private PeriodesIndisposRepositoryInterface $repository,
        private TauxCalculatorFactory $calculatorFactory,
        private DateRangeFactory $dateRangeFactory,
        private TauxFormatterInterface $formatter,
    ) {
    }

    public function getByScenarios(array $scenarios, \DateTime $date): array
    {
        $ranges = $this->dateRangeFactory->createStandardRanges($date);
        $indispos = [];

        foreach ($scenarios as $scenario) {
            $jour = $this->repository->getTauxIndisposByDateScenario(
                $scenario['id'],
                $ranges['jour']['debut'],
                $ranges['jour']['fin']
            );

            $semaine = $this->repository->getTauxIndisposByDateScenario(
                $scenario['id'],
                $ranges['semaine']['debut'],
                $ranges['semaine']['fin']
            );

            $mois = $this->repository->getTauxIndisposByDateScenario(
                $scenario['id'],
                $ranges['mois']['debut'],
                $ranges['mois']['fin']
            );

            $annee = $this->repository->getTauxIndisposByDateScenario(
                $scenario['id'],
                $ranges['annee']['debut'],
                $ranges['annee']['fin']
            );

            $indispos[$scenario['id']] = [
                'nom' => $scenario['nom'],
                'jour' => $jour[0]['brut'] ?? '-',
                'semaine' => $semaine[0]['brut'] ?? '-',
                'mois' => $mois[0]['brut'] ?? '-',
                'annee' => $annee[0]['brut'] ?? '-',
            ];
        }

        return $this->formatter->sortByName($indispos);
    }

    public function getByApplications(
        array $applications,
        \DateTime $date,
        TauxCalculationConfig $config
    ): array {
        $calculator = $this->calculatorFactory->create($config);

        $data = $calculator->calculate($applications, $date, $config);

        return $this->formatter->sortByApplication($data);
    }

    public function getByScenarioItem(int $scenarioId, string $key, \DateTime $date): array
    {
        $range = $this->dateRangeFactory->createRangeByKey($key, $date);
        $item = $this->getItemTypeByKey($key);

        return $this->repository->getTauxIndisposByItemScenario(
            $scenarioId,
            $item,
            $range['debut'],
            $range['fin']
        );
    }

    private function getItemTypeByKey(string $key): string
    {
        return match ($key) {
            'jour' => 'hour',
            'semaine' => 'day',
            'mois' => 'week',
            'annee' => 'month',
            default => 'year',
        };
    }
}
