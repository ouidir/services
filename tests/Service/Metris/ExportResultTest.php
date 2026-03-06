<?php

namespace App\Tests\Service\Metris;

use App\Service\Metris\ExportResult;
use PHPUnit\Framework\TestCase;

class ExportResultTest extends TestCase
{
    public function testSuccessCreatesResultWithCorrectStatusAndMessage(): void
    {
        $result = ExportResult::success('export_20240115.csv');

        $this->assertSame('export_20240115.csv', $result->file);
        $this->assertSame(ExportResult::STATUS_SUCCESS, $result->status);
        $this->assertSame(0, $result->status);
        $this->assertSame(
            'Création et transmission du fichier export_20240115.csv a METRIS OK',
            $result->message
        );
    }

    public function testFileErrorCreatesResultWithCorrectStatusAndMessage(): void
    {
        $result = ExportResult::fileError('/path/to/file.csv');

        $this->assertSame('/path/to/file.csv', $result->file);
        $this->assertSame(ExportResult::STATUS_FILE_ERROR, $result->status);
        $this->assertSame(1, $result->status);
        $this->assertSame(
            "ERREUR - Ouverture du fichier de sortie '/path/to/file.csv' impossible",
            $result->message
        );
    }

    public function testNoDataCreatesResultWithCorrectStatusAndMessage(): void
    {
        $result = ExportResult::noData('empty.csv');

        $this->assertSame('empty.csv', $result->file);
        $this->assertSame(ExportResult::STATUS_NO_DATA, $result->status);
        $this->assertSame(2, $result->status);
        $this->assertSame(
            "WARNING - Aucun element retourné par la requete d'extraction",
            $result->message
        );
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $result = ExportResult::success('test.csv');

        $array = $result->toArray();

        $this->assertArrayHasKey('file', $array);
        $this->assertArrayHasKey('statut', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertSame('test.csv', $array['file']);
        $this->assertSame(ExportResult::STATUS_SUCCESS, $array['statut']);
        $this->assertSame(
            'Création et transmission du fichier test.csv a METRIS OK',
            $array['message']
        );
    }

    public function testToArrayForFileError(): void
    {
        $result = ExportResult::fileError('bad.csv');

        $array = $result->toArray();

        $this->assertSame('bad.csv', $array['file']);
        $this->assertSame(1, $array['statut']);
    }

    public function testToArrayForNoData(): void
    {
        $result = ExportResult::noData('empty.csv');

        $array = $result->toArray();

        $this->assertSame('empty.csv', $array['file']);
        $this->assertSame(2, $array['statut']);
    }

    public function testStatusConstants(): void
    {
        $this->assertSame(0, ExportResult::STATUS_SUCCESS);
        $this->assertSame(1, ExportResult::STATUS_FILE_ERROR);
        $this->assertSame(2, ExportResult::STATUS_NO_DATA);
    }

    public function testConstructorDirectly(): void
    {
        $result = new ExportResult(
            file: 'custom.csv',
            status: 99,
            message: 'Custom message',
        );

        $this->assertSame('custom.csv', $result->file);
        $this->assertSame(99, $result->status);
        $this->assertSame('Custom message', $result->message);
    }
}
