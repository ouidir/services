<?php

namespace App\Service\Search;

interface SearchableInterface
{
    /**
     * Recherche dans une entité spécifique
     *
     * @param string $query
     * @return array
     */
    public function find(string $query): array;

    /**
     * Retourne le type de recherche
     *
     * @return string
     */
    public function getType(): string;
}
