<?php

namespace LimeSurvey\Api\Command\Export\ExportTypes;

use LimeSurvey\Api\Command\Export\ExportInterface;

class ExportPDF implements ExportInterface
{
    public function export(array $data, string $filename, bool $download = true)
    {
        $pdf = new TCPDF();
        $pdf->AddPage();

        $html = '<h1>Exported Data</h1><table border="1" cellpadding="4">';
        if (!empty($data)) {
            $html .= '<tr>';
            foreach (array_keys(reset($data)) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr>';

            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</table>';

        $pdf->writeHTML($html);

        if ($download) {
            $pdf->Output($filename . '.pdf', 'D');
            exit;
        }

        return $pdf->Output($filename . '.pdf', 'S'); // return as string
    }
}
