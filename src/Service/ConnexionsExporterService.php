<?php

namespace App\Service;

use App\Repository\LoginHistoryRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConnexionsExporterService
{
    public function __construct(
        private LoginHistoryRepository $loginHistoryRepository,
        private LoggerInterface $logger
    ) {
    }

    public function export(array $connectionIds): StreamedResponse
    {
        $this->logger->info(sprintf('Exporting %d user connections', count($connectionIds)));

        $response = new StreamedResponse(function () use ($connectionIds): void {
            try {
                $handle = fopen('php://output', 'w');

                // Write CSV header
                fputcsv($handle, ['Utilisateurs', 'Date de connexion', 'Navigateur'], ',', '"', '\\');

                foreach ($connectionIds as $connectionData) {
                    $connectionId = is_array($connectionData) ? $connectionData['id'] : $connectionData;

                    $connection = $this->loginHistoryRepository->find($connectionId);

                    if (!$connection) {
                        $this->logger->warning(sprintf('Connection not found:  %s', $connectionId));
                        continue;
                    }

                    $user = $connection->getUser();
                    $userName = $user ?  sprintf('%s %s', $user->getNom(), $user->getPrenom()) : 'Unknown';
                    $row = [
                        $userName,
                        $connection->getLoggedAt()?->format('Y-m-d H:i:s') ?? '',
                        $connection->getUserAgent() ?? ''
                    ];

                    fputcsv($handle, $row, ',', '"', '\\');

                }

                fclose($handle);

                $this->logger->info('User connections export completed');
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Export failed: %s', $e->getMessage()));
                if (isset($handle) && is_resource($handle)) {
                    fclose($handle);
                }
            }
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="Connexions. csv"');

        return $response;
    }
}
