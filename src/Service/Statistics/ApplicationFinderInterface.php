<?php

namespace App\Service\Statistics;

/**
 * Interface pour la recherche d'applications
 * Respecte ISP et DIP
 */
interface ApplicationFinderInterface
{
    /**
     * Trouve les applications avec le plus d'indisponibilités
     *
     * @param \DateTime $date Date de référence
     * @param int $limit Nombre maximum d'applications à retourner
     * @return array Liste des IDs d'applications
     */
    public function findTopApplications(\DateTime $date, int $limit = 10): array;
}
