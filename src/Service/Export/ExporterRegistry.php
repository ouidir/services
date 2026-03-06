<?php

namespace App\Service\Export;

use App\Handler\DataHandler\ExporterInterface;
use Psr\Log\LoggerInterface;

/**
 * Registry pattern pour gérer les exporters
 * Respecte SRP et OCP (ouvert à l'extension, fermé à la modification)
 */
class ExporterRegistry
{
    /** @var ExporterInterface[] */
    private array $exporters = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        iterable $exporters
    ) {
        foreach ($exporters as $exporter) {
            $this->register($exporter);
        }

        $this->logger->info('ExporterRegistry initialized', [
            'exporters_count' => count($this->exporters)
        ]);
    }

    /**
     * Enregistre un nouvel exporter
     */
    public function register(ExporterInterface $exporter): void
    {
        $this->exporters[] = $exporter;
    }

    /**
     * Trouve un exporter compatible avec le type donné
     */
    public function findExporter(string $type): ?ExporterInterface
    {
        foreach ($this->exporters as $exporter) {
            if ($exporter->supports($type)) {
                $this->logger->debug(sprintf('Found exporter for type: %s', $type), [
                    'exporter' => get_class($exporter)
                ]);
                return $exporter;
            }
        }

        $this->logger->warning(sprintf('No exporter found for type: %s', $type), [
            'available_types' => $this->getAvailableTypes()
        ]);

        return null;
    }

    /**
     * Retourne tous les exporters enregistrés
     *
     * @return ExporterInterface[]
     */
    public function getAll(): array
    {
        return $this->exporters;
    }

    /**
     * Retourne les types d'exporters disponibles
     */
    public function getAvailableTypes(): array
    {
        return array_map(
            fn(ExporterInterface $exporter) => get_class($exporter),
            $this->exporters
        );
    }
}
