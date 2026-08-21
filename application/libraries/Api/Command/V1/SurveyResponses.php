<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use CDbException;
use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
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

class SurveyResponses implements CommandInterface
{
    use AuthPermissionTrait;
    use ResponseMappingTrait;

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
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        try {
            $data = $this->process($request);

            return $this->responseFactory->makeSuccess(['responses' => $data]);
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
            // Since questions keys are column, if there's an invalid key sent,
            // an exception will be thrown which will result in an error 500.
            throw new TransformerException();
        }

        $this->transformerOutputSurveyResponses->fieldMap =
            createFieldMap($this->survey, 'full', true, false);

        $data = [];
        $data['responses'] = $this->transformerOutputSurveyResponses->transform(
            $surveyResponses,
            ['survey' => $this->survey]
        );
        $data['surveyQuestions'] = $this->getQuestionFieldMap();
        $data['_meta'] = [
            'pagination' => [
                'pageSize' => $pagination['pageSize'],
                'currentPage' => $pagination['currentPage'],
                'totalItems' => $dataProvider->getTotalItemCount(),
                'totalPages' => ceil(
                    $dataProvider->getTotalItemCount()
                    / ($pagination['pageSize'] ?? 1)
                )
            ],
            'filters' => $request->getData('filters', []),
            'sort' => $request->getData('sort', []),
        ];

        $this->answerCache->load((int) $surveyId, $this->survey->language);
        $data['responses'] = $this->mapResponsesToQuestions(
            $data['responses'],
            $data['surveyQuestions']
        );

        $data['timingFields'] = $this->appendTimingData($data['responses']);

        return $data;
    }

    /**
     * Adds timing values to the responses and returns the metadata needed to
     * build timing columns in API clients.
     *
     * Timings are stored in a separate table, so fetching them after response
     * pagination keeps the response count and pagination unchanged.
     *
     * @param array $responses
     * @return array
     */
    protected function appendTimingData(array &$responses): array
    {
        if (!$this->survey->hasTimingsTable) {
            return [];
        }

        $timingFields = $this->getTimingFields();
        $timingsByResponse = $this->getTimingsByResponse(
            array_column($responses, 'id'),
            array_column($timingFields, 'fieldname')
        );

        $responses = array_map(
            static function (array $response) use ($timingsByResponse): array {
                $responseId = (int)($response['id'] ?? 0);
                $response['timings'] = $timingsByResponse[$responseId] ?? [];
                return $response;
            },
            $responses
        );

        return $timingFields;
    }

    /**
     * @return array
     */
    private function getTimingFields(): array
    {
        return array_values(createTimingsFieldMap(
            $this->survey->sid,
            'full',
            false,
            false,
            $this->survey->language
        ));
    }

    /**
     * @param array $responseIds
     * @param array $fieldNames
     * @return array
     */
    private function getTimingsByResponse(array $responseIds, array $fieldNames): array
    {
        if ($responseIds === []) {
            return [];
        }

        $model = \SurveyTimingDynamic::model($this->survey->sid);
        $fieldNames = array_values(array_intersect(
            $fieldNames,
            $model->getTableSchema()->getColumnNames()
        ));

        if ($fieldNames === []) {
            return [];
        }

        $criteria = new \CDbCriteria();
        $criteria->addInCondition('id', $responseIds);
        $records = $model->findAll($criteria);
        $timings = [];

        foreach ($records as $record) {
            $timings[(int)$record->id] = array_map(
                static fn($value) => is_numeric($value) ? (float)$value : null,
                $record->getAttributes($fieldNames)
            );
        }

        return $timings;
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
        $surveyId = (string)$request->getData('_id');
        if (!is_numeric($surveyId)) {
            throw new \InvalidArgumentException("Invalid survey ID");
        }

        return $surveyId;
    }

    protected function getSurveyDynamicModel(Request $request): \SurveyDynamic
    {
        return \SurveyDynamic::model($this->getSurveyId($request));
    }

    protected function buildCriteria(Request $request): array
    {
        $searchParams = [];
        $searchParams['filters'] = $request->getData('filters', null);
        $searchParams['sort'] = $request->getData('sort', null);
        $dataMap = $this->transformerOutputSurveyResponses->getDataMap();
        $sort = new \CSort();
        $criteria = new \LSDbCriteria();
        $this->responseFilterPatcher->apply(
            $searchParams,
            $criteria,
            $sort,
            $dataMap
        );

        return [$criteria, $sort];
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
}
