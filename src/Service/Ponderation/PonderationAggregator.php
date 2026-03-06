<?php

namespace App\Service\Ponderation;

use App\Entity\Execution;
use App\Entity\Selection;
use Doctrine\ORM\EntityManagerInterface;

class PonderationAggregator
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function aggregateExecutions(Selection $selection, \DateTime $dateDebut, \DateTime $dateFin): array
    {
        $dateFin = (clone $dateFin)->add(new \DateInterval('P1D'));

        $data = $this->entityManager->getRepository(Execution::class)
            ->executionsByPonderation($selection, $dateDebut, $dateFin);

        return $this->aggregate($data);
    }

    public function aggregate(array $data): array
    {
        // Reset instances for new aggregation
        PonderationHandler::$instances = [];

        foreach ($data as $item) {
            $handler = PonderationHandler::getInstance($this->entityManager, $item);
            $handler->agregate($item);
        }

        $executions = $this->sortExecutions(PonderationHandler::$instances);
        $groupedData = $this->groupByApplication($executions);
        $totalData = $this->calculateTotals($executions);

        return ['data' => $groupedData, 'total' => $totalData];
    }

    private function sortExecutions(array $executions): array
    {
        usort($executions, function ($e1, $e2) {
            return ($e1->applicationNom > $e2->applicationNom && $e1->scenario > $e2->scenario) ? 1 : -1;
        });

        return $executions;
    }

    private function groupByApplication(array $executions): array
    {
        $grouped = [];
        foreach ($executions as $execution) {
            $grouped[$execution->applicationId][$execution->id] = $execution;
        }
        return $grouped;
    }

    private function calculateTotals(array $executions): array
    {
        $ok = $warn = $critiq = 0;

        foreach ($executions as $execution) {
            $ok += $execution->totalOk;
            $warn += $execution->totalWarn;
            $critiq += $execution->totalCritiq;
        }

        $total = $ok + $warn + $critiq;

        if ($total === 0) {
            return [
                'tauxTotalRestitOK' => 'N/A',
                'tauxTotalRestitWarn' => 'N/A',
                'tauxTotalRestitCritiq' => 'N/A',
                'tauxTotalRestitDispo' => 'N/A'
            ];
        }

        return [
            'tauxTotalRestitOK' => $this->formatPercentage($ok, $total),
            'tauxTotalRestitWarn' => $this->formatPercentage($warn, $total),
            'tauxTotalRestitCritiq' => $this->formatPercentage($critiq, $total),
            'tauxTotalRestitDispo' => $this->formatPercentage($ok + $warn, $total)
        ];
    }

    public function calculateApplicationRates(array $data): array
    {
        $sumOk = $sumWarn = $sumCritiq = 0;

        array_walk($data, function ($pd) use (&$sumOk, &$sumWarn, &$sumCritiq) {
            $sumOk += $pd->totalOk;
            $sumWarn += $pd->totalWarn;
            $sumCritiq += $pd->totalCritiq;
        });

        $total = $sumOk + $sumWarn + $sumCritiq;

        if ($total === 0) {
            return [
                'Ok' => 'N/A',
                'Warn' => 'N/A',
                'Critiq' => 'N/A',
                'Dispo' => 'N/A'
            ];
        }

        return [
            'Ok' => $this->formatPercentage($sumOk, $total),
            'Warn' => $this->formatPercentage($sumWarn, $total),
            'Critiq' => $this->formatPercentage($sumCritiq, $total),
            'Dispo' => $this->formatPercentage($sumOk + $sumWarn, $total)
        ];
    }

    private function formatPercentage(float $value, float $total): string
    {
        return number_format($value * 100 / $total, 2, '.', ' ') . ' %';
    }
}
