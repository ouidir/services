<?php

namespace App\Service\Scenario;

use App\Model\StatusModel;
use App\Repository\ScenarioRepository;

class SinapsDataExporter
{
    private const COLUMNS = [
        'application' => 0,
        'scenario' => 1,
        'etat' => 2,
        'date' => 3,
        'pas' => 4,
        'message' => 5
    ];

    public function __construct(
        private ScenarioRepository $scenarioRepository
    ) {
    }

    public function export(): array
    {
        $data = $this->scenarioRepository->getStatusScenario(null, true);

        return [
            'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            'cols' => self::COLUMNS,
            'rows' => $this->transformRows($data)
        ];
    }

    private function transformRows(array $data): array
    {
        return array_map(
            fn(array $row) => $this->transformRow($row),
            $data
        );
    }

    private function transformRow(array $row): array
    {
        $etat = $this->getEtatLibelle($row['statusExecution']);

        return [
            $row['application'],
            $row['scenario'],
            $etat,
            $row['dateExecution'],
            $row['pasExecution'],
            $row['commentaire']
        ];
    }

    private function getEtatLibelle(int $statusExecution): string
    {
        $statusKey = StatusModel::ITEMS[$statusExecution] ?? null;
        return StatusModel::ITEMS_LIBELLE_SINAPS[$statusKey] ?? 'INCONNU';
    }
}
