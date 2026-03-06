<?php

namespace App\Service\Search;

use App\Service\DirectionService;
use App\Service\Search\SearchableInterface;
use App\Service\Search\StringMatcherTrait;

class DirectionSearcher implements SearchableInterface
{
    use StringMatcherTrait;

    public function __construct(
        private readonly DirectionService $directionService,
    ) {
    }

    public function find(string $query): array
    {
        $results = [];
        $directions = $this->directionService->getDirections();

        if (!$directions) {
            return $results;
        }

        foreach ($directions as $direction) {
            $results = array_merge(
                $results,
                $this->searchInDirection($direction, $query),
                $this->searchInBureaux($direction, $query)
            );
        }

        return $results;
    }

    public function getType(): string
    {
        return 'direction';
    }

    private function searchInDirection(array $direction, string $query): array
    {
        if (!$this->matches($direction['libelle'], $query)) {
            return [];
        }

        return [[
            'id' => $direction['id'],
            'nom' => $direction['libelle'],
            'type' => 'direction',
            'parent' => null
        ]];
    }

    private function searchInBureaux(array $direction, string $query): array
    {
        $results = [];

        foreach ($direction['bureauEtablissements'] as $bureau) {
            if ($this->matches($bureau['libelle'], $query)) {
                $results[] = [
                    'id' => $bureau['id'],
                    'nom' => $bureau['libelle'],
                    'type' => 'bureau_etablissement',
                    'parent' => $direction['id']
                ];
            }
        }

        return $results;
    }
}
