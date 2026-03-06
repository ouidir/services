<?php

namespace App\Service\Scenario;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\AnomalieModel;
use App\Model\KeysCacheModel;
use App\Repository\ScenarioRepository;

class ScenarioAnomalieProvider
{
    public function __construct(
        private ScenarioRepository $scenarioRepository,
        private CacheHandler $cacheHandler,
        private AnomalieModel $anomalieModel
    ) {
    }

    public function getAnomalies(): array
    {
        $value = $this->cacheHandler->get(
            'scenario',
            KeysCacheModel::ISAC_ANOMALIES_DATA,
            fn(): array => $this->fetchAndAggregateAnomalies(),
            900
        );

        return $value ?? [];
    }

    private function fetchAndAggregateAnomalies(): array
    {
        $data = $this->scenarioRepository->getStatusScenario();
        return $this->anomalieModel->agregate($data);
    }
}
