<?php

namespace App\Service;

use App\Entity\Application;
use App\Service\Intervention\GesipService;
use App\Service\Intervention\SwitchService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Facade orchestrating Switch and Gesip concerns together.
 * Prefer injecting GesipService or SSwitchService directly when only one domain is needed.
 */
class InterventionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GesipService $gesipService,
        private readonly SwitchService $switchService,
        private readonly ApplicationService $applicationService,
    ) {
    }

    public function ecritureSwitch(array $applicationIds): void
    {
        $this->switchService->invalidateCache();
        $this->refreshApplicationsCache($applicationIds);
    }

    public function ecritureGesip(array $applicationIds): void
    {
        $this->gesipService->invalidateCache();
        $this->refreshApplicationsCache($applicationIds);
    }

    /**
     * @return array{gesips: array[], switchs: array[]}
     */
    public function getGesipSwitchByIdApp(int $applicationId): array
    {
        $application = $this->entityManager->getRepository(Application::class)->find($applicationId);
        $date        = new \DateTime();

        $gesips  = $this->gesipService->getByApplication($application, $date, formatDates: true);
        $switchs = $this->switchService->getByApplication($application, $date, formatDates: true);

        return [
            'gesips'  => array_map(fn($dto) => $dto->toArray(), $gesips),
            'switchs' => array_map(fn($dto) => $dto->toArray(), $switchs),
        ];
    }

    /**
     * @return array{gesips: array<int, array[]>, switchs: array<int, array[]>}
     */
    public function getGesipSwitch(): array
    {
        $gesips  = $this->gesipService->getAllGroupedByApplication();
        $switchs = $this->switchService->getAllGroupedByApplication();

        return [
            'gesips'  => array_map(fn($group) => array_map(fn($dto) => $dto->toArray(), $group), $gesips),
            'switchs' => array_map(fn($group) => array_map(fn($dto) => $dto->toArray(), $group), $switchs),
        ];
    }

    public function getGesipByIdApp(Application $application, \DateTime $date): array
    {
        return array_map(
            fn($dto) => $dto->toArray(),
            $this->gesipService->getByApplication($application, $date)
        );
    }

    public function getSwitchByIdApp(Application $application, \DateTime $date): array
    {
        return array_map(
            fn($dto) => $dto->toArray(),
            $this->switchService->getByApplication($application, $date)
        );
    }

    public function gesip(): array
    {
        return $this->gesipService->getRssFeed();
    }

    public function switch(): array
    {
        return $this->switchService->getRssFeed();
    }    

    public function gesipForHistoriqueByDate(Application $application, \DateTime $start, \DateTime $end): array
    {
        return $this->gesipService->getForHistoriqueByDate($application, $start, $end);
    }

    public function switchForHistoriqueByDate(Application $application, \DateTime $start, \DateTime $end): array
    {
        return $this->switchService->getForHistoriqueByDate($application, $start, $end);
    }

    private function refreshApplicationsCache(array $applicationIds): void
    {
        foreach ($applicationIds as $applicationId) {
            $app = $this->entityManager->getRepository(Application::class)->find($applicationId);
            if ($app !== null) {
                $this->applicationService->refreshApplicationCache($app);
            }
        }
    }
}
