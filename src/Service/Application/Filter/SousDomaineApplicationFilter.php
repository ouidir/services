<?php

namespace App\Service\Application\Filter;

use App\Entity\SousDomaine;

class SousDomaineApplicationFilter implements ApplicationFilterInterface
{
    public function __construct(
        private readonly SousDomaine $sousDomaine
    ) {
    }

    public function filter(array $applications): array
    {
        $filtered = [];

        foreach ($applications as $app) {
            if ($this->hasSousDomaine($app)) {
                $filtered[$app['id']] = $app;
            }
        }

        return $filtered;
    }

    private function hasSousDomaine(array $app): bool
    {
        return isset($app['sousDomaine'])
            && $app['sousDomaine']['id'] === $this->sousDomaine->getId();
    }
}
