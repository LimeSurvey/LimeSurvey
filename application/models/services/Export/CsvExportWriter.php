<?php

namespace LimeSurvey\Models\Services\Export;

use RuntimeException;

class CsvExportWriter implements ExportWriterInterface
{
    use ExportHeadingTrait;

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

    /** @var array Active metadata columns from fieldMap */
    private array $metaColumns = [];

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
        $this->metaColumns = $metadata['metaColumns'] ?? [];

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

        // UTF-8 BOM for Excel compatibility
        fwrite($this->handle, "\xEF\xBB\xBF");

        $headers = [];
        foreach ($this->metaColumns as $meta) {
            $headers[] = $meta['header'];
        }
        foreach ($surveyQuestions as $question) {
            $headers[] = $this->buildQuestionHeading($question);
        }
        $this->writeCsvRow($headers);
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

        // Use field keys (not qids) to correctly map subquestion answers
        $fieldKeys = array_keys($surveyQuestions);

        foreach ($responses as $response) {
            $row = [];
            foreach ($this->metaColumns as $meta) {
                $row[] = $response[$meta['key']] ?? '';
            }

            $answersByKey = [];
            if (isset($response['answers'])) {
                foreach ($response['answers'] as $answer) {
                    $key = $answer['key'] ?? null;
                    if ($key !== null) {
                        $answersByKey[$key] = $answer['value'];
                    }
                }
            }

            foreach ($fieldKeys as $fieldKey) {
                $row[] = $answersByKey[$fieldKey] ?? '';
            }

            $this->writeCsvRow($row);
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
     * Write a single CSV row with RFC 4180 line endings (\r\n).
     *
     * @param array $fields
     * @return void
     */
    private function writeCsvRow(array $fields): void
    {
        $escaped = [];
        foreach ($fields as $field) {
            $escaped[] = $this->csvEscape($field);
        }
        fwrite($this->handle, implode(',', $escaped) . "\r\n");
    }

    /**
     * Escape a value for CSV output.
     *
     * @param mixed $value
     * @return string
     */
    private function csvEscape($value): string
    {
        if (is_null($value) || $value === '') {
            return '';
        }
        $value = (string) $value;
        $value = preg_replace('~\R~u', "\n", $value);
        return '"' . str_replace('"', '""', $value) . '"';
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
