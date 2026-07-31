<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
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
 * Returns the responses of a survey, one entry per response with its answers.
 *
 * Filtering and sorting go through the generic {@see FilterPatcher}, whose
 * filter keys are validated against the survey field map so any filter method
 * (equal, contain, multi-select, …) can target nested question/subquestion
 * columns. A `fields` param additionally restricts the SELECT to a caller-chosen
 * subset of response columns; when it is omitted every column is returned.
 */
class SurveyResponses implements CommandInterface
{
    use AuthPermissionTrait;
    use ResponseMappingTrait;
    use SurveyRequestTrait;

    /**
     * Response columns always loaded regardless of field selection, because
     * the transformed output (id, completed flag, dates, language, token, …)
     * depends on them. Intersected with the survey's real columns before use.
     */
    private const FIXED_OUTPUT_COLUMNS = [
        'id',
        'submitdate',
        'startdate',
        'datestamp',
        'startlanguage',
        'lastpage',
        'seed',
        'token',
        'ipaddr',
        'refurl',
    ];

    protected Survey $survey;
    protected Permission $permission;
    protected ResponseFactory $responseFactory;
    protected FilterPatcher $responseFilterPatcher;
    protected TransformerOutputSurveyResponses $transformerOutputSurveyResponses;
    protected SurveyAnswerCache $answerCache;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param Permission $permission
     * @param FilterPatcher $responseFilterPatcher
     * @param ResponseFactory $responseFactory
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     * @param SurveyAnswerCache $answerCache
     */
    public function __construct(
        Survey $survey,
        Permission $permission,
        FilterPatcher $responseFilterPatcher,
        ResponseFactory $responseFactory,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses,
        SurveyAnswerCache $answerCache
    ) {
        $this->survey = $survey;
        $this->permission = $permission;
        $this->responseFactory = $responseFactory;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
        $this->answerCache = $answerCache;
    }

    /**
     * Run survey responses command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            return $this->responseFactory->makeSuccess($this->process($request));
        } catch (TransformerException $e) {
            return $this->responseFactory->makeError('Invalid key sent');
        } catch (\InvalidArgumentException $e) {
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

        [$criteria, $sort] = $this->buildCriteria($request);

        $pagination = $this->buildPagination($request);
        $dataProvider = new \LSCActiveDataProvider(
            $model,
            array(
                'sort' => $sort,
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
            'responses' => $responses,
            'surveyQuestions' => $surveyQuestions,
            '_meta' => [
                'pagination' => [
                    'pageSize' => $pageSize,
                    'currentPage' => $pagination['currentPage'],
                    'totalItems' => $totalItems,
                    'totalPages' => (int) ceil($totalItems / $pageSize),
                ],
                'filters' => $request->getData('filters', []),
                'sort' => $request->getData('sort', []),
            ],
        ];
    }

    /**
     * Build the criteria and sort from the generic filter/sort engine. The
     * survey's real columns are passed so filter keys can be validated against
     * them (and nested question/subquestion columns filtered at query level).
     *
     * @param Request $request
     * @return array{0: \LSDbCriteria, 1: \CSort}
     */
    protected function buildCriteria(Request $request): array
    {
        $searchParams = [];
        $searchParams['filters'] = $request->getData('filters', null);
        $searchParams['sort'] = $request->getData('sort', null);
        $dataMap = $this->transformerOutputSurveyResponses->getDataMap();
        $validColumns = array_keys($this->transformerOutputSurveyResponses->fieldMap);
        $sort = new \CSort();
        $criteria = new \LSDbCriteria();
        $this->responseFilterPatcher->apply(
            $searchParams,
            $criteria,
            $sort,
            $dataMap,
            $validColumns
        );
        $this->applyFieldSelection($criteria, $request);

        return [$criteria, $sort];
    }

    /**
     * Restrict the SELECT to a caller-provided subset of response columns.
     *
     * Only columns that exist in the survey's field map are honoured; the
     * fixed system columns the transformed output relies on (id, dates,
     * language, token, …) are always kept so selecting question columns never
     * strips the metadata each response needs. When no (valid) field is
     * provided every column is returned.
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

        $fixed = array_intersect(self::FIXED_OUTPUT_COLUMNS, $validColumns);
        $criteria->select = array_values(
            array_unique(array_merge($fixed, $selected))
        );
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
