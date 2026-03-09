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
use Question;
use SurveyDynamic;

class ExportSurveyResultsService
{
    use ResponseMappingTrait;

    protected $supportedExportTypes = ['csv', 'html'];

    /**
     * @var Survey Injected model instance used for querying.
     */
    protected $survey;

    /**
     * @var Survey|null The loaded survey instance for the current export.
     */
    protected $loadedSurvey;

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
     * @param int $chunkSize Number of responses to fetch per chunk (default 500)
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
        if (!is_int($chunkSize) || $chunkSize <= 0) {
            throw new InvalidArgumentException("Invalid chunkSize: must be a positive integer, got: " . var_export($chunkSize, true));
        }

        if (!in_array($exportType, $this->supportedExportTypes)) {
            throw new InvalidArgumentException("Unsupported export type: $exportType");
        }

        // Fetch the survey
        $survey = $this->survey->findByPk($surveyId);
        if ($survey === null) {
            throw new RuntimeException("Survey not found: $surveyId");
        }
        $this->loadedSurvey = $survey;

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
            createFieldMap($this->loadedSurvey, 'full', false, false, $metadata['language'] ?? '');

        // Get question field map (do this once)
        $surveyQuestions = $this->getQuestionFieldMap();

        // Add timing columns if enabled
        $hasTimings = $this->loadedSurvey->savetimings == "Y"
            && tableExists("survey_{$surveyId}_timings");
        $timingFieldKeys = [];
        if ($hasTimings) {
            $timingFieldMap = $this->getTimingFieldMap($metadata['language'] ?? null);
            $surveyQuestions = $surveyQuestions + $timingFieldMap;
            $timingFieldKeys = array_keys($timingFieldMap);
        }

        // Pre-cache token table existence for the transformer
        $this->transformerOutputSurveyResponses->hasTokenTable =
            tableExists('tokens_' . $surveyId);

        $writer = $this->getExportWriter($exportType);
        $writer->init($surveyQuestions, $metadata);

        $totalCount = $this->getTotalResponseCount($surveyId);
        $model = SurveyDynamic::model($surveyId);

        for ($offset = 0; $offset < $totalCount; $offset += $chunkSize) {
            $chunk = $this->fetchResponseChunkDirect($model, $offset, $chunkSize);

            $timingsData = [];
            if ($hasTimings) {
                $timingsData = $this->fetchTimingsForChunk($surveyId, $chunk);
            }

            $processedChunk = $this->processResponseChunk(
                $chunk,
                $surveyQuestions,
                $timingsData,
                $timingFieldKeys
            );
            $writer->writeChunk($processedChunk, $surveyQuestions);
            unset($chunk, $processedChunk, $timingsData);
        }

