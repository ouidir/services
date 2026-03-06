<?php

namespace App\Service\PeriodesIndispos;

use App\Entity\Scenario;
use App\Model\IntervalHorraire;
use App\Repository\Interface\ExecutionRepositoryInterface;
use App\Service\PeriodesIndispos\PeriodeIndispoDto;
use App\Service\PeriodesIndispos\PlanningValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Calculateur de périodes basé sur les exécutions
 * Respecte SRP : se concentre uniquement sur le calcul
 */
class PeriodeCalculator implements PeriodeCalculatorInterface
{
    public function __construct(
        private ExecutionRepositoryInterface $executionRepository,
        private PlanningValidatorInterface $planningValidator,
        private LoggerInterface $logger,
    ) {
    }

    public function calculate(
        Scenario $scenario,
        \DateTime $dateDebut,
        \DateTime $dateFin
    ): array {
        $periodes = [];
        $dateTmp = clone $dateDebut;

        while ($dateTmp <= $dateFin) {
            // Vérification des données déjà calculées
            if ($this->shouldSkipDate($scenario, $dateTmp)) {
                $dateTmp = $this->skipToNextRelevantDate($scenario, $dateTmp);
                continue;
            }

            // Validation du planning
            if (!$this->planningValidator->shouldCalculate($scenario, $dateTmp)) {
                $dateTmp->add(new \DateInterval('P1D'));
                continue;
            }

            // Calcul des périodes pour la journée
            $periodesJour = $this->calculateDayPeriodes($scenario, $dateTmp);
            $periodes = array_merge($periodes, $periodesJour);

            $dateTmp->add(new \DateInterval('P1D'));
        }

        return $periodes;
    }

    private function calculateDayPeriodes(Scenario $scenario, \DateTime $date): array
    {
        $periodes = [];
        $planning = $scenario->getPlanningIndispo();

        if (!$planning) {
            return $periodes;
        }

        foreach ($planning->getPeriodes() as $periode) {
            if ($periode->getJour() === (int)$date->format('N')) {
                $periodeData = $this->calculatePeriodeFromExecutions(
                    $scenario,
                    $date,
                    $periode->getHeureDebut(),
                    $periode->getHeureFin()
                );
                $periodes = array_merge($periodes, $periodeData);
            }
        }

        return $periodes;
    }

    private function calculatePeriodeFromExecutions(
        Scenario $scenario,
        \DateTime $date,
        \DateTime $heureDebut,
        \DateTime $heureFin
    ): array {
        $pasExecution = new IntervalHorraire('PT' . $scenario->getPasExecution() . 'S');

        // Création des bornes temporelles
        $bornes = $this->createTimeBounds($date, $heureDebut, $heureFin, $pasExecution);

        // Récupération des exécutions
        $executions = $this->executionRepository->findByScenarioDateField(
            $scenario,
            $bornes['borneA'],
            $bornes['borneB']
        );

        // Agrégation en périodes
        return $this->aggregateExecutionsIntoPeriodes(
            $executions,
            $bornes,
            $pasExecution,
            $scenario
        );
    }

    private function createTimeBounds(
        \DateTime $date,
        \DateTime $heureDebut,
        \DateTime $heureFin,
        IntervalHorraire $pasExecution
    ): array {
        $tmpA = explode(':', $heureDebut->format('H:i:s'));
        $tmpB = explode(':', $heureFin->format('H:i:s'));

        $borneA = (clone $date)->setTime((int)$tmpA[0], (int)$tmpA[1], (int)$tmpA[2]);
        $borneB = (clone $date)->setTime((int)$tmpB[0], (int)$tmpB[1], (int)$tmpB[2]);

        return [
            'borneA' => $borneA->sub($pasExecution->toDateInterval()),
            'borneB' => $borneB->add($pasExecution->toDateInterval()),
            'debutIndispo' => (clone $date)->setTime((int)$tmpA[0], (int)$tmpA[1], (int)$tmpA[2]),
            'finIndispo' => (clone $date)->setTime((int)$tmpB[0], (int)$tmpB[1], (int)$tmpB[2]),
        ];
    }

