<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class CsvExportWriter implements ExportWriterInterface
{
    /**
     * Export survey responses to CSV format.
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
        $filename = "survey_{$surveyId}_responses_{$timestamp}.csv";
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        $handle = fopen($filePath, 'w');
        if ($handle === false) {
            throw new RuntimeException("Unable to create export file: $filePath");
        }

        try {
            // Build header row
            $headers = ['Response ID', 'Submit Date', 'Last Page', 'Start Language', 'Seed'];

            // Add question headers based on surveyQuestions
            foreach ($surveyQuestions as $fieldCode => $question) {
                $qid = $question['qid'];
                $headers[] = "Q{$qid}";
            }

            fputcsv($handle, $headers);

            // Write response rows
            foreach ($responses as $response) {
                $row = [
                    $response['id'] ?? '',
                    $response['submitdate'] ?? '',
                    $response['lastpage'] ?? '',
                    $response['startlanguage'] ?? '',
                    $response['seed'] ?? ''
                ];

                // Add answer values in the same order as headers
                foreach ($surveyQuestions as $fieldCode => $question) {
                    $qid = $question['qid'];
                    $value = '';

                    // Find the answer for this question
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

                fputcsv($handle, $row);
            }

            return [
                'filePath' => $filePath,
                'filename' => $filename,
                'mimeType' => $this->getMimeType(),
                'extension' => $this->getFileExtension(),
                'size' => filesize($filePath),
                'responseCount' => count($responses)
            ];
        } finally {
            fclose($handle);
        }
    }

    /**
     * Get the file extension for CSV format.
     *
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'csv';
    }

    /**
     * Get the MIME type for CSV format.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'text/csv';
    }
}
