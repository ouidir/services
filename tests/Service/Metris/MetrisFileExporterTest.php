<?php

namespace App\Tests\Service\Metris;

use App\Service\Metris\ExportResult;
use App\Service\Metris\MetrisFileExporter;
use PHPUnit\Framework\TestCase;

class MetrisFileExporterTest extends TestCase
{
    private MetrisFileExporter $exporter;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->exporter = new MetrisFileExporter();
        $this->tmpDir = sys_get_temp_dir() . '/metris_exporter_test_' . uniqid();
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
    }

    public function testExportWithNoStatisticsReturnsNoDataResults(): void
    {
        $fileName = $this->tmpDir . '/test_empty.csv';
        $files = [
            'PREFIX' => ['name' => $fileName, 'data' => ''],
        ];

        $result = $this->exporter->export([], $files, ';');

        $this->assertCount(1, $result);
        $this->assertSame(ExportResult::STATUS_NO_DATA, $result[0]['statut']);
        $this->assertSame('test_empty.csv', $result[0]['file']);
    }

    public function testExportWithDataWritesCorrectContentAndReturnsSuccess(): void
    {
        $fileName = $this->tmpDir . '/test_data.csv';
        $files = [
            'PREFIX' => ['name' => $fileName, 'data' => ''],
        ];

        $rawStatistics = [
            [
                'metris_file' => 'PREFIX',
                'date' => '2024-01-15',
                'appli' => 'APP_TEST',
                'scenario' => 'SCENARIO_1',
                'brut' => '99.5',
            ],
        ];

        $result = $this->exporter->export($rawStatistics, $files, ';');

        $this->assertCount(1, $result);
        $this->assertSame(ExportResult::STATUS_SUCCESS, $result[0]['statut']);
        $this->assertSame('test_data.csv', $result[0]['file']);
        $this->assertFileExists($fileName);

        $content = file_get_contents($fileName);
        $this->assertStringContainsString('2024-01-15', $content);
        $this->assertStringContainsString('APP_TEST', $content);
        $this->assertStringContainsString('SCENARIO_1', $content);
        $this->assertStringContainsString('99.5', $content);
    }

    public function testExportAppendDataToMultipleFilesBasedOnMetrisFile(): void
    {
        $fileA = $this->tmpDir . '/file_a.csv';
        $fileB = $this->tmpDir . '/file_b.csv';
        $files = [
            'PREFIX_A' => ['name' => $fileA, 'data' => ''],
            'PREFIX_B' => ['name' => $fileB, 'data' => ''],
        ];

        $rawStatistics = [
            [
                'metris_file' => 'PREFIX_A',
                'date' => '2024-01-15',
                'appli' => 'APP1',
                'scenario' => 'SC1',
                'brut' => '100',
            ],
            [
                'metris_file' => 'PREFIX_B',
                'date' => '2024-01-15',
                'appli' => 'APP2',
                'scenario' => 'SC2',
                'brut' => '95',
            ],
        ];

        $result = $this->exporter->export($rawStatistics, $files, ';');

        $this->assertCount(2, $result);

        foreach ($result as $res) {
            $this->assertSame(ExportResult::STATUS_SUCCESS, $res['statut']);
        }

        $contentA = file_get_contents($fileA);
        $this->assertStringContainsString('APP1', $contentA);

        $contentB = file_get_contents($fileB);
        $this->assertStringContainsString('APP2', $contentB);
    }

    public function testExportWithMultipleMetrisFilesInSingleRow(): void
    {
        $fileA = $this->tmpDir . '/multi_a.csv';
        $fileB = $this->tmpDir . '/multi_b.csv';
        $files = [
            'MULTI_A' => ['name' => $fileA, 'data' => ''],
            'MULTI_B' => ['name' => $fileB, 'data' => ''],
        ];

        $rawStatistics = [
            [
                'metris_file' => 'MULTI_A;MULTI_B',
                'date' => '2024-01-15',
                'appli' => 'APP_MULTI',
                'scenario' => 'SC_MULTI',
                'brut' => '88',
            ],
        ];

        $result = $this->exporter->export($rawStatistics, $files, ';');

        $this->assertCount(2, $result);

        $contentA = file_get_contents($fileA);
        $contentB = file_get_contents($fileB);

        $this->assertStringContainsString('APP_MULTI', $contentA);
        $this->assertStringContainsString('APP_MULTI', $contentB);
    }

    public function testExportUsesProvidedDelimiter(): void
    {
        $fileName = $this->tmpDir . '/delimiter_test.csv';
        $files = [
            'PREFIX' => ['name' => $fileName, 'data' => ''],
        ];

        $rawStatistics = [
            [
                'metris_file' => 'PREFIX',
                'date' => '2024-01-15',
                'appli' => 'APP',
                'scenario' => 'SC',
                'brut' => '100',
            ],
        ];

        $this->exporter->export($rawStatistics, $files, '|');

        $content = file_get_contents($fileName);
        $this->assertStringContainsString('|', $content);
    }
}