        return $writer->finalize();
    }

    /**
     * Get the total number of responses for a survey.
     *
     * @param int $surveyId
     * @return int
     * @throws RuntimeException
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
     * Fetch a chunk of responses directly using CDbCriteria.
     *
     * @param SurveyDynamic $model
     * @param int $offset
     * @param int $limit
     * @return SurveyDynamic[]
     * @throws RuntimeException
     */
    protected function fetchResponseChunkDirect($model, $offset, $limit)
    {
        $criteria = new \CDbCriteria();
        $criteria->order = $model->primaryKey() . ' ASC';
        $criteria->limit = $limit;
        $criteria->offset = $offset;

        try {
            return $model->findAll($criteria);
        } catch (CDbException $e) {
            throw new RuntimeException("Unable to fetch survey responses: " . $e->getMessage());
        }
    }

    /**
     * Process a chunk of SurveyDynamic responses through the transformer,
     * override dates with raw values, format answers, and merge timing data.
     *
     * @param array $chunk Array of SurveyDynamic objects
     * @param array $surveyQuestions The survey questions field map
     * @param array $timingsData Timing data indexed by response ID
     * @param array $timingFieldKeys Array of timing field keys
     * @return array Processed responses ready for the writer
     */
    protected function processResponseChunk(
        array $chunk,
        array $surveyQuestions,
        array $timingsData = [],
        array $timingFieldKeys = []
    ) {
        $transformedResponses = $this->transformerOutputSurveyResponses->transform(
            $chunk,
            ['survey' => $this->loadedSurvey]
        );

        $dummyDate = '1980-01-01 00:00:00';

        foreach ($transformedResponses as $index => &$response) {
            // Override date fields with raw DB values (bypass ISO 8601 formatting)
            if (isset($chunk[$index]) && $chunk[$index] instanceof SurveyDynamic) {
                $rawAttributes = $chunk[$index]->attributes;

                // Make all raw attributes available
                foreach ($rawAttributes as $key => $value) {
                    if (!isset($response[$key])) {
                        $response[$key] = $value;
                    }
                }

                $rawSubmitDate = $rawAttributes['submitdate'] ?? null;
                $response['submitDate'] = ($rawSubmitDate === $dummyDate) ? null : $rawSubmitDate;

                $rawStartDate = $rawAttributes['startdate'] ?? null;
                $response['startDate'] = ($rawStartDate === $dummyDate) ? null : $rawStartDate;

                $rawDatestamp = $rawAttributes['datestamp'] ?? null;
                $response['dateLastAction'] = ($rawDatestamp === $dummyDate) ? null : $rawDatestamp;
            }

            // Format answer values using full answer formatting
            if (isset($response['answers'])) {
                foreach ($response['answers'] as &$answer) {
                    $key = $answer['key'] ?? null;
                    if ($key !== null && isset($surveyQuestions[$key])) {
                        $question = $surveyQuestions[$key];
                        $answer['qid'] = $question['qid'];
                        $answer['value'] = $this->formatFullAnswer(
                            $answer['value'],
                            $question['type'] ?? null,
                            $key
                        );
                    }
                }

                // Merge timing data into answers
                if (!empty($timingsData)) {
                    $responseId = $response['id'];
                    if (isset($timingsData[$responseId])) {
                        $timingRow = $timingsData[$responseId];
                        foreach ($timingFieldKeys as $fieldKey) {
                            $response['answers'][$fieldKey] = [
                                'key' => $fieldKey,
                                'value' => $timingRow[$fieldKey] ?? '',
                            ];
                        }
                    }
                }
            }
        }

        return $transformedResponses;
    }

    /**
     * Format a raw answer value to its display text,
     * matching the old export's "full answer" format.
     *
     * @param mixed $value Raw answer value from the database
     * @param string|null $type Question type character
     * @param string $fieldKey Full field key (e.g. "123X456X789SQ001")
     * @return mixed Formatted display value
     */
    protected function formatFullAnswer($value, $type, $fieldKey)
    {
        if ($type === null) {
            return $value;
        }

        switch ($type) {
            case Question::QT_M_MULTIPLE_CHOICE:
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                // Comment and "other" fields: pass through raw value
                if (str_ends_with($fieldKey, 'other') || str_ends_with($fieldKey, 'comment')) {
                    return $value;
                }
                // Checkbox fields: Y/N/null -> Yes/No/N/A
                if ($value === 'Y') {
                    return gT('Yes');
                }
                if ($value === 'N' || $value === '') {
                    return gT('No');
                }
                return gT('N/A');

            case Question::QT_Y_YES_NO_RADIO:
                if ($value === 'Y') {
                    return gT('Yes');
                }
                if ($value === 'N') {
                    return gT('No');
                }
                return gT('N/A');

            case Question::QT_G_GENDER:
                if ($value === 'M') {
                    return gT('Male');
                }
                if ($value === 'F') {
                    return gT('Female');
                }
                return gT('N/A');

            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                if ($value === 'Y') {
                    return gT('Yes');
                }
                if ($value === 'N') {
                    return gT('No');
                }
                if ($value === 'U') {
                    return gT('Uncertain');
                }
                return $value;

            case Question::QT_E_ARRAY_INC_SAME_DEC:
                if ($value === 'I') {
                    return gT('Increase');
                }
                if ($value === 'S') {
                    return gT('Same');
                }
                if ($value === 'D') {
                    return gT('Decrease');
                }
                return $value;

            default:
                return $value;
        }
    }

    /**
     * Get the question field map with export-specific fields.
     * Overrides the trait method to include additional fields needed for export.
     *
     * @return array
     */
    protected function getQuestionFieldMap(): array
    {
        $fieldMap = $this->transformerOutputSurveyResponses->fieldMap;

        return array_filter(
            array_map(
                function ($item) {
                    if (!empty($item['qid'])) {
                        return [
                            'fieldname' => $item['fieldname'] ?? null,
                            'gid' => $item['gid'],
                            'qid' => $item['qid'],
                            'aid' => $item['aid'] ?? null,
                            'sqid' => $item['sqid'] ?? null,
                            'scaleid' => $item['scale_id'] ?? null,
                            'title' => $item['title'] ?? null,
                            'question' => $item['question'] ?? null,
                            'subquestion' => $item['subquestion'] ?? null,
                            'subquestion1' => $item['subquestion1'] ?? null,
                            'subquestion2' => $item['subquestion2'] ?? null,
                            'scale' => $item['scale'] ?? null,
                            'type' => $item['type'] ?? null,
                        ];
                    }
                    return null;
                },
                $fieldMap
            )
        );
    }

    /**
     * Generate timing field map entries for export headers and data mapping.
     *
     * @param string|null $language
     * @return array Timing field map keyed by field name
     */
    protected function getTimingFieldMap($language = null)
    {
        $surveyId = $this->loadedSurvey->sid;
        $timingsFieldMap = createTimingsFieldMap(
            $surveyId,
            'full',
            true,
            false,
            $language ?? $this->loadedSurvey->language
        );

        $result = [];
        foreach ($timingsFieldMap as $key => $field) {
            $result[$key] = [
                'fieldname' => $field['fieldname'],
                'gid' => $field['gid'] ?? '',
                'qid' => $field['qid'] ?? '',
                'aid' => $field['aid'] ?? '',
                'sqid' => null,
                'scaleid' => null,
                'title' => $field['title'] ?? '',
                'question' => $field['question'] ?? '',
                'subquestion' => null,
                'subquestion1' => null,
                'subquestion2' => null,
                'scale' => null,
                'type' => $field['type'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Fetch timing data for a chunk of SurveyDynamic response objects.
     *
     * @param int $surveyId
     * @param array $chunk Array of SurveyDynamic objects
     * @return array Timing rows indexed by response ID
     */
    protected function fetchTimingsForChunk($surveyId, array $chunk)
    {
        $ids = [];
        foreach ($chunk as $response) {
            if ($response instanceof SurveyDynamic) {
                $ids[] = $response->id;
            }
        }

        if (empty($ids)) {
            return [];
        }

        $tableName = "{{survey_{$surveyId}_timings}}";
        $rows = \Yii::app()->db->createCommand()
            ->select('*')
            ->from($tableName)
            ->where(['in', 'id', $ids])
            ->queryAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['id']] = $row;
        }

        return $result;
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
