<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\ResponseMappingTrait;
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

            return $this->responseFactory->makeSuccess(['answers' => $data]);
        } catch (TransformerException $e) {
            return $this->responseFactory->makeError('Invalid key sent');
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

        return [
            'answers' => $this->flattenAnswers($responses),
            'surveyQuestions' => $surveyQuestions,
            '_meta' => [
                'pagination' => [
                    'pageSize' => $pagination['pageSize'],
                    'currentPage' => $pagination['currentPage'],
                    'totalItems' => $totalItems,
                    'totalPages' => (int) ceil(
                        $totalItems / ($pagination['pageSize'] ?? 1)
                    ),
                ],
            ],
        ];
    }

    protected function buildPagination(Request $request): array
    {
        $pagination = $request->getData('page');
        $paginationDefault = [
            'pageSize' => 15,
            'currentPage' => 0,
        ];

        if ($pagination) {
            $paginationRequiredKeys = ['currentPage', 'pageSize'];

            if (
                isset($pagination['pageSize'])
                && (int)$pagination['pageSize'] == 0
            ) {
                $pagination['pageSize'] = $paginationDefault['pageSize'];
            }

            if (
                !empty(
                    array_diff_key(
                        array_flip($paginationRequiredKeys),
                        $pagination
                    )
                )
            ) {
                return array_merge($paginationDefault, $pagination);
            }

            return $pagination;
        }

        return $paginationDefault;
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
            foreach ($response['answers'] ?? [] as $answer) {
                $answer['responseId'] = $response['id'] ?? null;
                $answers[] = $answer;
            }
        }

        return $answers;
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

        // Always keep the primary key so answers remain identifiable.
        $criteria->select = array_values(array_unique(array_merge(['id'], $selected)));
    }

    protected function getSurvey(Request $request): void
    {
        $survey = $this->survey->findByPk($this->getSurveyId($request));
        if ($survey === null) {
            throw new \RuntimeException('Survey not found');
        }
        $this->survey = $survey;
    }

    protected function getSurveyId(Request $request): string
    {
        $surveyId = (string) $request->getData('_id');
        if (!is_numeric($surveyId)) {
            throw new \InvalidArgumentException("Invalid survey ID");
        }

        return $surveyId;
    }

    protected function getSurveyDynamicModel(Request $request): \SurveyDynamic
    {
        return \SurveyDynamic::model($this->getSurveyId($request));
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
