<?php

namespace App\Service\Scenario;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Model\StatusModel;
use App\Repository\ScenarioRepository;

class ScenarioStatusProvider
{
    public function __construct(
        private ScenarioRepository $scenarioRepository,
        private CacheHandler $cacheHandler
    ) {
    }

    public function getStatusByScenarioId(int $scenarioId): int
    {
        $data = $this->scenarioRepository
            ->getStatusScenario(null, false, null, $scenarioId);

        if ($data && isset($data[0]['statusExecution'])) {
            return $data[0]['statusExecution'];
        }

        return StatusModel::ITEM_INCONNU;
    }

    public function getAllScenarioStatus(): array
    {
        $value = $this->cacheHandler->get(
            'scenario',
            KeysCacheModel::ISAC_EXECUTIONS_DATA,
            fn(): array => $this->fetchAndAggregateStatus(),
            900
        );

        return $value ?? [];
    }

    private function fetchAndAggregateStatus(): array
    {
        $data = $this->scenarioRepository->getStatusScenario();
        return StatusModel::agregate($data);
    }
}
