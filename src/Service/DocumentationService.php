<?php

namespace App\Service;

use App\Entity\Documentation;
use App\Repository\DocumentationRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Filesystem\Path;

class DocumentationService
{
    // Taille maximale de fichier en octets (20 Mo)
    private const MAX_FILE_SIZE = 20 * 1024 * 1024;

    // Extensions autorisées
    private const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
        'zip', 'rar', '7z', 'jpg', 'jpeg', 'png', 'gif'
    ];

    public function __construct(
        private readonly DocumentationRepository $documentationRepository,
        private readonly LoggerInterface $logger,
        #[Autowire('%app.path.documentation%')]
        private readonly string $uploadDirectory,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {
    }

    /**
     * Télécharge un document
     */
    public function download(Documentation $documentation): BinaryFileResponse
    {
        $filePath = $this->getFullPath($documentation);

        if (!file_exists($filePath)) {
            throw new \RuntimeException('Le fichier n\'existe pas sur le serveur');
        }

        // Vérifier le MIME autorisé
        $mime = mime_content_type($filePath);
        if (!in_array($mime, $this->getAllowedMimeTypes(), true)) {
            throw new \RuntimeException('Type de fichier non autorisé');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $documentation->getTitre() . '.' . $this->getFileExtension($documentation)
        );

        $this->logger->info('Document téléchargé', [
            'documentation_id' => $documentation->getId(),
            'titre' => $documentation->getTitre(),
        ]);

        return $response;
    }

    /**
     * Récupère les informations d'un fichier
     */
    public function getFileInfo(Documentation $documentation): array
    {
        $path = $documentation->getPath();

        if (!$path) {
            return [
                'exists' => false,
                'name' => null,
                'size' => 0,
                'size_formatted' => 'N/A',
                'extension' => null,
                'mime_type' => null,
                'uploaded_at' => null,
            ];
        }

        $filePath = $this->getFullPath($documentation);
        $exists = file_exists($filePath);
        $size = $exists ? filesize($filePath) : 0;

        return [
            'exists' => $exists,
            'name' => $path,
            'size' => $size,
            'size_formatted' => $exists ? $this->formatFileSize($size) : 'N/A',
            'extension' => $this->getFileExtension($documentation),
            'mime_type' => $exists ? mime_content_type($filePath) : null,
            'uploaded_at' => $exists ? date('d/m/Y H:i', filemtime($filePath)) : null,
        ];
    }

    /**
     * Formate une taille de fichier
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, 2, ',', ' ') . ' ' . $units[$i];
    }

    /**
     * Récupère l'extension d'un fichier
     */
    public function getFileExtension(Documentation $documentation): ?string
    {
        $path = $documentation->getPath();

        if (!$path) {
            return null;
        }

        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Récupère le chemin complet d'un fichier
     */
    private function getFullPath(Documentation $documentation): string
    {
        return Path::join($this->projectDir, 'public', $this->uploadDirectory, $documentation->getPath());
    }

    /**
     * Supprime le fichier physique
     */
    public function deletePhysicalFile(Documentation $documentation): void
    {
        $filePath = $this->getFullPath($documentation);

        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                throw new \RuntimeException('Impossible de supprimer le fichier physique');
            }

            $this->logger->info('Fichier physique supprimé', [
                'documentation_id' => $documentation->getId(),
                'path' => $filePath,
            ]);
        }
    }

    /**
     * Vérifie l'intégrité des fichiers
     */
    public function checkIntegrity(): array
    {
        $documentations = $this->documentationRepository->findAll();
        $issues = [];

        foreach ($documentations as $doc) {
            if (!$doc->getPath()) {
                $issues[] = [
                    'id' => $doc->getId(),
                    'titre' => $doc->getTitre(),
                    'issue' => 'Aucun fichier associé',
                ];
                continue;
            }

            $filePath = $this->getFullPath($doc);

            if (!file_exists($filePath)) {
                $issues[] = [
                    'id' => $doc->getId(),
                    'titre' => $doc->getTitre(),
                    'issue' => 'Fichier manquant sur le serveur',
                    'path' => $filePath,
                ];
            }
        }

        return $issues;
    }

    /**
     * Récupère les documentations par extension
     */
    public function getByExtension(string $extension): array
    {
        return array_filter(
            $this->documentationRepository->findAll(),
            fn(Documentation $doc) => strtolower($this->getFileExtension($doc) ??  '') === strtolower($extension)
        );
    }

    /**
     * Vérifie si une extension est autorisée
     */
    public function isExtensionAllowed(string $extension): bool
    {
        return in_array(strtolower($extension), self::ALLOWED_EXTENSIONS, true);
    }

    /**
     * Récupère les extensions autorisées
     */
    public function getAllowedExtensions(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }

    /**
     * Récupère la taille maximale de fichier
     */
    public function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Récupère la taille maximale de fichier formatée
     */
    public function getMaxFileSizeFormatted(): string
    {
        return $this->formatFileSize(self::MAX_FILE_SIZE);
    }

    private function getAllowedMimeTypes(): array
    {
        return [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }
}
