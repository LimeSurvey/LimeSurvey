<?php

namespace LimeSurvey\Api\Command\Export\ExportTypes;

use LimeSurvey\Api\Command\Export\ExportInterface;

class ExportCSV implements ExportInterface
{
    public function export(array $data, string $filename, bool $download = true)
    {
        $output = fopen('php://temp', 'r+');

        if (!empty($data)) {
            fputcsv($output, array_keys(reset($data)));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        if ($download) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            echo $csvContent;
            exit;
        }

        return $csvContent;
    }
}
