<?php

namespace App\Service\Metris;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MetrisConfiguration
{
    public const DELIMITEUR = ";";

    public function __construct(
        #[Autowire('%metrisCSVPath%')]
        private readonly string $csvPath,
        #[Autowire('%metris_files%')]
        private readonly string $filesPrefixes,
    ) {
    }

    public function getCsvPath(): string
    {
        return $this->csvPath;
    }

    public function getFilesPrefixes(): array
    {
        return \explode(',', $this->filesPrefixes);
    }

    public function getDelimiter(): string
    {
        return self::DELIMITEUR;
    }

    public function getFilePattern(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, string $prefix): string
    {
        $datePattern = ($dateDebut->format('Ymd') === $dateFin->format('Ymd'))
            ? $dateDebut->format('Ymd')
            : $dateDebut->format('Ymd') . '-' . $dateFin->format('Ymd');

        return sprintf('%s_%s.csv', $prefix, $datePattern);
    }

    public function getFullFilePath(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, string $prefix): string
    {
        return $this->csvPath . '/' . $this->getFilePattern($dateDebut, $dateFin, $prefix);
    }


    public function initializeFiles(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): array
    {
        $files = [];

        foreach ($this->getFilesPrefixes() as $prefix) {
            if (!file_exists($this->getCsvPath())) {
                mkdir($this->getCsvPath(), 0755, true);
            }

            $fileCsv = $this->getFullFilePath($dateDebut, $dateFin, $prefix);

            $files[$prefix] = ['name' => $fileCsv, 'data' => ''];
        }

        return $files;
    }
}
