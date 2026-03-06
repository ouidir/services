<?php

namespace App\Service\Statistics;

interface TopIndispoServiceInterface
{
    public function getTopByApplications(
        array $applications,
        \DateTime $date,
        int $vue = 3,
        array $options = []
    ): array;

    public function getTopBrut(\DateTime $date): array;
}
