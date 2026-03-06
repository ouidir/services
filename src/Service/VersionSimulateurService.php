<?php

namespace App\Service;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\VersionSimulateurRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VersionSimulateurService
{
    public function __construct(
        private VersionSimulateurRepository $versionSimulateurRepository,
        private NormalizerInterface $normalizer,
        private CacheHandler $cacheHandler
    ) {
    }

    /**
     * Method getAll
     *
     * @return iterable
     */
    public function getAll(): array
    {
        $value = $this->cacheHandler->get(
            'version_simulateur',
            KeysCacheModel::LIST_VERSION_SIMULATEUR,
            function (): array {
                $list = $this->versionSimulateurRepository->findAll();
                return $this->normalizer->normalize($list, null, ['groups' => 'level1']);
            }
        );

        return $value ?? [];
    }
}
