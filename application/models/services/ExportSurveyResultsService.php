<?php

namespace LimeSurvey\Models\Services;

use CDbException;
use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\ResponseMappingTrait;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\Export\ExportAnswerFormatter;
use LimeSurvey\Models\Services\Export\ExportWriterInterface;
use LimeSurvey\Models\Services\Export\CsvExportWriter;
use LimeSurvey\Models\Services\Export\HtmlExportWriter;
use LimeSurvey\Models\Services\SurveyAnswerCache;
use RuntimeException;
use Survey;
use SurveyDynamic;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
class ExportSurveyResultsService
{
    use ResponseMappingTrait;

    private const META_COLUMN_MAP = [
        'id'            => ['key' => 'id',             'header' => 'Response ID'],
        'submitdate'    => ['key' => 'submitDate',     'header' => 'Date submitted'],
        'lastpage'      => ['key' => 'lastPage',       'header' => 'Last page'],
        'startlanguage' => ['key' => 'language',       'header' => 'Start language'],
        'seed'          => ['key' => 'seed',            'header' => 'Seed'],
        'token'         => ['key' => 'token',           'header' => 'Token'],
        'startdate'     => ['key' => 'startDate',      'header' => 'Date started'],
        'datestamp'     => ['key' => 'dateLastAction',  'header' => 'Date last action'],
        'ipaddr'        => ['key' => 'ipAddr',         'header' => 'IP address'],
        'refurl'        => ['key' => 'refUrl',         'header' => 'Referrer URL'],
    ];

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
     * @var SurveyAnswerCache
     */
    protected $answerCache;

    /**
     * @var FilterPatcher
     */
    protected $responseFilterPatcher;

    /**
     * @var TransformerOutputSurveyResponses
     */
    protected $transformerOutputSurveyResponses;

    /**
     * @var ExportAnswerFormatter
     */
    protected $answerFormatter;

    /** @var string|null Language code for the export. Defaults to survey base language. */
    protected $language;

    /** @var string Output mode: 'memory' or 'file'. */
    protected $outputMode = 'memory';

    /** @var int Number of responses to fetch per chunk. */
    protected $chunkSize = 500;

    /** @var string 'long' for full translated answers, 'short' for raw answer codes. */
    protected $answerFormat = 'long';

    /**
     * ExportSurveyResultsService constructor.
     *
     * @param Survey $survey
     * @param FilterPatcher $responseFilterPatcher
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     * @param ExportAnswerFormatter $answerFormatter
     * @param SurveyAnswerCache $answerCache
     */
    public function __construct(
        Survey $survey,
        FilterPatcher $responseFilterPatcher,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses,
        ExportAnswerFormatter $answerFormatter,
        SurveyAnswerCache $answerCache
    ) {
        $this->survey = $survey;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
        $this->answerFormatter = $answerFormatter;
        $this->answerCache = $answerCache;
    }

