<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\ResponseMappingTrait;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\SurveyRequestTrait;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\SurveyAnswerCache;
use Permission;
use Survey;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;

/**
 * Returns the answers of a survey.
 *
 * Mirrors {@see SurveyResponses} but is focused on answers: it accepts a survey
 * id, an optional response id, a language and an optional list of selected
 * fields. When no field is provided every answer of the matched response(s) is
 * returned; when no response id is provided answers of all responses are
 * returned.
 */
class SurveyResponseAnswer implements CommandInterface
{
    use AuthPermissionTrait;
    use ResponseMappingTrait;
    use SurveyRequestTrait;

    protected Survey $survey;
    protected Permission $permission;
    protected ResponseFactory $responseFactory;
    protected TransformerOutputSurveyResponses $transformerOutputSurveyResponses;
    protected SurveyAnswerCache $answerCache;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param Permission $permission
     * @param ResponseFactory $responseFactory
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     * @param SurveyAnswerCache $answerCache
     */
    public function __construct(
        Survey $survey,
        Permission $permission,
        ResponseFactory $responseFactory,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses,
        SurveyAnswerCache $answerCache
    ) {
        $this->survey = $survey;
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
        $this->answerCache = $answerCache;
    }

    /**
     * Run survey response answer command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            $data = $this->process($request);

            return $this->responseFactory->makeSuccess($data);
        } catch (TransformerException $e) {
            return $this->responseFactory->makeError('Invalid key sent');
        } catch (\InvalidArgumentException $e) {
            // Invalid survey id / response id from the request validation.
            return $this->responseFactory->makeErrorBadRequest($e->getMessage());
        } catch (PermissionDeniedException $e) {
            return $this->responseFactory->makeErrorUnauthorised();
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws TransformerException
     */
    public function process(Request $request): array
    {
        $surveyId = $this->getSurveyId($request);
        if (!$this->permission->hasSurveyPermission($surveyId, 'responses')) {
            throw new PermissionDeniedException();
        }

        $this->getSurvey($request);
        $model = $this->getSurveyDynamicModel($request);
        $language = $this->getLanguage($request);

        $this->transformerOutputSurveyResponses->fieldMap =
            createFieldMap($this->survey, 'full', true, false, $language);

        $criteria = new \LSDbCriteria();
        $this->applyResponseFilter($criteria, $request);
        $this->applyAnswerFilter($criteria, $request);
        $this->applyFieldSelection($criteria, $request);
        $this->applyQuestionFilter($criteria, $request);
        $this->applyAnswerValueFilter($criteria, $request);
        $criteria->order = 'id DESC';

        $pagination = $this->buildPagination($request);
        $dataProvider = new \LSCActiveDataProvider(
            $model,
            array(
                'sort' => false,
                'criteria' => $criteria,
                'pagination' => $pagination,
            )
        );

        try {
            $surveyResponses = $dataProvider->getData();
        } catch (CDbException $e) {
            // Question keys map to columns, so an invalid field would raise an
            // exception (and otherwise a 500). Surface it as an invalid key.
            throw new TransformerException();
        }

        $responses = $this->transformerOutputSurveyResponses->transform(
            $surveyResponses,
            ['survey' => $this->survey]
        );

        $surveyQuestions = $this->getQuestionFieldMap();

        $this->answerCache->load((int) $surveyId, $language);
        $responses = $this->mapResponsesToQuestions($responses, $surveyQuestions);

        $totalItems = $dataProvider->getTotalItemCount();
        $pageSize = max(1, $pagination['pageSize'] ?? 1);

        return [
            'answers' => $this->flattenAnswers($responses),
            'surveyQuestions' => $surveyQuestions,
            '_meta' => [
                'pagination' => [
                    'pageSize' => $pageSize,
                    'currentPage' => $pagination['currentPage'],
                    'totalItems' => $totalItems,
                    'totalPages' => (int) ceil($totalItems / $pageSize),
                ],
            ],
        ];
    }

    /**
     * Flatten the per-response answers into a single list, tagging each answer
     * with the response it belongs to.
     *
     * @param array $responses
     * @return array<array>
     */
    protected function flattenAnswers(array $responses): array
    {
        $answers = [];
        foreach ($responses as $response) {
            $date = $response['submitDate']
                ?? $response['dateLastAction']
                ?? $response['startDate']
                ?? null;
            foreach ($response['answers'] ?? [] as $answer) {
                $answer['responseId'] = $response['id'] ?? null;
                $answer['date'] = $date;
                $answers[] = $answer;
            }
        }

        return $answers;
    }

    /**
     * The responses table's date columns that exist for this survey, validated
     * against the field map (so datestamp-less surveys, which have neither
     * `startdate` nor `datestamp`, are handled). flattenAnswers() timestamps
     * every answer from these columns, so they must survive any narrowed select.
     *
     * @return string[]
     */
    protected function dateColumns(): array
    {
        $dateColumns = ['submitdate', 'startdate', 'datestamp'];
        $validColumns = array_keys($this->transformerOutputSurveyResponses->fieldMap);

        return array_values(array_intersect($dateColumns, $validColumns));
    }

