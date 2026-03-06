<?php

namespace App\Service\Application\Filter;

use App\Entity\Direction;

class DirectionApplicationFilter implements ApplicationFilterInterface
{
    public function __construct(
        private readonly Direction $direction
    ) {
    }

    public function filter(array $applications): array
    {
        $filtered = [];

        foreach ($applications as $app) {
            if ($this->hasDirection($app)) {
                $filtered[$app['id']] = $app;
            }
        }

        return $filtered;
    }

    private function hasDirection(array $app): bool
    {
        if (!isset($app['directions'])) {
            return false;
        }

        foreach ($app['directions'] as $direction) {
            if (isset($direction['direction']) && $direction['direction'] === $this->direction->getId()) {
                return true;
            }
        }

        return false;
    }
}
