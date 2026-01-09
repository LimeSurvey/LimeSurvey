<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class XlsExportWriter implements ExportWriterInterface
{
    /**
     * Export survey responses to XLS format (Excel 97-2003).
     *
     * @param array $responses The survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @param array $metadata Additional metadata (survey ID, language, etc.)
     * @return array Export result with file path and metadata
     * @throws RuntimeException If file cannot be created
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function export(array $responses, array $surveyQuestions, array $metadata): array
    {
        $surveyId = $metadata['surveyId'];
        $timestamp = date('YmdHis');

        $tempDir = sys_get_temp_dir();
        $filename = "survey_{$surveyId}_responses_{$timestamp}.xls";
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        // Build HTML table that Excel can read as XLS
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
        $html .= '<body><table border="1">';

        // Build header row
        $html .= '<tr style="font-weight:bold;">';
        $html .= '<td>Response ID</td>';
        $html .= '<td>Submit Date</td>';
        $html .= '<td>Last Page</td>';
        $html .= '<td>Start Language</td>';
        $html .= '<td>Seed</td>';

        foreach ($surveyQuestions as $fieldCode => $question) {
            $qid = $question['qid'];
            $html .= '<td>Q' . htmlspecialchars($qid) . '</td>';
        }
        $html .= '</tr>';

        // Build response rows
        foreach ($responses as $response) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($response['id'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['submitdate'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['lastpage'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['startlanguage'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($response['seed'] ?? '') . '</td>';

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

                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        if (file_put_contents($filePath, $html) === false) {
            throw new RuntimeException("Unable to create export file: $filePath");
        }

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
     * Get the file extension for XLS format.
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'xls';
    }

    /**
     * Get the MIME type for XLS format.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'application/vnd.ms-excel';
    }
}
