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
