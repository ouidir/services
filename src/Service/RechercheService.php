<?php

namespace App\Service;

class RechercheService
{
    /**
     * @param iterable<SearchableInterface> $searchProviders
     */
    public function __construct(
        private iterable $searchProviders
    ) {
    }

    public function recherche($query)
    {
        $results = [];
        foreach ($this->searchProviders as $provider) {
            $results = array_merge($results, $provider->find(strtolower($query)));
        }

        return $results;
    }
}
