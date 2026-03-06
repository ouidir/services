<?php

namespace App\Service\Correction;

use App\Entity\Scenario;
use App\Service\PeriodesIndisposService;
use Psr\Log\LoggerInterface;

final readonly class IndisponibiliteCorrectionStrategy implements CorrectionStrategyInterface
{
    public function __construct(
        private PeriodesIndisposService $periodesIndisposService,
        private LoggerInterface $logger
    ) {
    }

    public function correct(Scenario $scenario, DataDto $data): void
    {
        if (!$scenario->getFlagIndispo()) {
            $this->logger->debug('Scénario sans flag indispo, correction ignorée', [
                'scenario_id' => $scenario->getId(),
            ]);
            return;
        }

        $dateDebut = $data->getDateDebutMutable();
        $dateFin = $data->getDateFinMutable();

        // Normalisation des dates à minuit
        $tmpDateDeb = (clone $dateDebut)->setTime(0, 0, 0);
        $tmpDateFin = (clone $dateFin)->setTime(0, 0, 0);

        // Si même jour, ajouter un jour à la fin
        if ($tmpDateDeb == $tmpDateFin) {
            $tmpDateFin->add(new \DateInterval('P1D'));
        }

        // Suppression et recalcul des périodes
        $this->periodesIndisposService->deletePeriodes($scenario, $tmpDateDeb, $tmpDateFin);
        $this->periodesIndisposService->regeneratePeriodes($tmpDateDeb, $tmpDateFin, $scenario);

        $this->logger->info('Indisponibilités corrigées pour le scénario', [
            'scenario_id' => $scenario->getId(),
            'scenario_nom' => $scenario->getNom(),
            'date_debut' => $tmpDateDeb->format('Y-m-d'),
            'date_fin' => $tmpDateFin->format('Y-m-d'),
        ]);
    }
}
