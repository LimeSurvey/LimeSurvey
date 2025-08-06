<?php

namespace LimeSurvey\Api\Command\Export\ExportTypes;

use LimeSurvey\Api\Command\Export\ExportInterface;
use Spreadsheet_Excel_Writer;

class ExportXLS implements ExportInterface
{
    public function export(array $data, string $filename, bool $download = true)
    {
        $workbook = new Spreadsheet_Excel_Writer();

        if (!$download) {
            $tempFile = tempnam(sys_get_temp_dir(), 'xls');
            $workbook->send($tempFile);
        } else {
            $workbook->send($filename . '.xls');
        }

        $worksheet = $workbook->addWorksheet('Sheet1');

        $rowIndex = 0;

        if (!empty($data)) {
            // Write header
            $headers = array_keys(reset($data));
            foreach ($headers as $colIndex => $header) {
                $worksheet->write($rowIndex, $colIndex, $header);
            }

            // Write rows
            foreach ($data as $row) {
                $rowIndex++;
                foreach ($row as $colIndex => $cell) {
                    $worksheet->write($rowIndex, $colIndex, $cell);
                }
            }
        }

        ob_start();
        $workbook->close();
        $xlsContent = ob_get_clean();

        if (!$download) {
            return $xlsContent;
        }

        exit;
    }
}
