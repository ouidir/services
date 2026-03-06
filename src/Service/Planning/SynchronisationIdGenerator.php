<?php

namespace App\Service\Planning;

use App\Service\InjecteurService;

class SynchronisationIdGenerator
{
    public function __construct(private readonly InjecteurService $injecteurService)
    {
    }

    public function generateNextId(): int
    {
        $maxId = 0;
        foreach ($this->injecteurService->getAllinjecteurs() as $injecteur) {
            $maxId = max($maxId, $injecteur->getSynchronisation()->getIdSynchroPlanning());
        }

        return $maxId;
    }
}
