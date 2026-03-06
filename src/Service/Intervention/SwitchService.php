<?php

namespace App\Service\Intervention;

use App\Entity\Application;
use App\Entity\SSwitch;
use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use Doctrine\ORM\EntityManagerInterface;

class SwitchService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheHandler $cacheHandler,
    ) {
    }

    public function invalidateCache(): void
    {
        $this->cacheHandler->clear('intervention_switch');
    }

    /**
     * @return SwitchDto[]
     */
    public function getByApplication(Application $application, \DateTime $date, bool $formatDates = false): array
    {
        $switchs = $this->entityManager
            ->getRepository(SSwitch::class)
            ->getAnomaliesSwitchByApplication($application, $date);

        return array_map(
            fn(SSwitch $entity) => SwitchDto::fromEntity($entity, $formatDates),
            $switchs
        );
    }

    /**
     * @return array<int, SwitchDto[]> indexed by application ID
     */
    public function getAllGroupedByApplication(): array
    {
        $switchs = $this->entityManager
            ->getRepository(SSwitch::class)
            ->getAnomaliesSwitch();

        $data = [];
        foreach ($switchs as $entity) {
            if (!$entity->getApplication()) {
                continue;
            }
            $data[$entity->getApplication()->getId()][] = SwitchDto::fromEntity($entity);
        }

        return $data;
    }

    public function getRssFeed(): array
    {
        $value = $this->cacheHandler->get(
            'intervention_switch',
            KeysCacheModel::ISAC_SWITCH,
            fn(): array => $this->entityManager->getRepository(SSwitch::class)->rssSwitch()
        );

        return $value ?? [];
    }

    public function getForHistoriqueByDate(Application $application, \DateTime $start, \DateTime $end): array
    {
        return $this->entityManager
            ->getRepository(SSwitch::class)
            ->switchForHistoriqueByDate($application, $start, $end);
    }
}
