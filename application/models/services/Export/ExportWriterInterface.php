<?php

namespace LimeSurvey\Models\Services\Export;

interface ExportWriterInterface
{
    /**
     * Export survey responses to the specified format.
     *
     * Can generate content in-memory or write to a file depending on outputMode in metadata.
     *
     * @param array $responses The survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @param array $metadata Additional metadata including:
     *                        - surveyId: int Survey ID
     *                        - language: string Language code
     *                        - outputMode: string 'memory' (default) or 'file'
     * @return array Export result with the following keys:
     *               - content: string|null The generated export content (if outputMode is 'memory')
     *               - filePath: string|null Path to the export file (if outputMode is 'file')
     *               - filename: string Suggested filename for the export
     *               - mimeType: string MIME type of the content
     *               - extension: string File extension
     *               - size: int Size of the content in bytes
     *               - responseCount: int Number of responses exported
     */
    public function export(array $responses, array $surveyQuestions, array $metadata): array;

    /**
     * Initialize the writer for chunked export.
     *
     * @param array $surveyQuestions The survey questions field map (for headers)
     * @param array $metadata Additional metadata (surveyId, language, etc.)
     * @return void
     */
    public function init(array $surveyQuestions, array $metadata): void;

    /**
     * Write a chunk of responses.
     *
     * @param array $responses Chunk of survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @return void
     */
    public function writeChunk(array $responses, array $surveyQuestions): void;

    /**
     * Finalize the export and return the result.
     *
     * @return array Export result with content/filePath and metadata
     */
    public function finalize(): array;

    /**
     * Get the file extension for this export format.
     *
     * @return string File extension (e.g., 'csv', 'xlsx', 'pdf')
     */
    public function getFileExtension(): string;

    /**
     * Get the MIME type for this export format.
     *
     * @return string MIME type (e.g., 'text/csv', 'application/pdf')
     */
    public function getMimeType(): string;
}
