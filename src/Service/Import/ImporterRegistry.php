<?php

namespace App\Service\Import;

use App\Handler\DataHandler\ImporterInterface;
use Psr\Log\LoggerInterface;

/**
 * Registry pattern pour gérer les importers
 * Respecte SRP et OCP (ouvert à l'extension, fermé à la modification)
 */
class ImporterRegistry
{
    /** @var ImporterInterface[] */
    private array $importers = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        iterable $importers
    ) {
        foreach ($importers as $importer) {
            $this->register($importer);
        }

        $this->logger->info('ImporterRegistry initialized', [
            'importers_count' => count($this->importers)
        ]);
    }

    /**
     * Enregistre un nouvel Importer
     */
    public function register(ImporterInterface $importer): void
    {
        $this->importers[] = $importer;
    }

    /**
     * Trouve un Importer compatible avec le type donné
     */
    public function findImporter(string $type): ?ImporterInterface
    {
        foreach ($this->importers as $importer) {
            if ($importer->supports($type)) {
                $this->logger->debug(sprintf('Found importer for type: %s', $type), [
                    'importer' => get_class($importer)
                ]);
                return $importer;
            }
        }

        $this->logger->warning(sprintf('No importer found for type: %s', $type), [
            'available_types' => $this->getAvailableTypes()
        ]);

        return null;
    }

    /**
     * Retourne tous les importers enregistrés
     *
     * @return ImporterInterface[]
     */
    public function getAll(): array
    {
        return $this->importers;
    }

    /**
     * Retourne les types d'importers disponibles
     */
    public function getAvailableTypes(): array
    {
        return array_map(
            fn(ImporterInterface $importer) => get_class($importer),
            $this->importers
        );
    }
}
