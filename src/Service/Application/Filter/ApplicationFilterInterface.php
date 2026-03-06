<?php

namespace App\Service\Application\Filter;

interface ApplicationFilterInterface
{
    /**
     * Filtre les applications selon un critère
     *
     * @param array $applications Applications en cache
     * @return array
     */
    public function filter(array $applications): array;
}
