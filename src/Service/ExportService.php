<?php

namespace App\Service;

use App\Service\Export\ExporterRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Service d'export refactorisé selon les principes SOLID
 *
 * SRP : Responsabilité unique = orchestrer les exports via les exporters
 * OCP : Ouvert à l'extension (nouveaux exporters), fermé à la modification
 * LSP : Tous les exporters implémentent ExporterInterface
 * ISP : Interface ségrégée (ExporterInterface simple et ciblée)
 * DIP : Dépend de l'abstraction ExporterRegistry, pas d'implémentations concrètes
 */
class ExportService
{
    public function __construct(
        private readonly ExporterRegistry $exporterRegistry,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Exporte des données selon le type spécifié
     *
     * @throws \InvalidArgumentException Si aucun exporter n'est trouvé
     */
    public function exportByType(string $type): StreamedResponse
    {
        $this->logger->info(sprintf('Starting export for type: %s', $type));

        $exporter = $this->exporterRegistry->findExporter($type);

        if (!$exporter) {
            $this->logger->error(sprintf('No exporter found for type: %s', $type), [
                'available_types' => $this->exporterRegistry->getAvailableTypes()
            ]);

            throw new \InvalidArgumentException(
                sprintf(
                    'No exporter found for type: %s. Available types: %s',
                    $type,
                    implode(', ', $this->exporterRegistry->getAvailableTypes())
                )
            );
        }

        return $exporter->export();
    }
}
