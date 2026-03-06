<?php

namespace App\Service\Application\Filter;

use App\Entity\Zone;

class ZoneApplicationFilter implements ApplicationFilterInterface
{
    public function __construct(
        private readonly Zone $zone
    ) {
    }

    public function filter(array $applications): array
    {
        $filtered = [];

        foreach ($applications as $app) {
            if ($this->hasZone($app)) {
                $filtered[$app['id']] = $app;
            }
        }

        return $filtered;
    }

    private function hasZone(array $app): bool
    {
        return isset($app['zone'])
            && $app['zone']['id'] === $this->zone->getId();
    }
}
