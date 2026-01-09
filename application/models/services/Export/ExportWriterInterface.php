<?php

namespace LimeSurvey\Models\Services\Export;

interface ExportWriterInterface
{
    /**
     * Export survey responses to the specified format.
     *
     * @param array $responses The survey responses data
     * @param array $surveyQuestions The survey questions field map
     * @param array $metadata Additional metadata (survey ID, language, etc.)
     * @return array Export result with file path and metadata
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
