<?php

namespace App\Service;

use App\Entity\BureauEtablissement;
use App\Entity\Direction;
use App\Entity\Domaine;
use App\Entity\SousDomaine;
use App\Entity\User;
use App\Entity\Zone;
use App\Handler\CacheHandler\ApplicationCacheHandlerInterface;
use App\Service\Application\Filter\BureauApplicationFilter;
use App\Service\Application\Filter\DirectionApplicationFilter;
use App\Service\Application\Filter\DomaineApplicationFilter;
use App\Service\Application\Filter\PiloteApplicationFilter;
use App\Service\Application\Filter\SousDomaineApplicationFilter;
use App\Service\Application\Filter\ZoneApplicationFilter;
use Doctrine\Common\Collections\ArrayCollection;

class ApplicationFilterService
{
    public function __construct(
        private readonly ApplicationService $applicationService,
        private readonly ApplicationCacheHandlerInterface $applicationCacheHandler
    ) {
    }

    public function getApplicationsByPilote(User $user)
    {
        $filter = new PiloteApplicationFilter($user);
        return $this->filterAndTransform($filter);
    }

    public function getApplicationsByDirection(Direction $direction)
    {
        $filter = new DirectionApplicationFilter($direction);
        return $this->filterAndTransform($filter);
    }

    public function getApplicationsByBureau(BureauEtablissement $bureau)
    {
        $filter = new BureauApplicationFilter($bureau);
        return $this->filterAndTransform($filter);
    }

    public function getApplicationsByDomaine(Domaine $domaine)
    {
        $filter = new DomaineApplicationFilter($domaine);
        return $this->filterAndTransform($filter);
    }

    public function getApplicationsBySousDomaine(SousDomaine $sousDomaine)
    {
        $filter = new SousDomaineApplicationFilter($sousDomaine);
        return $this->filterAndTransform($filter);
    }

    public function getApplicationsByZone(Zone $zone)
    {
        $filter = new ZoneApplicationFilter($zone);
        return $this->filterAndTransform($filter);
    }

    private function filterAndTransform($filter): array
    {
        $apps = $this->applicationService->getActiveApplications();
        $collectionApplications = new ArrayCollection($apps);
        $applications = $this->applicationCacheHandler->getCachedApplications($collectionApplications);
        $filtered = $filter->filter($applications);

        return array_map(
            function ($application) {
                return [
                    'id' => $application['id'],
                    'nom' => $application['nom'],
                    'nbrSwitchs' => $application['nbrSwitchs'] ?? 0,
                    'nbrGesips' => $application['nbrGesips'] ?? 0,
                    'nbrInformations' => $application['nbrInformations'] ?? 0,
                    'status' => $application['status'] ?? null
                ];
            },
            $filtered
        );
    }
}
