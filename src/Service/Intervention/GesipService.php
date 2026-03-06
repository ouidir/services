<?php

namespace App\Service\Intervention;

use App\Entity\Application;
use App\Entity\Gesip;
use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use Doctrine\ORM\EntityManagerInterface;

class GesipService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheHandler $cacheHandler,
    ) {
    }

    public function invalidateCache(): void
    {
        $this->cacheHandler->clear('intervention_gesip');
    }

    /**
     * @return GesipDto[]
     */
    public function getByApplication(Application $application, \DateTime $date, bool $formatDates = false): array
    {
        $gesips = $this->entityManager
            ->getRepository(Gesip::class)
            ->getAnomaliesGesipByApplication($application, $date);

        return array_map(
            fn(Gesip $entity) => GesipDto::fromEntity($entity, $formatDates),
            $gesips
        );
    }

    /**
     * @return array<int, GesipDto[]> indexed by application ID
     */
    public function getAllGroupedByApplication(): array
    {
        $gesips = $this->entityManager
            ->getRepository(Gesip::class)
            ->getAnomaliesGesip();

        $data = [];
        foreach ($gesips as $entity) {
            if (!$entity->getApplication()) {
                continue;
            }
            $data[$entity->getApplication()->getId()][] = GesipDto::fromEntity($entity);
        }

        return $data;
    }

    public function getRssFeed(): array
    {
        $value = $this->cacheHandler->get(
            'intervention_gesip',
            KeysCacheModel::ISAC_GESIP,
            fn(): array => $this->entityManager->getRepository(Gesip::class)->rssGesip()
        );

        return $value ?? [];
    }

    public function getForHistoriqueByDate(Application $application, \DateTime $start, \DateTime $end): array
    {
        return $this->entityManager
            ->getRepository(Gesip::class)
            ->gesipForHistoriqueByDate($application, $start, $end);
    }
}
