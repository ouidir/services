<?php

namespace App\Service;

use App\Entity\Scenario;
use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use App\Service\PeriodesIndispos\PeriodeCalculatorInterface;
use App\Service\PeriodesIndispos\PeriodeWriterInterface;
use Psr\Log\LoggerInterface;

/**
 * Service orchestrateur pour les périodes d'indisponibilité
 * Respecte SRP en déléguant les responsabilités
 */
class PeriodesIndisposService
{
    public function __construct(
        private PeriodeCalculatorInterface $calculator,
        private PeriodeWriterInterface $writer,
        private PeriodesIndisposRepositoryInterface $repository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Régénère les périodes d'indisponibilité pour un scénario
     */
    public function regeneratePeriodes(
        \DateTime $dateDebut,
        \DateTime $dateFin,
        ?Scenario $scenario = null
    ): array {
        $scenarios = $this->repository->findScenariosForIndispo($scenario);

        if (empty($scenarios)) {
            return [false, "Aucun scénario sélectionné ou configuré en indisponibilité"];
        }

        $totalPeriodes = 0;
        foreach ($scenarios as $scenarioItem) {
            $periodes = $this->calculator->calculate($scenarioItem, $dateDebut, $dateFin);
            $this->writer->writePeriodes($scenarioItem, $periodes);
            $totalPeriodes += count($periodes);
        }

        $this->logger->info("Régénération terminée : {$totalPeriodes} périodes créées");

        return [true, $totalPeriodes];
    }

    /**
     * Supprime les périodes sur une plage de dates
     */
    public function deletePeriodes(
        Scenario $scenario,
        \DateTime $dateDebut,
        \DateTime $dateFin
    ): int {
        return $this->repository->deleteByScenarioAndDates($scenario, $dateDebut, $dateFin);
    }
}
