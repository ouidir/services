<?php

namespace App\Service\Search;

use App\Service\DomaineService;
use App\Service\Search\SearchableInterface;
use App\Service\Search\StringMatcherTrait;

class DomaineSearcher implements SearchableInterface
{
    use StringMatcherTrait;

    public function __construct(
        private readonly DomaineService $domaineService
    ) {
    }

    public function find(string $query): array
    {
        $results = [];
        $domaines = $this->domaineService->getDomaines();

        if (!$domaines) {
            return $results;
        }

        foreach ($domaines as $domaine) {
            $results = array_merge(
                $results,
                $this->searchInDomaine($domaine, $query),
                $this->searchInSousDomaines($domaine, $query)
            );
        }

        return $results;
    }

    public function getType(): string
    {
        return 'domaine';
    }

    private function searchInDomaine(array $domaine, string $query): array
    {
        if (!$this->matches($domaine['libDomaine'], $query)) {
            return [];
        }

        return [[
            'id' => $domaine['id'],
            'nom' => $domaine['libDomaine'],
            'type' => 'domaine',
            'parent' => null
        ]];
    }

    private function searchInSousDomaines(array $domaine, string $query): array
    {
        $results = [];

        foreach ($domaine['sousDomaine'] as $sousDomaine) {
            if ($this->matches($sousDomaine['libelle'], $query)) {
                $results[] = [
                    'id' => $sousDomaine['id'],
                    'nom' => $sousDomaine['libelle'],
                    'type' => 'sous_domaine',
                    'parent' => $domaine['id']
                ];
            }
        }

        return $results;
    }
}
