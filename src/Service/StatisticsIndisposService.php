<?php

namespace App\Service;

use App\Service\Statistics\ApplicationFinderInterface;
use App\Service\Statistics\DTO\TauxCalculationConfig;
use App\Service\Statistics\TopIndispoServiceInterface;
use App\Service\Statistics\ComparaisonServiceInterface;
use App\Service\Statistics\TauxIndispoServiceInterface;

/**
 * Service principal des statistiques d'indisponibilité
 * Respecte SRP : Orchestration uniquement
 */
class StatisticsIndisposService
{
    public function __construct(
        private TauxIndispoServiceInterface $tauxIndispoService,
        private TopIndispoServiceInterface $topIndispoService,
        private ComparaisonServiceInterface $comparaisonService,
        private ApplicationFinderInterface $applicationFinder,
    ) {
    }

    /**
     * Récupère les taux d'indisponibilité pour des scénarios
     */
    public function getTauxIndisposByDateScenario(
        array $scenarios,
        int $jour,
        int $mois,
        int $annee
    ): array {
        $date = new \DateTime("$annee-$mois-$jour");

        return $this->tauxIndispoService->getByScenarios($scenarios, $date);
    }

    /**
     * Récupère les taux d'indisponibilité pour des applications
     */
    public function getTauxIndisposByApplications(
        array $applications,
        int $jour,
        int $mois,
        int $annee,
        int $vue = 1,
        int $periode = 4,
        bool $moyenne = false,
        bool $archive = false
    ): array {
        $date = new \DateTime("$annee-$mois-$jour");

        $config = new TauxCalculationConfig(
            vue: $vue,
            periode: $periode,
            moyenne: $moyenne,
            archive: $archive
        );

        return $this->tauxIndispoService->getByApplications($applications, $date, $config);
    }

    public function topIndispoBrut(\DateTime $date): array
    {
        return $this->topIndispoService->getTopBrut($date);
    }

    /**
     * Récupère le top des indisponibilités
     */
    public function topIndispoBrutByApplications(
        array $applications,
        \DateTime $date,
        int $vue = 3,
        array $options = []
    ): array {
        return $this->topIndispoService->getTopByApplications(
            $applications,
            $date,
            $vue,
            $options
        );
    }

    /**
     * Récupère les données de comparaison
     */
    public function getComparaisonData(\DateTime $date, array|false $applications = false): array
    {
        return $this->comparaisonService->getComparaisonData($date, $applications);
    }

    /**
     * Récupère les données minimales de comparaison
     */
    public function getComparaisonMiniData(\DateTime $date): array
    {
        return $this->comparaisonService->getComparaisonMiniData($date);
    }

    /**
     * Récupère la liste des applications depuis les indispos
     */
    public function getListApplicationsFromIndispoByDate(
        int $jour,
        int $mois,
        int $annee,
        int $limit = 10
    ): array {
        $date = new \DateTime("$annee-$mois-$jour");

        return $this->applicationFinder->findTopApplications($date, $limit);
    }

    /**
     * Récupère les taux d'indisponibilité par item (scénario)
     */
    public function getTauxIndisposByItem(
        string $type,
        int $id,
        string $key,
        \DateTime $date
    ): array {
        if ($type !== 'scenario') {
            return [];
        }

        return $this->tauxIndispoService->getByScenarioItem($id, $key, $date);
    }
}
