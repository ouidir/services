<?php

namespace App\Service;

use App\Entity\Scenario;
use App\Service\Correction\DataDto;
use App\Service\Correction\ScenarioPeriodeManager;
use App\Service\Correction\CorrectionStrategyInterface;
use Psr\Log\LoggerInterface;

class CorrectionsService
{
    public function __construct(
        private ScenarioService $scenarioService,
        private ScenarioPeriodeManager $periodeManager,
        private CorrectionStrategyInterface $executionStrategy,
        private CorrectionStrategyInterface $indisponibiliteStrategy,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Lance la correction des executions pour plusieurs scénarios
     */
    public function launch(DataDto $data): void
    {
        foreach ($data->scenarios as $scenarioId) {
            try {
                $scenario = $this->scenarioService->find($scenarioId);

                if (!$scenario) {
                    $this->logger->warning('Scénario introuvable', ['scenario_id' => $scenarioId]);
                    continue;
                }

                $this->correctScenario($scenario, $data);
            } catch (\Exception $e) {
                $this->handleCorrectionError($scenarioId, $e);
            }
        }
    }

    /**
     * Corrige un scénario unique
     */
    private function correctScenario(Scenario $scenario, DataDto $data): void
    {
        $originScenario = $this->periodeManager->prepareScenario($scenario);

        try {
            // Correction des exécutions
            $this->executionStrategy->correct($scenario, $data);

            // Correction des indisponibilités
            $this->indisponibiliteStrategy->correct($scenario, $data);

            // Finalisation du scénario
            $this->periodeManager->finalizeScenario($scenario, $originScenario);

            $this->logger->info('Correction terminée avec succès', [
                'scenario_id' => $scenario->getId(),
                'scenario_nom' => $scenario->getNom(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la correction du scénario', [
                'scenario_id' => $scenario->getId(),
                'scenario_nom' => $scenario->getNom(),
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Correction à partir de données JSON
     */
    public function correctionExecutionsJson(array $data): array
    {
        $messages = [];

        foreach ($data as $item) {
            try {
                $scenario = $this->scenarioService->findOneByIdentifiant($item['identifiant']);

                if (!$scenario) {
                    $messages[] = $this->createErrorMessage($item['identifiant'], 'Scénario introuvable');
                    continue;
                }

                // Utilise un DTO avec un seul scénario
                $singleScenarioDto = $this->createDtoFromArray($scenario->getId(), $item);

                $this->correctScenario($scenario, $singleScenarioDto);
                $messages[] = $this->createSuccessMessage($scenario, 'exécutions');
            } catch (\Exception $e) {
                $messages[] = $this->createErrorMessage(
                    $item['identifiant'] ?? 'inconnu',
                    $e->getMessage()
                );
            }
        }

        return $messages;
    }

    /**
     * Correction des indisponibilités à partir de données JSON
     */
    public function correctionIndisponibilitesJson(array $data): array
    {
        $messages = [];

        foreach ($data as $item) {
            try {
                $scenario = $this->scenarioService->findOneByIdentifiant($item['identifiant']);

                if (!$scenario) {
                    $messages[] = $this->createErrorMessage($item['identifiant'], 'Scénario introuvable');
                    continue;
                }

                if (!$scenario->getFlagIndispo()) {
                    $messages[] = $this->createWarningMessage($scenario, 'Flag indispo désactivé');
                    continue;
                }

                $dto = new DataDto(
                    [$scenario->getId()],
                    new \DateTimeImmutable($item['debut']),
                    new \DateTimeImmutable($item['fin']),
                    [0], // Ne peut pas être vide
                    0, // Pas de code à affecter
                    null
                );

                $originScenario = $this->periodeManager->prepareScenario($scenario);
                $this->indisponibiliteStrategy->correct($scenario, $dto);
                $this->periodeManager->finalizeScenario($scenario, $originScenario);

                $messages[] = $this->createSuccessMessage($scenario, 'indisponibilités');
            } catch (\Exception $e) {
                $messages[] = $this->createErrorMessage(
                    $item['identifiant'] ?? 'inconnu',
                    $e->getMessage()
                );
            }
        }

        return $messages;
    }

    private function createDtoFromArray(int $idScenario, array $data): DataDto
    {
        return new DataDto(
            [$idScenario],
            new \DateTimeImmutable($data['debut']),
            new \DateTimeImmutable($data['fin']),
            $data['codeACorriger'],
            $data['codeAAffecter'],
            $data['commentaireModifie'] ?? null
        );
    }

    private function createSuccessMessage(Scenario $scenario, string $type): string
    {
        return sprintf(
            'Correction des %s - Scénario ID: %d, Nom: %s',
            $type,
            $scenario->getId(),
            $scenario->getNom()
        );
    }

    private function createErrorMessage(string $identifiant, string $error): string
    {
        return sprintf(
            'Erreur - Identifiant: %s, Message: %s',
            $identifiant,
            $error
        );
    }

    private function createWarningMessage(Scenario $scenario, string $warning): string
    {
        return sprintf(
            'Avertissement - Scénario ID: %d, Nom: %s, Message: %s',
            $scenario->getId(),
            $scenario->getNom(),
            $warning
        );
    }

    private function handleCorrectionError(mixed $scenarioId, \Exception $e): void
    {
        $this->logger->error('Correction des exécutions - Scénario en erreur', [
            'scenario_id' => $scenarioId,
            'error_message' => $e->getMessage(),
            'exception' => $e,
        ]);
    }
}
