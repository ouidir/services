<?php

namespace App\Service\Statistics;

use App\Repository\Interface\PeriodesIndisposRepositoryInterface;
use DateInterval;
use Psr\Log\LoggerInterface;

/**
 * Service de recherche d'applications par indisponibilité
 * Respecte SRP : Recherche et filtrage d'applications uniquement
 */
class ApplicationFinder implements ApplicationFinderInterface
{
    public function __construct(
        private PeriodesIndisposRepositoryInterface $indisposRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function findTopApplications(\DateTime $date, int $limit = 10): array
    {
        $year = (int)$date->format('Y');
        $debutAnnee = new \DateTime("{$year}-01-01");
        $finAnnee = (clone $debutAnnee)->add(new DateInterval('P1Y'));

        $this->logger->info(
            "Recherche des top {$limit} applications avec indisponibilités " .
            "pour la période {$debutAnnee->format('Y-m-d')} à {$finAnnee->format('Y-m-d')}"
        );

        $results = $this->indisposRepository->getListApplicationsFromIndispoByDate(
            $debutAnnee,
            $finAnnee,
            $limit
        );

        // Extraction uniquement des IDs
        $applicationIds = array_map(fn($app) => $app['id'], $results);

        $this->logger->info(
            "Trouvé " . count($applicationIds) . " applications avec indisponibilités"
        );

        return $applicationIds;
    }
}
