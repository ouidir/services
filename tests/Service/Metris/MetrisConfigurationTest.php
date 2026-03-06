<?php

namespace App\Tests\Service\Metris;

use App\Service\Metris\MetrisConfiguration;
use PHPUnit\Framework\TestCase;

class MetrisConfigurationTest extends TestCase
{
    private MetrisConfiguration $configuration;
    private string $csvPath;
    private string $filesPrefixes;

    protected function setUp(): void
    {
        $this->csvPath = '/tmp/metris/csv';
        $this->filesPrefixes = 'PREFIX_A,PREFIX_B,PREFIX_C';
        $this->configuration = new MetrisConfiguration($this->csvPath, $this->filesPrefixes);
    }

    public function testGetCsvPathReturnsConfiguredPath(): void
    {
        $this->assertSame($this->csvPath, $this->configuration->getCsvPath());
    }

    public function testGetFilesPrefixesReturnsArrayOfPrefixes(): void
    {
        $prefixes = $this->configuration->getFilesPrefixes();

        $this->assertIsArray($prefixes);
        $this->assertCount(3, $prefixes);
        $this->assertSame('PREFIX_A', $prefixes[0]);
        $this->assertSame('PREFIX_B', $prefixes[1]);
        $this->assertSame('PREFIX_C', $prefixes[2]);
    }

    public function testGetFilesPrefixesSinglePrefix(): void
    {
        $config = new MetrisConfiguration('/path', 'ONLY_ONE');
        $prefixes = $config->getFilesPrefixes();

        $this->assertCount(1, $prefixes);
        $this->assertSame('ONLY_ONE', $prefixes[0]);
    }

    public function testGetDelimiterReturnsSemicolon(): void
    {
        $this->assertSame(';', $this->configuration->getDelimiter());
        $this->assertSame(MetrisConfiguration::DELIMITEUR, $this->configuration->getDelimiter());
    }

    public function testGetFilePatternSameDayUsesSingleDate(): void
    {
        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-15');

        $pattern = $this->configuration->getFilePattern($dateDebut, $dateFin, 'EXPORT');

        $this->assertSame('EXPORT_20240115.csv', $pattern);
    }

    public function testGetFilePatternDifferentDaysUsesDateRange(): void
    {
        $dateDebut = new \DateTime('2024-01-01');
        $dateFin = new \DateTime('2024-01-31');

        $pattern = $this->configuration->getFilePattern($dateDebut, $dateFin, 'STATS');

        $this->assertSame('STATS_20240101-20240131.csv', $pattern);
    }

    public function testGetFullFilePathCombinesPathAndPattern(): void
    {
        $dateDebut = new \DateTime('2024-06-01');
        $dateFin = new \DateTime('2024-06-01');

        $fullPath = $this->configuration->getFullFilePath($dateDebut, $dateFin, 'DATA');

        $this->assertSame('/tmp/metris/csv/DATA_20240601.csv', $fullPath);
    }

    public function testGetFullFilePathWithDifferentDays(): void
    {
        $dateDebut = new \DateTime('2024-03-01');
        $dateFin = new \DateTime('2024-03-31');

        $fullPath = $this->configuration->getFullFilePath($dateDebut, $dateFin, 'REPORT');

        $this->assertSame('/tmp/metris/csv/REPORT_20240301-20240331.csv', $fullPath);
    }

    public function testInitializeFilesCreatesEntriesForEachPrefix(): void
    {
        $tmpDir = sys_get_temp_dir() . '/metris_test_' . uniqid();
        mkdir($tmpDir, 0755, true);

        $config = new MetrisConfiguration($tmpDir, 'PREF1,PREF2');
        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-15');

        $files = $config->initializeFiles($dateDebut, $dateFin);

        $this->assertArrayHasKey('PREF1', $files);
        $this->assertArrayHasKey('PREF2', $files);
        $this->assertArrayHasKey('name', $files['PREF1']);
        $this->assertArrayHasKey('data', $files['PREF1']);
        $this->assertSame('', $files['PREF1']['data']);
        $this->assertStringEndsWith('PREF1_20240115.csv', $files['PREF1']['name']);

        rmdir($tmpDir);
    }

    public function testInitializeFilesCreatesDirectoryIfNotExists(): void
    {
        $tmpDir = sys_get_temp_dir() . '/metris_new_dir_' . uniqid();

        $config = new MetrisConfiguration($tmpDir, 'TEST');
        $dateDebut = new \DateTime('2024-01-15');
        $dateFin = new \DateTime('2024-01-15');

        $files = $config->initializeFiles($dateDebut, $dateFin);

        $this->assertDirectoryExists($tmpDir);
        $this->assertArrayHasKey('TEST', $files);

        rmdir($tmpDir);
    }

    public function testDelimiteurConstant(): void
    {
        $this->assertSame(';', MetrisConfiguration::DELIMITEUR);
    }
}
