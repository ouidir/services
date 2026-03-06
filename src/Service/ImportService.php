<?php

namespace App\Service;

use App\Service\Import\ImporterRegistry;
use Psr\Log\LoggerInterface;

class ImportService
{
    public function __construct(
        private readonly ImporterRegistry $importerRegistry,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Importer des données selon le type spécifié
     *
     * @throws \InvalidArgumentException Si aucun importer n'est trouvé
     */
    public function importByType(string $type, array $data): void
    {
        $this->logger->info(sprintf('Starting import for type: %s', $type));

        $importer = $this->importerRegistry->findImporter($type);

        if (!$importer) {
            $this->logger->error(sprintf('No importer found for type: %s', $type));

            throw new \InvalidArgumentException(
                sprintf(
                    'No importer found for type: %s. Available types: %s',
                    $type,
                    implode(', ', $this->importerRegistry->getAvailableTypes())
                )
            );
            return;
        }

        try {
            $importer->import($data);
            $this->logger->info(sprintf('Successfully imported %s', $type));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to import %s: %s', $type, $e->getMessage()));
            throw $e;
        }
    }
}
