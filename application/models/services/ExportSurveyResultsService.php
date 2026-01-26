<?php

namespace LimeSurvey\Models\Services;

use CDbException;
use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\ResponseMappingTrait;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\Export\ExportWriterInterface;
use LimeSurvey\Models\Services\Export\CsvExportWriter;
use LimeSurvey\Models\Services\Export\HtmlExportWriter;
use RuntimeException;
use Survey;
use Answer;
use SurveyDynamic;

class ExportSurveyResultsService
{
    use ResponseMappingTrait;

    protected $supportedExportTypes = ['csv', 'html'];

    /**
     * @var Survey
     */
    protected $survey;

    /**
     * @var Answer
     */
    protected $answerModel;

    /**
     * @var FilterPatcher
     */
    protected $responseFilterPatcher;

    /**
     * @var TransformerOutputSurveyResponses
     */
    protected $transformerOutputSurveyResponses;

    /**
     * ExportSurveyResultsService constructor.
     *
     * @param Survey $survey
     * @param Answer $answerModel
     * @param FilterPatcher $responseFilterPatcher
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     */
    public function __construct(
        Survey $survey,
        Answer $answerModel,
        FilterPatcher $responseFilterPatcher,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses
    ) {
        $this->survey = $survey;
        $this->answerModel = $answerModel;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
    }

    /**
     * Export survey responses to the specified format.
     *
     * @param int $surveyId The survey ID
     * @param string $exportType The export format (csv, xlsx, xls, pdf, html)
     * @param string|null $language The language code for the export
     * @param string $outputMode Output mode: 'memory' (default) or 'file'
     * @param int $chunkSize Number of responses to fetch per chunk (default 100)
     * @return array Export result with content/filePath and metadata
     * @throws InvalidArgumentException If the export type is not supported
     * @throws RuntimeException If survey is not found or responses cannot be fetched
     */
    public function exportResponses(
        $surveyId,
        $exportType,
        $language = null,
        $outputMode = 'memory',
        $chunkSize = 500
    ) {
        if (!in_array($exportType, $this->supportedExportTypes)) {
            throw new InvalidArgumentException("Unsupported export type: $exportType");
        }

        // Fetch the survey
        $survey = $this->survey->findByPk($surveyId);
        if ($survey === null) {
            throw new RuntimeException("Survey not found: $surveyId");
        }
        $this->survey = $survey;

        // Use provided language or default to survey language
        if ($language === null) {
            $language = $survey->language;
        }

        // Prepare metadata
        $metadata = [
            'surveyId' => $surveyId,
            'language' => $language,
            'exportType' => $exportType,
            'outputMode' => $outputMode
        ];

        // Export responses using chunked writing
        return $this->exportResponsesInChunks($surveyId, $exportType, $metadata, $chunkSize);
    }

    /**
     * Export survey responses in chunks, writing directly to the export writer.
     *
     * @param int $surveyId
     * @param string $exportType
     * @param array $metadata
     * @param int $chunkSize Number of responses per chunk
     * @return array Export result from the writer
     * @throws RuntimeException If responses cannot be fetched
     */
    protected function exportResponsesInChunks($surveyId, $exportType, array $metadata, $chunkSize = 500)
    {
        // Generate field map for questions (do this once)
        $this->transformerOutputSurveyResponses->fieldMap =
            createFieldMap($this->survey, 'full', false, false);

        // Get question field map (do this once)
        $surveyQuestions = $this->getQuestionFieldMap();

        // Pre-cache the token table check to avoid repeated tableExists() calls
        $this->transformerOutputSurveyResponses->hasTokenTable =
            tableExists('tokens_' . $surveyId);

        // Get the appropriate export writer
        $writer = $this->getExportWriter($exportType);
        $writer->init($surveyQuestions, $metadata);
        $totalCount = $this->getTotalResponseCount($surveyId);

        $model = SurveyDynamic::model($surveyId);

        for ($offset = 0; $offset < $totalCount; $offset += $chunkSize) {
            $chunk = $this->fetchResponseChunkDirect($model, $offset, $chunkSize);
            $processedChunk = $this->processResponseChunk($chunk, $surveyQuestions);

            $writer->writeChunk($processedChunk, $surveyQuestions);

            unset($chunk, $processedChunk);
        }

        return $writer->finalize();
    }

    /**
     * Get the total count of survey responses.
     *
     * @param int $surveyId
     * @return int Total number of responses
     * @throws RuntimeException If count cannot be fetched
     */
    protected function getTotalResponseCount($surveyId)
    {
        $model = SurveyDynamic::model($surveyId);

        try {
            return (int) $model->count();
        } catch (CDbException $e) {
            throw new RuntimeException("Unable to get response count: " . $e->getMessage());
        }
    }

    /**
     * Fetch a single chunk of survey responses using direct query (faster than DataProvider).
     *
     * @param SurveyDynamic $model Pre-instantiated model
     * @param int $offset Starting position
     * @param int $limit Number of responses to fetch
     * @return array Array of survey response objects
     * @throws RuntimeException If responses cannot be fetched
     */
    protected function fetchResponseChunkDirect($model, $offset, $limit)
    {
        $criteria = new \CDbCriteria();
        $criteria->limit = $limit;
        $criteria->offset = $offset;

        try {
            return $model->findAll($criteria);
        } catch (CDbException $e) {
            throw new RuntimeException("Unable to fetch survey responses: " . $e->getMessage());
        }
    }

    /**
     * Process a chunk of responses (transform and map to questions).
     * Optimized for export - skips unnecessary processing like actual_aid lookup.
     *
     * @param array $chunk Array of raw survey response objects
     * @param array $surveyQuestions Question field map
     * @return array Processed and mapped responses
     */
    protected function processResponseChunk(array $chunk, array $surveyQuestions)
    {
        $transformedResponses = $this->transformerOutputSurveyResponses->transform(
            $chunk,
            ['survey' => $this->survey]
        );

        foreach ($transformedResponses as $index => &$response) {
            // Add raw attributes that may not be in transformed output
            if (isset($chunk[$index]) && $chunk[$index] instanceof SurveyDynamic) {
                $rawAttributes = $chunk[$index]->attributes;
                foreach ($rawAttributes as $key => $value) {
                    if (!isset($response[$key])) {
                        $response[$key] = $value;
                    }
                }
            }

            if (isset($response['answers'])) {
                foreach ($response['answers'] as &$answer) {
                    $key = $answer['key'] ?? null;
                    if ($key !== null && isset($surveyQuestions[$key])) {
                        $answer['qid'] = $surveyQuestions[$key]['qid'];
                    }
                }
            }
        }

        return $transformedResponses;
    }

    /**
     * Get the appropriate export writer for the given type.
     *
     * @param string $exportType
     * @return ExportWriterInterface
     * @throws InvalidArgumentException
     */
    protected function getExportWriter($exportType): ExportWriterInterface
    {
        switch ($exportType) {
            case 'csv':
                return new CsvExportWriter();
            case 'html':
                return new HtmlExportWriter();
            default:
                throw new InvalidArgumentException("Unsupported export type: $exportType");
        }
    }
}
