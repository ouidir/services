<?php

namespace App\Service;

use App\Repository\Interface\MeteoExecutionRepositoryInterface;

class MeteoService
{
    public function __construct(
        private readonly MeteoExecutionRepositoryInterface $meteoExecutionRepository
    ) {
    }

    /**
     * Retourne la liste des 3 derniers rejeux de chaque scénario topé 'Météo'
     * avec la liste des codes 'id Météo' des applications impactées
     *
     * @return array<int, array<string, mixed>>
     */
    public function getExecutionsMeteo(): array
    {
        return $this->meteoExecutionRepository->findLastThreeExecutionsPerMeteoScenario();
    }
}