    /**
     * Restrict the query to responses whose answer column equals a given value
     * (e.g. only responses that picked a specific answer option). The column is
     * validated against the field map so only real response columns are used.
     *
     * @param \LSDbCriteria $criteria
     * @param Request $request
     */
    protected function applyAnswerFilter(\LSDbCriteria $criteria, Request $request): void
    {
        $field = $request->getData('answerField');
        $value = $request->getData('answerValue');
        if (!is_string($field) || $field === '' || $value === null || $value === '') {
            return;
        }

        $validColumns = array_keys($this->transformerOutputSurveyResponses->fieldMap);
        if (!in_array($field, $validColumns, true)) {
            return;
        }

        $criteria->compare($field, $value, false);
    }

    /**
     * Restrict the query to a single response when a response id is provided.
     *
     * @param \LSDbCriteria $criteria
     * @param Request $request
     */
    protected function applyResponseFilter(\LSDbCriteria $criteria, Request $request): void
    {
        $responseId = $request->getData('responseId');
        if ($responseId === null || $responseId === '') {
            return;
        }
        if (!is_numeric($responseId)) {
            throw new \InvalidArgumentException("Invalid response ID");
        }

        $criteria->compare('id', (int) $responseId);
    }

    /**
     * Restrict the query to a caller-provided subset of answer columns (the
     * dynamic question columns of the responses table); when no (valid) field
     * is provided every column is returned.
     *
     * @param \LSDbCriteria $criteria
     * @param Request $request
     */
    protected function applyFieldSelection(\LSDbCriteria $criteria, Request $request): void
    {
        $fields = $request->getData('fields');
        if (!is_array($fields) || empty($fields)) {
            return;
        }

        $validColumns = array_keys($this->transformerOutputSurveyResponses->fieldMap);
        $selected = array_values(array_intersect($fields, $validColumns));
        if (empty($selected)) {
            return;
        }

        // Always keep the primary key and date columns so answers remain
        // identifiable and timestamped (flattenAnswers reads the date columns).
        $criteria->select = array_values(array_unique(
            array_merge(['id'], $this->dateColumns(), $selected)
        ));
    }

    /**
     * Restrict the selected columns to a single question's fields, so the
     * endpoint only returns that question's answers (every field of a question
     * shares the question code as its field-map `title`). No-op when no
     * questionCode is given.
     *
     * @param \LSDbCriteria $criteria
     * @param Request $request
     */
    protected function applyQuestionFilter(\LSDbCriteria $criteria, Request $request): void
    {
        $questionCode = $request->getData('questionCode');
        if (!is_string($questionCode) || $questionCode === '') {
            return;
        }

        $questionColumns = [];
        foreach ($this->transformerOutputSurveyResponses->fieldMap as $column => $meta) {
            if (($meta['title'] ?? null) === $questionCode) {
                $questionColumns[] = $column;
            }
        }

        // Only narrow the selection when the question actually maps to columns.
        if (empty($questionColumns)) {
            return;
        }

        // Keep the primary key and date columns alongside the question columns,
        // so answers remain identifiable and timestamped (flattenAnswers reads
        // the date columns).
        $criteria->select = array_values(array_unique(
            array_merge(['id'], $this->dateColumns(), $questionColumns)
        ));
    }

    /**
     * Restrict responses to those that selected a given answer of a question,
     * resolving the response column from the question's field map. Lets the
     * comments view page server-side through a single answer's comments. No-op
     * without questionCode + answerValue, or when an explicit answerField is
     * already provided.
     *
     * @param \LSDbCriteria $criteria
     * @param Request $request
     */
    protected function applyAnswerValueFilter(\LSDbCriteria $criteria, Request $request): void
    {
        $questionCode = $request->getData('questionCode');
        $answerValue = $request->getData('answerValue');
        if (
            !is_string($questionCode) || $questionCode === ''
            || $answerValue === null || $answerValue === ''
            || $request->getData('answerField')
        ) {
            return;
        }

        // The question's answer columns (excluding its comment columns).
        $answerColumns = [];
        foreach ($this->transformerOutputSurveyResponses->fieldMap as $column => $meta) {
            if (($meta['title'] ?? null) !== $questionCode) {
                continue;
            }
            $aid = (string)($meta['aid'] ?? '');
            if (str_ends_with($aid, 'comment')) {
                continue;
            }
            $answerColumns[$column] = $aid;
        }

        if (count($answerColumns) === 1) {
            // Single selection (e.g. list-with-comment): the column stores the
            // chosen answer code.
            $criteria->compare(array_key_first($answerColumns), (string)$answerValue, false);
            return;
        }

        // Sub-question selection (e.g. multiple-choice-with-comments): the
        // chosen sub-question column is "Y" when selected.
        foreach ($answerColumns as $column => $aid) {
            if ($aid === (string)$answerValue) {
                $criteria->compare($column, 'Y', false);
                return;
            }
        }
    }

    /**
     * Resolve the requested language, falling back to the survey base language
     * when none (or an unknown one) is requested.
     *
     * @param Request $request
     * @return string
     */
    protected function getLanguage(Request $request): string
    {
        $language = (string) $request->getData('language', '');
        $availableLanguages = $this->survey->getAllLanguages();
        if ($language === '' || !in_array($language, $availableLanguages, true)) {
            return $this->survey->language;
        }

        return $language;
    }
}
