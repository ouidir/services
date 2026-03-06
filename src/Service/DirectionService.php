<?php

namespace App\Service;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\DirectionRepository;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DirectionService
{
    public function __construct(
        private DirectionRepository $directionRepository,
        private NormalizerInterface $normalizer,
        private CacheHandler $cacheHandler,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * Method getDirections
     *
     * @return iterable
     */
    public function getDirections(): array
    {
        $value = $this->cacheHandler->get(
            'direction',
            KeysCacheModel::LIST_DIRECTION,
            function (): array {
                $directions = $this->directionRepository->findBy([], ['position' => 'ASC']);
                return $this->normalizer->normalize($directions, null, ['groups' => 'level2']);
            }
        );

        return $value ?? [];
    }

    public function getListDirections(): array
    {
        $directions = [];

        foreach ($this->directionRepository->findBy([], ['position' => 'ASC']) as $direction) {
            $directions[$direction->getLibelle()] = $direction->getId();
        }

        return $directions;
    }
}