    /**
     * @param string|null $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $outputMode 'memory' or 'file'
     * @return $this
     */
    public function setOutputMode($outputMode)
    {
        $this->outputMode = $outputMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutputMode()
    {
        return $this->outputMode;
    }

    /**
     * @param int $chunkSize
     * @return $this
     */
    public function setChunkSize($chunkSize)
    {
        if (!is_int($chunkSize) || $chunkSize <= 0) {
            throw new InvalidArgumentException("Invalid chunkSize: must be a positive integer, got: " . var_export($chunkSize, true));
        }
        $this->chunkSize = $chunkSize;
        return $this;
    }

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * @param string $answerFormat 'long' or 'short'
     * @return $this
     */
    public function setAnswerFormat($answerFormat)
    {
        $this->answerFormat = $answerFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnswerFormat()
    {
        return $this->answerFormat;
    }

    /**
     * Export survey responses to the specified format.
     *
     * @param int $surveyId The survey ID
     * @param string $exportType The export format (csv, html)
     * @return array Export result with content/filePath and metadata
     * @throws InvalidArgumentException If the export type is not supported
     * @throws RuntimeException If survey is not found or responses cannot be fetched
     */
    public function exportResponses(
        $surveyId,
        $exportType
    ) {
        if (!in_array($exportType, $this->supportedExportTypes)) {
            throw new InvalidArgumentException("Unsupported export type: $exportType");
        }

        // Fetch the survey
        $survey = $this->survey->findByPk($surveyId);
        if ($survey === null) {
            throw new RuntimeException("Survey not found: $surveyId");
        }
        $this->loadedSurvey = $survey;

        // Use configured language or default to survey language
        $language = $this->language ?? $survey->language;

        // Prepare metadata
        $metadata = [
            'surveyId' => $surveyId,
            'language' => $language,
            'exportType' => $exportType,
            'outputMode' => $this->outputMode,
        ];

        // Export responses using chunked writing
        return $this->exportResponsesInChunks($surveyId, $exportType, $metadata);
    }

    /**
     * Export survey responses in chunks, writing directly to the export writer.
     *
     * @param int $surveyId
     * @param string $exportType
     * @param array $metadata
     * @return array Export result from the writer
     * @throws RuntimeException If responses cannot be fetched
     */
    protected function exportResponsesInChunks($surveyId, $exportType, array $metadata)
    {
        $language = $metadata['language'] ?? $this->loadedSurvey->language;

        // Generate field map for questions (do this once)
        // force_refresh = true to bypass stale session-cached field maps
        $fieldMap = createFieldMap($this->loadedSurvey, 'full', true, false, $language);
        $this->transformerOutputSurveyResponses->fieldMap = $this->expandRankingFieldMap($surveyId, $fieldMap, $language);

        // Pre-cache token table existence for the transformer
        $this->transformerOutputSurveyResponses->hasTokenTable =
            tableExists('tokens_' . $surveyId);

        // Pre-load answer data for list/array/ranking question types
        $this->answerFormatter->loadAnswers($surveyId, $language);

        // Get question field map (do this once)
        $surveyQuestions = $this->getQuestionFieldMap();

        // Add timing columns if enabled
        $hasTimings = $this->loadedSurvey->savetimings == "Y"
            && (tableExists("survey_{$surveyId}_timings") || tableExists("timings_{$surveyId}"));
        $timingFieldKeys = [];
        if ($hasTimings) {
            $timingFieldMap = $this->getTimingFieldMap($metadata['language'] ?? null);
            $surveyQuestions = $surveyQuestions + $timingFieldMap;
            $timingFieldKeys = array_keys($timingFieldMap);
        }

        // Compute active meta columns from fieldMap
        $metaColumns = [];
        foreach (self::META_COLUMN_MAP as $fieldMapKey => $meta) {
            if (isset($this->transformerOutputSurveyResponses->fieldMap[$fieldMapKey])) {
                $metaColumns[] = $meta;
            }
        }
        $metadata['metaColumns'] = $metaColumns;

        $writer = $this->getExportWriter($exportType);
        $writer->init($surveyQuestions, $metadata);

        $totalCount = $this->getTotalResponseCount($surveyId);
        $model = SurveyDynamic::model($surveyId);

        for ($offset = 0; $offset < $totalCount; $offset += $this->chunkSize) {
            $chunk = $this->fetchResponseChunkDirect($model, $offset, $this->chunkSize);

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
     * Replaces each ranking question's single combined fieldmap entry
     * ("Q{qid}", storing a JSON array of ranked subquestion codes) with a
     * dynamically sized, ordered list of fresh per-rank-position entries
     * ("Q{qid}_rank{n}"), and registers those with the transformer so
     * extractAnswers() emits one answer per rank instead of one JSON blob.
     *
     * The number of columns is taken from the data being exported rather than
     * from the question's subquestion count: a respondent never ranks more
     * items than exist, but may rank fewer, and which is which isn't knowable
     * from the survey definition alone (mirrors the classic exporter's
     * ExportSurveyResultsService::expandRankingColumns() in
     * application/helpers/admin/exportresults_helper.php).
     *
     * The old, static per-subquestion fieldmap entries ("Q{qid}_S{sqid}",
     * capped by the max_subquestions attribute) are dropped rather than kept,
     * to avoid duplicate/blank output columns for the same ranking question.
     *
     * @param int $surveyId
     * @param array $fieldMap
     * @param string $language
     * @return array
     */
    private function expandRankingFieldMap($surveyId, array $fieldMap, $language)
    {
        $this->transformerOutputSurveyResponses->rankingFields = [];
        $expanded = [];
        foreach ($fieldMap as $fieldName => $field) {
            if (($field['type'] ?? null) !== \Question::QT_R_RANKING) {
                $expanded[$fieldName] = $field;
                continue;
            }
            if (($field['suffix'] ?? '') !== '') {
                // Superseded static per-subquestion entry; drop (see docblock).
                continue;
            }

            $qid = $field['qid'];
            // Upper bound: a response can never rank more items than the question defines.
            $itemCount = count(getSubQuestions($surveyId, $qid, $language));
            $usedRankCount = $this->getMaxRankedItemCount($surveyId, $qid);
            $columnCount = $usedRankCount > 0 ? min($usedRankCount, $itemCount) : $itemCount;

            $this->transformerOutputSurveyResponses->rankingFields[$fieldName] = [
                'qid' => $qid,
                'gid' => $field['gid'],
                'sid' => $surveyId,
                'columnCount' => $columnCount,
            ];

            for ($position = 1; $position <= $columnCount; $position++) {
                $rankFieldName = "Q{$qid}_rank{$position}";
                $expanded[$rankFieldName] = [
                    'fieldname' => $rankFieldName,
                    'type' => \Question::QT_R_RANKING,
                    'sid' => $surveyId,
                    'gid' => $field['gid'],
                    'qid' => $qid,
                    'aid' => $position,
                    'suffix' => "_rank{$position}",
                    'title' => $field['title'] ?? '',
                    'question' => $field['question'] ?? '',
                    'subquestion' => sprintf(gT('Rank %s'), $position),
                    'group_name' => $field['group_name'] ?? '',
                    'mandatory' => $field['mandatory'] ?? 'N',
                    'encrypted' => $field['encrypted'] ?? 'N',
                ];
            }
        }
        return $expanded;
    }

    /**
     * Finds the highest number of ranked items actually present across all
     * responses to a survey, by decoding the ranking question's raw JSON
     * column for every row. Used to size the exported rank-position columns
     * to what the data actually contains. This export path has no response
     * filtering (exportResponses() takes no filter parameter), so every row
     * in the responses table is scanned.
     *
     * @param int $surveyId
     * @param int $qid
     * @return int
     */
    private function getMaxRankedItemCount($surveyId, $qid)
    {
        $baseField = "Q{$qid}";
        $rawValues = \Yii::app()->db->createCommand()
            ->select($baseField)
            ->from(SurveyDynamic::model($surveyId)->tableName())
            ->queryColumn();

        $maxCount = 0;
        foreach ($rawValues as $rawValue) {
            if ($rawValue === null || $rawValue === '') {
                continue;
            }
            $rankedCodes = json_decode($rawValue, true);
            if (is_array($rankedCodes)) {
                $maxCount = max($maxCount, count($rankedCodes));
            }
        }
        return $maxCount;
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

        foreach ($transformedResponses as $index => &$response) {
            if (isset($chunk[$index]) && $chunk[$index] instanceof SurveyDynamic) {
                $this->overrideDateFields($response, $chunk[$index]->attributes);
            }

            if (isset($response['answers'])) {
                foreach ($response['answers'] as &$answer) {
                    $key = $answer['key'] ?? null;
                    if ($key !== null && isset($surveyQuestions[$key])) {
                        $question = $surveyQuestions[$key];
                        $answer['qid'] = $question['qid'];
                        if ($this->answerFormat === 'long') {
                            $answer['value'] = $this->answerFormatter->formatFullAnswer(
                                $answer['value'],
                                $question['type'] ?? null,
                                $key,
                                $question['qid']
                            );
                        }
                    }
                }
                $this->mergeTimingData($response, $timingsData, $timingFieldKeys);
            }
        }

        return $transformedResponses;
    }

    /**
     * Override date fields with raw DB values (bypass ISO 8601 formatting)
     * and merge any missing raw attributes into the response.
     *
     * @param array $response
     * @param array $rawAttributes
     */
    private function overrideDateFields(array &$response, array $rawAttributes)
    {
        $dummyDate = '1980-01-01 00:00:00';

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

    /**
     * Merge timing data into the response's answers array.
     *
     * @param array $response
     * @param array $timingsData Timing data indexed by response ID
     * @param array $timingFieldKeys
     */
    private function mergeTimingData(array &$response, array $timingsData, array $timingFieldKeys)
    {
        if (empty($timingsData)) {
            return;
        }
        $responseId = $response['id'];
        if (!isset($timingsData[$responseId])) {
            return;
        }
        $timingRow = $timingsData[$responseId];
        foreach ($timingFieldKeys as $fieldKey) {
            $response['answers'][$fieldKey] = [
                'key' => $fieldKey,
                'value' => $timingRow[$fieldKey] ?? '',
            ];
        }
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

        $tableName = tableExists("{{timings_{$surveyId}}}") ? "{{timings_{$surveyId}}}" : "{{survey_{$surveyId}_timings}}";
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
