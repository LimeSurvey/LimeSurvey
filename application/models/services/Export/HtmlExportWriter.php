<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class HtmlExportWriter implements ExportWriterInterface
{
    /** @var resource|null File handle for chunked writing */
    private $handle = null;

    /** @var string|null File path for file output mode */
    private ?string $filePath = null;

    /** @var string Generated filename */
    private string $filename = '';

    /** @var array Metadata from init */
    private array $metadata = [];

    /** @var int Response count */
    private int $responseCount = 0;

    /**
     * Export survey responses to HTML format.
     *
     * @param array $responses The survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @param array $metadata Additional metadata (survey ID, language, outputMode, etc.)
     * @return array Export result with content/filePath and metadata
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function export(array $responses, array $surveyQuestions, array $metadata): array
    {
        $this->init($surveyQuestions, $metadata);
        $this->writeChunk($responses, $surveyQuestions);
        return $this->finalize();
    }

    /**
     * Initialize the writer for chunked export.
     *
     * @param array $surveyQuestions The survey questions field map (for headers)
     * @param array $metadata Additional metadata (surveyId, language, etc.)
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function init(array $surveyQuestions, array $metadata): void
    {
        $this->metadata = $metadata;
        $this->responseCount = 0;

        $surveyId = $metadata['surveyId'];
        $timestamp = date('YmdHis');
        $this->filename = "survey_{$surveyId}_responses_{$timestamp}.html";
        $outputMode = $metadata['outputMode'] ?? 'memory';

        if ($outputMode === 'file') {
            $tempDir = sys_get_temp_dir();
            $this->filePath = $tempDir . DIRECTORY_SEPARATOR . $this->filename;
            $this->handle = fopen($this->filePath, 'w');
            if ($this->handle === false) {
                throw new RuntimeException("Unable to create export file: {$this->filePath}");
            }
        } else {
            $this->handle = fopen('php://temp', 'r+');
            if ($this->handle === false) {
                throw new RuntimeException("Unable to create memory stream for HTML export");
            }
            $this->filePath = null;
        }

        // Write HTML header
        fwrite($this->handle, '<!DOCTYPE html>');
        fwrite($this->handle, '<html lang="en">');
        fwrite($this->handle, '<head>');
        fwrite($this->handle, '<meta charset="UTF-8">');
        fwrite($this->handle, '<meta name="viewport" content="width=device-width, initial-scale=1.0">');
        fwrite($this->handle, '<title>Survey ' . htmlspecialchars($surveyId) . ' Responses Export</title>');
        fwrite($this->handle, '<style>');
        fwrite($this->handle, 'body { font-family: Arial, sans-serif; margin: 20px; }');
        fwrite($this->handle, 'h1 { color: #333; }');
        fwrite($this->handle, 'table { border-collapse: collapse; width: 100%; margin-top: 20px; }');
        fwrite($this->handle, 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
        fwrite($this->handle, 'th { background-color: #4CAF50; color: white; font-weight: bold; }');
        fwrite($this->handle, 'tr:nth-child(even) { background-color: #f2f2f2; }');
        fwrite($this->handle, 'tr:hover { background-color: #ddd; }');
        fwrite($this->handle, '.meta { color: #666; font-size: 14px; margin-bottom: 10px; }');
        fwrite($this->handle, '</style>');
        fwrite($this->handle, '</head>');
        fwrite($this->handle, '<body>');

        fwrite($this->handle, '<h1>Survey ' . htmlspecialchars($surveyId) . ' - Response Export</h1>');
        fwrite($this->handle, '<div class="meta">Export Date: ' . date('Y-m-d H:i:s') . '</div>');
        // Note: Total responses will be updated in finalize via JavaScript or we skip it for streaming

        fwrite($this->handle, '<table>');

        // Write header row
        fwrite($this->handle, '<thead><tr>');
        fwrite($this->handle, '<th>Response ID</th>');
        fwrite($this->handle, '<th>Date submitted</th>');
        fwrite($this->handle, '<th>Last page</th>');
        fwrite($this->handle, '<th>Start language</th>');
        fwrite($this->handle, '<th>Seed</th>');
        fwrite($this->handle, '<th>Date started</th>');
        fwrite($this->handle, '<th>Date last action</th>');
        fwrite($this->handle, '<th>IP address</th>');
        fwrite($this->handle, '<th>Referrer URL</th>');

        foreach ($surveyQuestions as $question) {
            $questionText = $question['question'] ?? $question['title'] ?? "Q{$question['qid']}";
            $questionText = strip_tags(html_entity_decode($questionText, ENT_QUOTES, 'UTF-8'));
            fwrite($this->handle, '<th>' . htmlspecialchars($questionText) . '</th>');
        }
        fwrite($this->handle, '</tr></thead>');
        fwrite($this->handle, '<tbody>');
    }

    /**
     * Write a chunk of responses.
     *
     * @param array $responses Chunk of survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @return void
     */
    public function writeChunk(array $responses, array $surveyQuestions): void
    {
        if ($this->handle === null) {
            throw new RuntimeException("Writer not initialized. Call init() first.");
        }

        $qids = [];
        foreach ($surveyQuestions as $question) {
            $qids[] = $question['qid'];
        }

        foreach ($responses as $response) {
            fwrite($this->handle, '<tr>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['id'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['submitDate'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['lastPage'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['language'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['seed'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['startDate'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['dateLastAction'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['ipAddr'] ?? '')) . '</td>');
            fwrite($this->handle, '<td>' . htmlspecialchars((string)($response['refUrl'] ?? '')) . '</td>');

            $answersByQid = [];
            if (isset($response['answers'])) {
                foreach ($response['answers'] as $answer) {
                    if (isset($answer['qid'])) {
                        $answersByQid[$answer['qid']] = $answer['value'] ?? '';
                    }
                }
            }

            foreach ($qids as $qid) {
                fwrite($this->handle, '<td>' . htmlspecialchars((string)($answersByQid[$qid] ?? '')) . '</td>');
            }

            fwrite($this->handle, '</tr>');
            $this->responseCount++;
        }
    }

    /**
     * Finalize the export and return the result.
     *
     * @return array Export result with content/filePath and metadata
     */
    public function finalize(): array
    {
        if ($this->handle === null) {
            throw new RuntimeException("Writer not initialized. Call init() first.");
        }

        // Write HTML footer
        fwrite($this->handle, '</tbody>');
        fwrite($this->handle, '</table>');
        fwrite($this->handle, '<div class="meta">Total Responses: ' . $this->responseCount . '</div>');
        fwrite($this->handle, '</body>');
        fwrite($this->handle, '</html>');

        $outputMode = $this->metadata['outputMode'] ?? 'memory';

        if ($outputMode === 'file') {
            $size = ftell($this->handle);
            $handle = $this->handle;
            $this->handle = null;
            fclose($handle);

            return [
                'content' => null,
                'filePath' => $this->filePath,
                'filename' => $this->filename,
                'mimeType' => $this->getMimeType(),
                'extension' => $this->getFileExtension(),
                'size' => $size,
                'responseCount' => $this->responseCount
            ];
        } else {
            rewind($this->handle);
            $content = stream_get_contents($this->handle);
            $handle = $this->handle;
            $this->handle = null;
            fclose($handle);

            return [
                'content' => $content,
                'filePath' => null,
                'filename' => $this->filename,
                'mimeType' => $this->getMimeType(),
                'extension' => $this->getFileExtension(),
                'size' => strlen($content),
                'responseCount' => $this->responseCount
            ];
        }
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