    private function aggregateExecutionsIntoPeriodes(
        array $executions,
        array $bornes,
        IntervalHorraire $pasExecution,
        Scenario $scenario
    ): array {
        $periodes = [];
        $debutPeriode = clone $bornes['debutIndispo'];
        $finPeriode = clone $bornes['debutIndispo'];
        $etatCourant = null;

        foreach ($executions as $execution) {
            $etatScenario = $this->determineEtatExecution($execution);
            $dateExecution = $execution->getDate();

            // Avant la consolidation
            if ($dateExecution < $bornes['debutIndispo']) {
                $etatCourant = $etatScenario;
                continue;
            }

            // Après la plage
            if ($dateExecution > $bornes['finIndispo']) {
                $etatCourant = $etatCourant ?? $etatScenario;
                break;
            }

            // Détection des trous de supervision
            if ($this->isSupervisionGap($dateExecution, $finPeriode, $pasExecution)) {
                // Enregistrer période avant le trou
                if ($etatCourant !== null) {
                    $periodes[] = new PeriodeIndispoDto(
                        $debutPeriode,
                        $finPeriode->add($pasExecution->toDateInterval()),
                        $etatCourant
                    );
                }

                // Période "absence d'exécution"
                $periodes[] = $this->createAbsencePeriode(
                    $finPeriode,
                    $dateExecution,
                    $scenario,
                    $pasExecution
                );

                $debutPeriode = clone $dateExecution;
                $etatCourant = $etatScenario;
                $finPeriode = clone $dateExecution;
                continue;
            }

            // Initialisation état
            if ($etatCourant === null) {
                $etatCourant = $etatScenario;
                $finPeriode = $dateExecution;
                continue;
            }

            // Changement d'état
            if ($etatCourant !== $etatScenario) {
                $periodes[] = new PeriodeIndispoDto(
                    $debutPeriode,
                    $dateExecution,
                    $etatCourant
                );

                $finPeriode = clone $dateExecution;
                $debutPeriode = clone $dateExecution;
                $etatCourant = $etatScenario;
            } else {
                $finPeriode = $dateExecution;
            }
        }

        // Dernière période
        if (!empty($executions) && $etatCourant !== null) {
            $periodes = array_merge(
                $periodes,
                $this->createFinalPeriodes(
                    $debutPeriode,
                    $finPeriode,
                    $bornes['finIndispo'],
                    $etatCourant,
                    $pasExecution
                )
            );
        }

        return $periodes;
    }

    private function determineEtatExecution($execution): int
    {
        $etat = $execution->getStatus();

        // Les warnings sont OK
        if ($etat === 1) {
            return 0;
        }

        // Maintenance signalée avec état 9
        if ($execution->getMaintenance()) {
            return 9;
        }

        return $etat;
    }

    private function isSupervisionGap(
        \DateTime $dateExecution,
        \DateTime $finPeriode,
        IntervalHorraire $pasExecution
    ): bool {
        $intervalle = IntervalHorraire::createFromDateInterval(
            $dateExecution->diff($finPeriode)
        );

        return $intervalle->toSeconds() > ($pasExecution->toSeconds() * 2);
    }

    private function createAbsencePeriode(
        \DateTime $debut,
        \DateTime $fin,
        Scenario $scenario,
        IntervalHorraire $pasExecution
    ): PeriodeIndispoDto {
        $dateActivation = $scenario->getDateActivation() ?? new \DateTime('0000-00-00 00:00:00');
        $dateActivationAdjusted = $dateActivation->add($pasExecution->toDateInterval());

        $etat = ($scenario->getFlagActif() && $fin > $dateActivationAdjusted) ? 3 : 7;

        return new PeriodeIndispoDto($debut, $fin, $etat);
    }

    private function createFinalPeriodes(
        \DateTime $debutPeriode,
        \DateTime $finPeriode,
        \DateTime $finIndispo,
        int $etatCourant,
        IntervalHorraire $pasExecution
    ): array {
        $periodes = [];
        $finPeriodeAdjusted = $finPeriode->add($pasExecution->toDateInterval());

        if ($finPeriodeAdjusted < $finIndispo) {
            $periodes[] = new PeriodeIndispoDto($debutPeriode, $finPeriodeAdjusted, $etatCourant);
            $periodes[] = new PeriodeIndispoDto($finPeriodeAdjusted, $finIndispo, 3);
        } else {
            $periodes[] = new PeriodeIndispoDto($debutPeriode, $finIndispo, $etatCourant);
        }

        return $periodes;
    }

    private function shouldSkipDate(Scenario $scenario, \DateTime $date): bool
    {
        return $scenario->getDateFinPeriode() &&
               $date < $scenario->getDateFinPeriode();
    }

    private function skipToNextRelevantDate(Scenario $scenario, \DateTime $date): \DateTime
    {
        $gap = $date->diff($scenario->getDateFinPeriode());

        if ($gap->days > 1) {
            $this->logger->info("Données déjà générées - saut de {$gap->days} jours");
            return $date->add(new \DateInterval("P{$gap->days}D"));
        }

        $this->logger->info("Données déjà générées - passage au jour suivant");
        return $date->add(new \DateInterval('P1D'));
    }
}
