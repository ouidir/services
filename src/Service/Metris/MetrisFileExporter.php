<?php

namespace App\Service\Metris;

class MetrisFileExporter
{
    public function export(array $rawStatistics, array $files, string $delimiteur): array
    {
        foreach ($rawStatistics as $line) {
            foreach (explode(';', $line['metris_file']) as $file) {
                $files[$file]['data'] .= $line['date']
                . $delimiteur
                . $line['appli']
                . $delimiteur
                . $line['scenario']
                . $delimiteur
                . $line['brut']
                . $delimiteur
                . "\r\n";
            }
        }

        return $this->writeFile($files, $rawStatistics);
    }

    private function writeFile(array $files, array $rawStatistics): array
    {
        $reponses = [];

        foreach ($files as $file) {
            $info = new \SplFileInfo($file['name']);

            if (count($rawStatistics) == 0) {
                $reponse = ExportResult::noData($info->getFilename());
            } elseif (!($fp = fopen($file['name'], "w"))) {
                $reponse = ExportResult::fileError($file['name']);
            } else {
                $reponse = ExportResult::success($info->getFilename());
            }

            fputs($fp, $file['data']);
            fclose($fp);
            $reponses[] = $reponse->toArray();
        }

        return $reponses;
    }
}
