<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class CsvExportWriter implements ExportWriterInterface
{
    /** @var resource|null File handle for chunked writing */
    private $handle = null;

    /** @var string|null File path for file output mode */
    private ?string $filePath = null;

    /** @var string Generated filename */
    private string $filename = '';

    /** @var array Metadata from init */
    private array $metadata = [];

    /** @var int Response count for chunked writing */
    private int $responseCount = 0;

    /** @var bool Whether headers have been written */
    private bool $headersWritten = false;

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
     */
    public function init(array $surveyQuestions, array $metadata): void
    {
        if ($this->headersWritten) {
            return;
        }

        $this->metadata = $metadata;
        $this->responseCount = 0;
        $this->headersWritten = false;

        $surveyId = $metadata['surveyId'];
        $timestamp = date('YmdHis');
        $this->filename = "survey_{$surveyId}_responses_{$timestamp}.csv";
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
                throw new RuntimeException("Unable to create memory stream for CSV export");
            }
            $this->filePath = null;
        }

        $headers = [
            'Response ID',
            'Date submitted',
            'Last page',
            'Start language',
            'Seed',
            'Date started',
            'Date last action',
            'IP address',
            'Referrer URL'
        ];
        foreach ($surveyQuestions as $question) {
            $questionText = $question['question'] ?? $question['title'] ?? "Q{$question['qid']}";
            $questionText = strip_tags(html_entity_decode($questionText, ENT_QUOTES, 'UTF-8'));
            $headers[] = $questionText;
        }
        fputcsv($this->handle, $headers);
        $this->headersWritten = true;
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

        // Pre-extract qids for faster lookup
        $qids = [];
        foreach ($surveyQuestions as $question) {
            $qids[] = $question['qid'];
        }

        foreach ($responses as $response) {
            $row = [
                $response['id'] ?? '',
                $response['submitDate'] ?? '',
                $response['lastPage'] ?? '',
                $response['language'] ?? '',
                $response['seed'] ?? '',
                $response['startDate'] ?? '',
                $response['dateLastAction'] ?? '',
                $response['ipAddr'] ?? '',
                $response['refUrl'] ?? ''
            ];

            $answersByQid = [];
            if (isset($response['answers'])) {
                foreach ($response['answers'] as $answer) {
                    if (isset($answer['qid'])) {
                        $answersByQid[$answer['qid']] = $answer['value'] ?? '';
                    }
                }
            }

            // Now lookup is O(1) per question
            foreach ($qids as $qid) {
                $row[] = $answersByQid[$qid] ?? '';
            }

            fputcsv($this->handle, $row);
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

        $outputMode = $this->metadata['outputMode'] ?? 'memory';

        if ($outputMode === 'file') {
            $size = ftell($this->handle);
            $size = $size === false ? 0 : $size;
            $content = null;
            $filePath = $this->filePath;
        } else {
            rewind($this->handle);
            $content = stream_get_contents($this->handle);
            $content = $content === false ? '' : $content;
            $size = strlen($content);
            $filePath = null;
        }

        $handle = $this->handle;
        $this->handle = null;
        fclose($handle);

        return [
            'content' => $content,
            'filePath' => $filePath,
            'filename' => $this->filename,
            'mimeType' => $this->getMimeType(),
            'extension' => $this->getFileExtension(),
            'size' => $size,
            'responseCount' => $this->responseCount
        ];
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
