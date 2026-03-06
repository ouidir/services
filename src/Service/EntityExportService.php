<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Scenario;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service dédié à l'export d'entités métier en JSON
 * Respecte le SRP en séparant les exports d'entités du service général
 */
class EntityExportService
{
    public function __construct(
        private readonly JsonExportService $jsonExportService
    ) {
    }

    /**
     * Exporte une application en JSON
     */
    public function exportApplication(Application $application): Response
    {
        return $this->jsonExportService->export(
            data: [$application],
            filename: sprintf('application_%s_%s', $application->getTrigramme(), date('Y-m-d')),
            serializationGroups: ['export_application']
        );
    }

    /**
     * Exporte un scénario en JSON
     */
    public function exportScenario(Scenario $scenario): Response
    {
        return $this->jsonExportService->export(
            data: [$scenario],
            filename: sprintf('scenario_%s_%s', $scenario->getIdentifiant(), date('Y-m-d')),
            serializationGroups: ['export_scenario']
        );
    }

    /**
     * Exporte plusieurs applications en JSON
     */
    public function exportApplications(array $applications): Response
    {
        return $this->jsonExportService->export(
            data: $applications,
            filename: sprintf('applications_%s', date('Y-m-d')),
            serializationGroups: ['export_application']
        );
    }

    /**
     * Exporte plusieurs scénarios en JSON
     */
    public function exportScenarios(array $scenarios): Response
    {
        return $this->jsonExportService->export(
            data: $scenarios,
            filename: sprintf('scenarios_%s', date('Y-m-d')),
            serializationGroups: ['export_scenario']
        );
    }
}
