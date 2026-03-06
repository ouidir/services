<?php

namespace App\Service\Search;

use App\Service\ApplicationService;
use App\Service\Search\SearchableInterface;
use App\Service\Search\StringMatcherTrait;

class ApplicationSearcher implements SearchableInterface
{
    use StringMatcherTrait;

    public function __construct(
        private readonly ApplicationService $applicationService,
    ) {
    }

    public function find(string $query): array
    {
        $results = [];
        $applications = $this->applicationService->getActiveApplications();

        if (!$applications) {
            return $results;
        }

        foreach ($applications as $itemApp) {
            $app = $this->applicationService->getCachedApplicationById($itemApp->getId());

            if ($this->matches($app['nom'], $query)) {
                $results[] = [
                    'id' => $app['id'],
                    'nom' => $app['nom'],
                    'type' => 'application'
                ];
            }
        }

        return $results;
    }

    public function getType(): string
    {
        return 'application';
    }
}
