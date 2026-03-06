<?php

namespace App\Service;

use App\Entity\Scenario;
use App\Service\Har\HarProcessingException;
use App\Service\Har\HarEntryDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use SplFileObject;
use JsonException;

class HarService
{
    private const REQUIRED_PATHS = [
        ['snapshot', 'time'],
        ['snapshot', 'request', 'method'],
        ['snapshot', 'request', 'url'],
        ['snapshot', 'response', 'status'],
        ['snapshot', 'response', 'statusText'],
        ['snapshot', 'response', 'headers'],
        ['snapshot', 'response', '_transferSize'],
        ['snapshot', 'response', 'content', 'mimeType'],
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%app.path.har%')]
        private readonly string $harDirectory,
    ) {
    }

    /**
     * @return HarEntryDto[]
     *
     * @throws HarProcessingException
     */
    public function getHarData(Scenario $scenario, string $date, string $heure): array
    {
        $filePath = $this->buildFilePath($scenario, $date, $heure);
        $this->logger->info('Processing HAR file', [
            'scenario' => $scenario->getNom(),
            'date' => $date,
            'heure' => $heure,
            'path' => $filePath,
        ]);

        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new HarProcessingException(sprintf('HAR file not found or unreadable: %s', $filePath));
        }

        $entries = [];
        $lineNumber = 0;
        $file = new SplFileObject($filePath, 'r');

        while (!$file->eof()) {
            $line = trim($file->fgets());
            if ($line === '') {
                continue;
            }
            ++$lineNumber;

            try {
                $event = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $this->logAndThrow(
                    sprintf('Invalid JSON at line %d: %s', $lineNumber, $e->getMessage()),
                    $scenario,
                    $date,
                    $heure
                );
            }

            $this->assertPathExists($event, self::REQUIRED_PATHS, $lineNumber, $scenario, $date, $heure);

            $entries[] = $this->buildHarEntry($event);
        }

        return $entries;
    }

    private function buildFilePath(Scenario $scenario, string $date, string $heure): string
    {
        return Path::join(
            $this->projectDir,
            'public',
            $this->harDirectory,
            $scenario->getApplication()->getNom(),
            $scenario->getNom(),
            $date,
            $heure,
            'trace.network' // le suffixe réel du fichier HAR
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return HarEntryDto
     */
    private function buildHarEntry(array $data): HarEntryDto
    {
        $url = $data['snapshot']['request']['url'];
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        return new HarEntryDto(
            time: $data['snapshot']['time'],
            method: $data['snapshot']['request']['method'],
            url: $url,
            path: $path,
            status: $data['snapshot']['response']['status'],
            statusText: $data['snapshot']['response']['statusText'],
            headers: $data['snapshot']['response']['headers'],
            size: $data['snapshot']['response']['_transferSize'],
            mimeType: $data['snapshot']['response']['content']['mimeType'],
        );
    }

    /**
     * @param array<string, mixed> $array
     * @param list<list<string>>   $paths
     *
     * @throws HarProcessingException
     */
    private function assertPathExists(
        array $array,
        array $paths,
        int $lineNumber,
        Scenario $scenario,
        string $date,
        string $heure
    ): void {
        foreach ($paths as $path) {
            $missing = $this->findMissingKey($array, $path);
            if (null !== $missing) {
                $this->logAndThrow(
                    sprintf(
                        'Missing key "%s" at line %d (path: %s)',
                        $missing,
                        $lineNumber,
                        implode(' → ', $path)
                    ),
                    $scenario,
                    $date,
                    $heure
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $array
     * @param list<string>         $path
     *
     * @return string|null missing key or null if the whole path exists
     */
    private function findMissingKey(array $array, array $path): ?string
    {
        foreach ($path as $key) {
            if (!array_key_exists($key, $array)) {
                return $key;
            }
            $array = $array[$key];
        }

        return null;
    }

    /**
     * @throws HarProcessingException
     */
    private function logAndThrow(string $message, Scenario $scenario, string $date, string $heure): void
    {
        $this->logger->error($message, [
            'scenario' => $scenario->getNom(),
            'date' => $date,
            'heure' => $heure,
        ]);

        throw new HarProcessingException($message);
    }
}
