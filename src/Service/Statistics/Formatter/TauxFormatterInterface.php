<?php

namespace App\Service\Statistics\Formatter;

interface TauxFormatterInterface
{
    public function sortByName(array $data): array;

    public function sortByApplication(array $data): array;
}
