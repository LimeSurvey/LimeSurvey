<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class XlsxExportWriter implements ExportWriterInterface
{
    /**
     * Export survey responses to XLSX format.
     *
     * Can generate content in-memory or write to a file depending on outputMode.
     *
     * @param array $responses The survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @param array $metadata Additional metadata (survey ID, language, outputMode, etc.)
     * @return array Export result with content/filePath and metadata
     * @throws RuntimeException If content cannot be generated or file cannot be created
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function export(array $responses, array $surveyQuestions, array $metadata): array
    {
        $surveyId = $metadata['surveyId'];
        $timestamp = date('YmdHis');
        $filename = "survey_{$surveyId}_responses_{$timestamp}.xlsx";
        $outputMode = $metadata['outputMode'] ?? 'memory';

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

        // Create XLSX file/content based on output mode
        if ($outputMode === 'file') {
            $tempDir = sys_get_temp_dir();
            $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            $this->createXlsxFile($filePath, $data);
            return [
                'content' => null,
                'filePath' => $filePath,
                'filename' => $filename,
                'mimeType' => $this->getMimeType(),
                'extension' => $this->getFileExtension(),
                'size' => filesize($filePath),
                'responseCount' => count($responses)
            ];
        } else {
            $content = $this->createXlsxContent($data);
            return [
                'content' => $content,
                'filePath' => null,
                'filename' => $filename,
                'mimeType' => $this->getMimeType(),
                'extension' => $this->getFileExtension(),
                'size' => strlen($content),
                'responseCount' => count($responses)
            ];
        }
    }

    /**
     * Create XLSX file at specified path from data array.
     *
     * @param string $filePath
     * @param array $data
     * @throws RuntimeException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function createXlsxFile(string $filePath, array $data): void
    {
        // Create a simple XLSX structure using ZIP
        $zip = new \ZipArchive();
        if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create XLSX file: $filePath");
        }

        $this->addXlsxFilesToZip($zip, $data);
        $zip->close();
    }

    /**
     * Create XLSX content from data array.
     *
     * @param array $data
     * @return string The XLSX file content
     * @throws RuntimeException
     */
    private function createXlsxContent(array $data): string
    {
        // Create a temporary file for the ZIP
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        // Create a simple XLSX structure using ZIP
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Unable to create XLSX content");
        }

        $this->addXlsxFilesToZip($zip, $data);
        $zip->close();

        // Read the content and clean up
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        if ($content === false) {
            throw new RuntimeException("Unable to read XLSX content");
        }

        return $content;
    }

    /**
     * Add all necessary files to XLSX ZIP archive.
     *
     * @param \ZipArchive $zip
     * @param array $data
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addXlsxFilesToZip(\ZipArchive $zip, array $data): void
    {
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
