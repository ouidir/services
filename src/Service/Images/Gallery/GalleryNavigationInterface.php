<?php

namespace App\Service\Images\Gallery;

use App\Service\ApplicationService;

interface GalleryNavigationInterface
{
    public function getNavigation(
        ApplicationService $applicationService,
        string $application,
        string $scenario,
        ?string $date = null,
        ?string $time = null
    ): array;
}
