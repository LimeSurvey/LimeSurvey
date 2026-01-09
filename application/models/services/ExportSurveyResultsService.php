<?php

namespace LimeSurvey\Models\Services;

use CDbException;
use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;
use LimeSurvey\Models\Services\Export\ExportWriterInterface;
use LimeSurvey\Models\Services\Export\CsvExportWriter;
use LimeSurvey\Models\Services\Export\XlsxExportWriter;
use LimeSurvey\Models\Services\Export\XlsExportWriter;
use LimeSurvey\Models\Services\Export\PdfExportWriter;
use LimeSurvey\Models\Services\Export\HtmlExportWriter;
use RuntimeException;
use Survey;
use Answer;

class ExportSurveyResultsService
{
    protected $supportedExportTypes = ['csv', 'xlsx', 'xls', 'pdf', 'html'];

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
     * @return array Export result with file path and metadata
     * @throws InvalidArgumentException If the export type is not supported
     * @throws RuntimeException If survey is not found or responses cannot be fetched
     */
    public function exportResponses($surveyId, $exportType, $language = null)
    {
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

        // Fetch survey responses
        $responsesData = $this->fetchSurveyResponses($surveyId);

        // Get the appropriate export writer
        $writer = $this->getExportWriter($exportType);

        // Prepare metadata
        $metadata = [
            'surveyId' => $surveyId,
            'language' => $language,
            'exportType' => $exportType
        ];

        // Delegate to the writer
        return $writer->export(
            $responsesData['responses'],
            $responsesData['surveyQuestions'],
            $metadata
        );
    }

    /**
     * Fetch survey responses using similar logic to SurveyResponses command.
     *
     * @param int $surveyId
     * @return array Array with 'responses' and 'surveyQuestions' keys
     * @throws RuntimeException If responses cannot be fetched
     */
    protected function fetchSurveyResponses($surveyId)
    {
        $model = \SurveyDynamic::model($surveyId);

        // Build criteria without filters for export (get all responses)
        $criteria = new \LSDbCriteria();
        $sort = new \CSort();

        // Create data provider to fetch all responses
        $dataProvider = new \LSCActiveDataProvider(
            $model,
            array(
                'sort' => $sort,
                'criteria' => $criteria,
                'pagination' => false // Get all responses for export
            )
        );

        try {
            $surveyResponses = $dataProvider->getData();
        } catch (CDbException $e) {
            throw new RuntimeException("Unable to fetch survey responses: " . $e->getMessage());
        }

        // Generate field map for questions
        $this->transformerOutputSurveyResponses->fieldMap =
            createFieldMap($this->survey, 'full', false, false);

        // Transform responses
        $transformedResponses = $this->transformerOutputSurveyResponses->transform(
            $surveyResponses,
            ['survey' => $this->survey]
        );

        // Get question field map
        $surveyQuestions = $this->getQuestionFieldMap();

        // Map responses to questions
        $mappedData = $this->mapResponsesToQuestions($transformedResponses, $surveyQuestions);

        return [
            'responses' => $mappedData,
            'surveyQuestions' => $surveyQuestions
        ];
    }

    /**
     * Get the question field map.
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
                            'gid' => $item['gid'],
                            'qid' => $item['qid'],
                            'aid' => $item['aid'] ?? null,
                            'sqid' => $item['sqid'] ?? null,
                            'scaleid' => $item['scale_id'] ?? null,
                        ];
                    }
                    return null;
                },
                $fieldMap
            )
        );
    }

    /**
     * Map survey responses to survey questions.
     *
     * @param array $responses
     * @param array $surveyQuestions
     * @return array
     */
    protected function mapResponsesToQuestions(array $responses, array $surveyQuestions): array
    {
        foreach ($responses as &$response) {
            foreach ($response['answers'] as &$answer) {
                $qid = $answer['key'];
                if (isset($surveyQuestions[$qid])) {
                    $answer = array_merge(
                        $answer,
                        $surveyQuestions[$qid]
                    );
                    $answer['actual_aid'] = $this->getActualAid(
                        $answer['qid'],
                        $answer['scale_id'] ?? $answer['scaleid'] ?? 0,
                        $answer['value'],
                    );
                }
            }
        }
        return $responses;
    }

    /**
     * Get the actual answer ID efficiently using cached answers.
     *
     * @param int $questionID
     * @param int $scaleId
     * @param string $value
     * @return int|null
     */
    protected function getActualAid($questionID, $scaleId, $value)
    {
        $allAnswers = $this->getAllSurveyAnswers();
        return $allAnswers[$questionID][$scaleId][$value] ?? null;
    }

    /**
     * Get all answers for the survey questions and cache them.
     *
     * @return array Answers indexed by qid, scale_id, and code
     */
    protected function getAllSurveyAnswers()
    {
        static $answersCache = [];
        $surveyId = $this->survey->sid;

        if (!isset($answersCache[$surveyId])) {
            $questions = $this->survey->questions;
            $questionIds = array_map(function (\Question $q): int {
                return $q->qid;
            }, $questions);

            if (empty($questionIds)) {
                $answersCache[$surveyId] = [];
                return $answersCache[$surveyId];
            }

            $answers = $this->answerModel->findAll(
                'qid IN (' . implode(',', $questionIds) . ')'
            );

            $answersCache[$surveyId] = [];
            foreach ($answers as $answer) {
                $answersCache[$surveyId][$answer->qid][$answer->scale_id][$answer->code] = $answer->aid;
            }
        }

        return $answersCache[$surveyId];
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
            case 'xlsx':
                return new XlsxExportWriter();
            case 'xls':
                return new XlsExportWriter();
            case 'pdf':
                return new PdfExportWriter();
            case 'html':
                return new HtmlExportWriter();
            default:
                throw new InvalidArgumentException("Unsupported export type: $exportType");
        }
    }
}
