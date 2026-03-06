<?php

namespace App\Service\Statistics;

interface ComparaisonServiceInterface
{
    public function getComparaisonData(\DateTime $date, array|false $applications = false): array;

    public function getComparaisonMiniData(\DateTime $date): array;
}
