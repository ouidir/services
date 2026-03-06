<?php

namespace App\Service\Correction;

use App\Entity\Scenario;
use App\Service\ExecutionService;
use Psr\Log\LoggerInterface;

final readonly class ExecutionCorrectionStrategy implements CorrectionStrategyInterface
{
    public function __construct(
        private ExecutionService $executionService,
        private LoggerInterface $logger
    ) {
    }

    public function correct(Scenario $scenario, DataDto $data): void
    {
        $dateDebut = $data->getDateDebutMutable();
        $dateFin = $data->getDateFinMutable();

        // Ajustement des dates pour inclure les bornes
        $tmpDateDeb = (clone $dateDebut)->sub(new \DateInterval('PT1S'));
        $tmpDateFin = (clone $dateFin)->add(new \DateInterval('PT1S'));

        $this->executionService->updateExecution(
            $scenario,
            $tmpDateDeb,
            $tmpDateFin,
            $data->codeACorriger,
            $data->codeAAffecter,
            $data->commentaireModifie
        );

        $this->logger->info('Exécutions corrigées pour le scénario', [
            'scenario_id' => $scenario->getId(),
            'scenario_nom' => $scenario->getNom(),
            'date_debut' => $dateDebut->format('Y-m-d H:i:s'),
            'date_fin' => $dateFin->format('Y-m-d H:i:s'),
        ]);
    }
}
