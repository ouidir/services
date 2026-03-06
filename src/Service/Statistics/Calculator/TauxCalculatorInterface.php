<?php

namespace App\Service\Statistics\Calculator;

use App\Service\Statistics\DTO\TauxCalculationConfig;

/**
 * Interface pour les calculateurs de taux
 * Respecte ISP et Strategy Pattern
 */
interface TauxCalculatorInterface
{
    /**
     * Calcule les taux d'indisponibilité
     *
     * @param array $applications Liste des applications
     * @param \DateTime $date Date de référence
     * @param TauxCalculationConfig $config Configuration
     * @return array Résultats formatés
     */
    public function calculate(
        array $applications,
        \DateTime $date,
        TauxCalculationConfig $config
    ): array;
}
