<?php

namespace App\Service\Images\Screenshot;

use App\Entity\Scenario;

interface ScreenshotArchiveInterface
{
    public function extract(Scenario $scenario): void;

    public function delete(Scenario $scenario): void;

    public function exists(Scenario $scenario): bool;
}
