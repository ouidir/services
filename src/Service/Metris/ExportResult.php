<?php

namespace App\Service\Metris;

class ExportResult
{
    public const STATUS_SUCCESS = 0;
    public const STATUS_FILE_ERROR = 1;
    public const STATUS_NO_DATA = 2;

    public function __construct(
        public readonly string $file,
        public readonly int $status,
        public readonly string $message,
    ) {
    }

    public static function success(string $file): self
    {
        return new self(
            file: $file,
            status: self::STATUS_SUCCESS,
            message: "Création et transmission du fichier {$file} a METRIS OK"
        );
    }

    public static function fileError(string $file): self
    {
        return new self(
            file: $file,
            status: self::STATUS_FILE_ERROR,
            message: "ERREUR - Ouverture du fichier de sortie '{$file}' impossible"
        );
    }

    public static function noData(string $file): self
    {
        return new self(
            file: $file,
            status: self::STATUS_NO_DATA,
            message: "WARNING - Aucun element retourné par la requete d'extraction"
        );
    }

    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'statut' => $this->status,
            'message' => $this->message,
        ];
    }
}
