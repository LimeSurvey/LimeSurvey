<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class CsvExportWriter implements ExportWriterInterface
{
    /**
     * Export survey responses to CSV format.
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
        $filename = "survey_{$surveyId}_responses_{$timestamp}.csv";
        $outputMode = $metadata['outputMode'] ?? 'memory';

        // Determine output destination
        if ($outputMode === 'file') {
            $tempDir = sys_get_temp_dir();
            $filePath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            $handle = fopen($filePath, 'w');
            if ($handle === false) {
                throw new RuntimeException("Unable to create export file: $filePath");
            }
        } else {
            // Use memory stream for in-memory generation
            $handle = fopen('php://temp', 'r+');
            if ($handle === false) {
                throw new RuntimeException("Unable to create memory stream for CSV export");
            }
            $filePath = null;
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

            // Prepare return value based on output mode
            if ($outputMode === 'file') {
                $size = ftell($handle);
                fclose($handle);
                return [
                    'content' => null,
                    'filePath' => $filePath,
                    'filename' => $filename,
                    'mimeType' => $this->getMimeType(),
                    'extension' => $this->getFileExtension(),
                    'size' => $size,
                    'responseCount' => count($responses)
                ];
            } else {
                // Get the content from the stream
                rewind($handle);
                $content = stream_get_contents($handle);
                fclose($handle);
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
        } catch (\Exception $e) {
            fclose($handle);
            if ($outputMode === 'file' && $filePath && file_exists($filePath)) {
                unlink($filePath);
            }
            throw $e;
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
