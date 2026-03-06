<?php

namespace App\Service;

use App\Service\Metris\MetrisConfiguration;
use App\Service\Metris\MetrisFileExporter;
use App\Service\Metris\MetrisStatisticsProviderInterface;

class MetrisService
{
    public function __construct(
        private readonly MetrisStatisticsProviderInterface $metrisStatisticsProvider,
        private readonly MetrisFileExporter $fileExporter,
        private readonly MetrisConfiguration $configuration,
    ) {
    }

    public function statsMetris(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        // Récupération des résultats
        $rawStatistics = $this->metrisStatisticsProvider->getStatistics($dateDebut, $dateFin);

        // Création du fichier csv à transmettre à METRIS : date;appli;scenario;brut
        $files = $this->configuration->initializeFiles($dateDebut, $dateFin);

        return $this->fileExporter->export(
            $rawStatistics,
            $files,
            $this->configuration->getDelimiter()
        );
    }

    /**
     * Retourne la liste des préfixes de fichiers Metris configurés
     *
     * @return string[]
     */
    public function getBrutFiles(): array
    {
        return $this->configuration->getFilesPrefixes();
    }
}
