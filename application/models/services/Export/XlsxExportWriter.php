<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class XlsxExportWriter implements ExportWriterInterface
{
    /**
     * Export survey responses to XLSX format.
     *
     * @param array $responses The survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @param array $metadata Additional metadata (survey ID, language, etc.)
     * @return array Export result with file path and metadata
     * @throws RuntimeException If file cannot be created
     */
    public function export(array $responses, array $surveyQuestions, array $metadata): array
    {
        $surveyId = $metadata['surveyId'];
        $timestamp = date('YmdHis');

        $tempDir = sys_get_temp_dir();
        $filename = "survey_{$surveyId}_responses_{$timestamp}.xlsx";
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        // Build spreadsheet data
        $data = [];

        // Build header row
        $headers = ['Response ID', 'Submit Date', 'Last Page', 'Start Language', 'Seed'];
        foreach ($surveyQuestions as $fieldCode => $question) {
            $qid = $question['qid'];
            $headers[] = "Q{$qid}";
        }
        $data[] = $headers;

        // Build response rows
        foreach ($responses as $response) {
            $row = [
                $response['id'] ?? '',
                $response['submitdate'] ?? '',
                $response['lastpage'] ?? '',
                $response['startlanguage'] ?? '',
                $response['seed'] ?? ''
            ];

            // Add answer values
            foreach ($surveyQuestions as $fieldCode => $question) {
                $qid = $question['qid'];
                $value = '';

                if (isset($response['answers'])) {
                    foreach ($response['answers'] as $answer) {
                        if (isset($answer['qid']) && $answer['qid'] == $qid) {
                            $value = $answer['value'] ?? '';
                            break;
                        }
                    }
                }

                $row[] = $value;
            }

            $data[] = $row;
        }

        // Create XLSX file using a simple XML-based approach
        $this->createXlsxFile($filePath, $data);

        return [
            'filePath' => $filePath,
            'filename' => $filename,
            'mimeType' => $this->getMimeType(),
            'extension' => $this->getFileExtension(),
            'size' => filesize($filePath),
            'responseCount' => count($responses)
        ];
    }

    /**
     * Create XLSX file from data array.
     *
     * @param string $filePath
     * @param array $data
     * @throws RuntimeException
     */
    private function createXlsxFile(string $filePath, array $data): void
    {
        // Create a simple XLSX structure using ZIP
        $zip = new \ZipArchive();
        if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create XLSX file: $filePath");
        }

        // Add [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $contentTypes .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $contentTypes .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $contentTypes .= '<Default Extension="xml" ContentType="application/xml"/>';
        $contentTypes .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $contentTypes .= '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $contentTypes .= '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // Add _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $rels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $rels .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
        $rels .= '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // Add xl/_rels/workbook.xml.rels
        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $workbookRels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $workbookRels .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>';
        $workbookRels .= '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);

        // Add xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $workbook .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $workbook .= '<sheets>';
        $workbook .= '<sheet name="Survey Responses" sheetId="1" r:id="rId1"/>';
        $workbook .= '</sheets>';
        $workbook .= '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // Add xl/worksheets/sheet1.xml with data
        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $worksheet .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $worksheet .= '<sheetData>';

        $rowNum = 1;
        foreach ($data as $rowData) {
            $worksheet .= '<row r="' . $rowNum . '">';
            $colNum = 0;
            foreach ($rowData as $cellValue) {
                $colLetter = $this->getColumnLetter($colNum);
                $cellRef = $colLetter . $rowNum;

                // Escape XML special characters
                $escapedValue = htmlspecialchars($cellValue, ENT_XML1 | ENT_QUOTES, 'UTF-8');

                $worksheet .= '<c r="' . $cellRef . '" t="inlineStr">';
                $worksheet .= '<is><t>' . $escapedValue . '</t></is>';
                $worksheet .= '</c>';
                $colNum++;
            }
            $worksheet .= '</row>';
            $rowNum++;
        }

        $worksheet .= '</sheetData>';
        $worksheet .= '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);

        $zip->close();
    }

    /**
     * Convert column index to Excel column letter (0 = A, 1 = B, etc.).
     *
     * @param int $colNum
     * @return string
     */
    private function getColumnLetter(int $colNum): string
    {
        $letter = '';
        while ($colNum >= 0) {
            $letter = chr($colNum % 26 + 65) . $letter;
            $colNum = intval($colNum / 26) - 1;
        }
        return $letter;
    }

    /**
     * Get the file extension for XLSX format.
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'xlsx';
    }

    /**
     * Get the MIME type for XLSX format.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }
}
