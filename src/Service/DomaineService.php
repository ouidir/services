<?php

namespace App\Service;

use App\Handler\CacheHandler\CacheHandler;
use App\Model\KeysCacheModel;
use App\Repository\DomaineRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DomaineService
{
    public function __construct(
        private DomaineRepository $domaineRepository,
        private CacheHandler $cacheHandler,
        private NormalizerInterface $normalizer
    ) {
    }

    /**
     * Method getDomaines
     *
     * @return iterable
     */
    public function getDomaines(): array
    {
        $value = $this->cacheHandler->get(
            'domaine',
            KeysCacheModel::LIST_DOMAINE,
            function (): array {
                $domaines = $this->domaineRepository->findBy([], ['position' => 'ASC']);
                return $this->normalizer->normalize($domaines, null, ['groups' => 'level2']);
            }
        );

        return $value ?? [];
    }

    public function getListDomaines()
    {
        $domaines = [];

        foreach ($this->domaineRepository->findBy([], ['position' => 'ASC']) as $domaine) {
            $domaines[$domaine->getLibDomaine()] = $domaine->getId();
        }

        return $domaines;
    }
}
