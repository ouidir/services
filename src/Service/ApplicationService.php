<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\User;
use App\Service\Application\ApplicationDataBuilder;
use App\Handler\CacheHandler\ApplicationCacheHandler;
use App\Service\Application\ApplicationHistoryService;
use App\Service\Application\ApplicationAccessService;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Main application service - delegates to specialized services.
 * Follows Single Responsibility Principle.
 */
class ApplicationService
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ApplicationCacheHandler $cacheService,
        private ApplicationDataBuilder $dataBuilder,
        private ApplicationHistoryService $historyService,
        private ApplicationAccessService $accessService,
    ) {
    }

    /**
     * @return array<Application>
     */
    public function getActiveApplications(): array
    {
        return $this->applicationRepository->getActiveApplications();
    }

    /**
     * @return array<int, array|null>
     */
    public function getCachedApplications(bool $withScenario = true): array
    {
        $applications = new ArrayCollection($this->applicationRepository->getActiveApplications());
        return $this->cacheService->getCachedApplications($applications, $withScenario);
    }

    /**
     * @return array<int, array>
     */
    public function getLightApplications(bool $withScenario = true, bool $withSimulateur = false): array
    {
        return $this->dataBuilder->buildLightApplicationsList($withScenario, $withSimulateur);
    }

    /**
     * @return array<int, array>
     */
    public function getApplicationsWithArchive(): array
    {
        return $this->dataBuilder->buildApplicationsWithArchive();
    }

    /**
     * @return array<Application>
     */
    public function getApplicationsByUser(User $user, bool $refreshCache = false): array
    {
        return $this->accessService->getApplicationsByUser($user, $refreshCache);
    }

    /**
     * @return array<int, array>
     */
    public function getHistoricalApplications(\DateTimeInterface $date): array
    {
        return $this->historyService->getApplicationStatusAtDate($date);
    }

    public function getCachedApplication(?Application $application): ?array
    {
        return $this->cacheService->getCachedApplication($application);
    }

    public function getCachedApplicationById(int $id): array
    {
        return $this->cacheService->getCachedApplicationById($id);
    }

    public function refreshApplicationCache(Application $application): array
    {
        return $this->cacheService->refreshApplication($application);
    }
}
