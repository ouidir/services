<?php

namespace App\Service\Application\Filter;

use App\Entity\Domaine;

class DomaineApplicationFilter implements ApplicationFilterInterface
{
    public function __construct(
        private readonly Domaine $domaine
    ) {
    }

    public function filter(array $applications): array
    {
        $filtered = [];

        foreach ($applications as $app) {
            if ($this->hasDomaine($app)) {
                $filtered[$app['id']] = $app;
            }
        }

        return $filtered;
    }

    private function hasDomaine(array $app): bool
    {
        return isset($app['domaine'])
            && $app['domaine']['id'] === $this->domaine->getId();
    }
}
