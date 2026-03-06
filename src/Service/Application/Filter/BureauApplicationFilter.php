<?php

namespace App\Service\Application\Filter;

use App\Entity\BureauEtablissement;

class BureauApplicationFilter implements ApplicationFilterInterface
{
    public function __construct(
        private readonly BureauEtablissement $bureau
    ) {
    }

    public function filter(array $applications): array
    {
        $filtered = [];

        foreach ($applications as $app) {
            if ($this->hasBureau($app)) {
                $filtered[$app['id']] = $app;
            }
        }

        return $filtered;
    }

    private function hasBureau(array $app): bool
    {
        if (!isset($app['directions'])) {
            return false;
        }

        foreach ($app['directions'] as $direction) {
            if (isset($direction['bureau']) && $direction['bureau'] === $this->bureau->getId()) {
                return true;
            }
        }

        return false;
    }
}
