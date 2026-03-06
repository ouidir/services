<?php

namespace App\Service\Search;

trait StringMatcherTrait
{
    private function matches(string $haystack, string $needle): bool
    {
        return str_contains(strtolower($haystack), strtolower($needle));
    }
}
