<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class HtmlExportWriter implements ExportWriterInterface
{
    /**
     * Export survey responses to HTML format.
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
        $filename = "survey_{$surveyId}_responses_{$timestamp}.html";
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        $html = '<!DOCTYPE html>';
        $html .= '<html lang="en">';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>Survey ' . htmlspecialchars($surveyId) . ' Responses Export</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= 'h1 { color: #333; }';
        $html .= 'table { border-collapse: collapse; width: 100%; margin-top: 20px; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #4CAF50; color: white; font-weight: bold; }';
        $html .= 'tr:nth-child(even) { background-color: #f2f2f2; }';
        $html .= 'tr:hover { background-color: #ddd; }';
        $html .= '.meta { color: #666; font-size: 14px; margin-bottom: 10px; }';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';

        $html .= '<h1>Survey ' . htmlspecialchars($surveyId) . ' - Response Export</h1>';
        $html .= '<div class="meta">Export Date: ' . date('Y-m-d H:i:s') . '</div>';
        $html .= '<div class="meta">Total Responses: ' . count($responses) . '</div>';

        $html .= '<table>';

        // Build header row
        $html .= '<thead><tr>';
        $html .= '<th>Response ID</th>';
        $html .= '<th>Submit Date</th>';
        $html .= '<th>Last Page</th>';
        $html .= '<th>Start Language</th>';
        $html .= '<th>Seed</th>';

        foreach ($surveyQuestions as $fieldCode => $question) {
            $qid = $question['qid'];
            $html .= '<th>Q' . htmlspecialchars($qid) . '</th>';
        }
        $html .= '</tr></thead>';

        // Build response rows
        $html .= '<tbody>';
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
        $html .= '</tbody>';

        $html .= '</table>';
        $html .= '</body>';
        $html .= '</html>';

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
     * Get the file extension for HTML format.
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'html';
    }

    /**
     * Get the MIME type for HTML format.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'text/html';
    }
}
