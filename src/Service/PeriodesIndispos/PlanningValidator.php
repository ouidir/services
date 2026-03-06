<?php

namespace App\Service\PeriodesIndispos;

use App\Entity\Scenario;

/**
 * Validateur de plannings d'indisponibilité
 * Respecte SRP : se concentre uniquement sur la validation
 */
class PlanningValidator implements PlanningValidatorInterface
{
    /**
     * Détermine si le calcul doit être effectué pour cette date
     */
    public function shouldCalculate(Scenario $scenario, \DateTime $date): bool
    {
        // Vérifier si le scénario est actif
        if (!$scenario->getFlagActif()) {
            return false;
        }

        // Vérifier si la date est dans la plage d'activation
        $dateActivation = $scenario->getDateActivation();
        if ($dateActivation && $date < $dateActivation) {
            return false;
        }

        // Vérifier s'il y a un planning d'indisponibilité configuré
        $planning = $scenario->getPlanningIndispo();
        if (!$planning) {
            return false;
        }

        // Vérifier si le jour de la semaine correspond au planning
        $jourSemaine = (int)$date->format('N'); // 1 = Lundi, 7 = Dimanche

        foreach ($planning->getPeriodes() as $periode) {
            if ($periode->getJour() === $jourSemaine) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si une période est valide pour un scénario donné
     */
    public function isPeriodeValid(Scenario $scenario, \DateTime $debut, \DateTime $fin): bool
    {
        if ($debut > $fin) {
            return false;
        }

        $planning = $scenario->getPlanningIndispo();
        if (!$planning) {
            return false;
        }

        $jourSemaine = (int)$debut->format('N');

        foreach ($planning->getPeriodes() as $periode) {
            if ($periode->getJour() === $jourSemaine) {
                $heureDebut = $periode->getHeureDebut();
                $heureFin = $periode->getHeureFin();

                // Vérifier si la période est dans les heures configurées
                if (
                    $debut->format('H:i:s') >= $heureDebut->format('H:i:s') &&
                    $fin->format('H:i:s') <= $heureFin->format('H:i:s')
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
