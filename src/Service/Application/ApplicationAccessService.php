<?php

namespace App\Service\Application;

use App\Entity\Application;
use App\Entity\User;
use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\ApplicationRepository;

/**
 * Manages user access to applications.
 */
class ApplicationAccessService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private CacheHandler $cacheHandler,
        private ScenarioFilter $scenarioFilter,
    ) {
    }

    /**
     * @return array<Application>
     */
    public function getApplicationsByUser(User $user, bool $refreshCache = false): array
    {
        $cacheKey = KeysCacheModel::INFO_USER_APPLICATION . $user->getId();

        if ($refreshCache) {
            $this->cacheHandler->delete('user', $cacheKey);
        }

        $value = $this->cacheHandler->get(
            'user',
            $cacheKey,
            fn() => $this->fetchApplicationsForUser($user),
            900
        );

        return $value ?? [];
    }

    /**
     * @return array<Application>
     */
    private function fetchApplicationsForUser(User $user): array
    {
        $applications = $this->isUserPilote($user)
            ? $this->applicationRepository->getApplicationByPilote($user)
            : $this->applicationRepository->getApplicationByUser($user);

        if (empty($applications)) {
            return [];
        }

        return array_map(
            fn(Application $app) => $this->scenarioFilter->filterVisibleScenarios($app),
            $applications
        );
    }

    private function isUserPilote(User $user): bool
    {
        return in_array('ROLE_PILOTE', $user->getRoles(), true);
    }
}
