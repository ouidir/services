<?php

namespace App\Service\Planning;

use App\Entity\Calendrier;

interface CalendrierFactoryInterface
{
    public function createFromPlanning(array $planning): Calendrier;

    public function createWithDefaultPeriodes(): Calendrier;
}
