<?php

namespace App\Service\Application\Filter;

use App\Entity\User;

class PiloteApplicationFilter implements ApplicationFilterInterface
{
    public function __construct(
        private readonly User $pilote
    ) {
    }

    public function filter(array $applications): array
    {
        $filtered = [];

        foreach ($applications as $app) {
            if ($this->isPilote($app)) {
                $filtered[$app['id']] = $app;
            }
        }

        return $filtered;
    }

    private function isPilote(array $app): bool
    {
        return $this->isPilotePrincipal($app) || $this->isPiloteSecondaire($app);
    }

    private function isPilotePrincipal(array $app): bool
    {
        return isset($app['pilotePrincipal']['id'])
            && $app['pilotePrincipal']['id'] === $this->pilote->getId();
    }

    private function isPiloteSecondaire(array $app): bool
    {
        return isset($app['piloteSecondaire']['id'])
            && $app['piloteSecondaire']['id'] === $this->pilote->getId();
    }
}
