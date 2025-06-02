<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use Survey;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;

class SurveyResponses implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected ResponseFactory $responseFactory;
    protected FilterPatcher $responseFilterPatcher;
    protected TransformerOutputSurveyResponses $transformerOutputSurveyResponses;

    /**
     * Constructor
     *
     * @param Survey                           $survey
     * @param FilterPatcher                    $responseFilterPatcher
     * @param ResponseFactory                  $responseFactory
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     */
    public function __construct(
        Survey $survey,
        FilterPatcher $responseFilterPatcher,
        ResponseFactory $responseFactory,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses
    ) {
        $this->survey = $survey;
        $this->responseFactory = $responseFactory;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
    }

    /**
     * Run survey detail command
     *
     * @param  Request $request
     * @return Response
     * @throws TransformerException
     */
    public function run(Request $request)
    {
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

        $data = [];
        $data['responses'] = $this->transformerOutputSurveyResponses->transform($dataProvider);
        $data['surveyQuestions'] = $this->getQuestionFieldMap();
        $data['_meta'] = [
            'pagination' => [
                'pageSize' => (int) $pagination['pageSize'],
                'currentPage' => (int) $pagination['currentPage'],
                'totalItems' => $dataProvider->getTotalItemCount(),
                'totalPages' => (int) ceil($dataProvider->getTotalItemCount() / $pagination['pageSize'] ?? 1)
            ],
            'filters' => $request->getData('filters', []),
            'sort' => $request->getData('sort', []),
        ];
        $data = $this->mapResponsesToQuestions($data);

        return $this->responseFactory
            ->makeSuccess(['responses' => $data]);
    }

    /**
     * Maps survey responses to survey questions.
     *
     * @param  array $data The survey responses and questions data.
     * @return array
     */
    public function mapResponsesToQuestions($data)
    {
        foreach ($data['responses'] as &$response) {
            foreach ($response['answers'] as &$answer) {
                $qid = $answer['key'];
                if (isset($data["surveyQuestions"][$qid])) {
                    $answer = array_merge($answer, $data["surveyQuestions"][$qid]);
                }
            }
        }
        return $data;
    }


    /**
     * @param  mixed      $question
     * @param  mixed|null $subquestion
     * @return string|array
     */
    private function getQuestionFieldMap()
    {
        //This function generates an array containing the fieldcode, and matching data in the same order as the responses table
        $fieldMap = createFieldMap($this->survey, 'short', false, false);

        return array_filter(
            array_map(
                function ($item) {
                    if (!empty($item['qid'])) {
                        return [
                        'gid' => $item['gid'],
                        'qid' => $item['qid'],
                        'aid' => $item['aid'] ?? null,
                        'sqid' => $item['sqid'] ?? null,
                        ];
                    }
                },
                $fieldMap
            )
        );
    }


    private function getSurvey(Request $request): void
    {
        $survey = $this->survey->findByPk($this->getSurveyId($request));
        if ($survey === null) {
            throw new \RuntimeException('Survey not found');
        }
        $this->survey = $survey;
    }


    private function getSurveyId(Request $request): string
    {
        $surveyId = (string) $request->getData('_id');
        if (!is_numeric($surveyId)) {
            throw new \InvalidArgumentException("Invalid survey ID");
        }

        return $surveyId;
    }

    private function getSurveyDynamicModel(Request $request): \SurveyDynamic
    {
        return \SurveyDynamic::model($this->getSurveyId($request));
    }

    private function buildCriteria(Request $request): array
    {
        $searchParams = [];
        $searchParams['filters'] = $request->getData('filters', null);
        $searchParams['sort'] = $request->getData('sort', null);
        $dataMap = $this->transformerOutputSurveyResponses->getDataMap();

        $sort = new \CSort();
        $criteria = new \LSDbCriteria();
        $this->responseFilterPatcher->apply($searchParams, $criteria, $sort, $dataMap);

        return [$criteria, $sort];
    }

    private function buildPagination(Request $request): array
    {
        $pagination = $request->getData('page');
        $paginationDefault = [
            'pageSize' => 15,
            'currentPage' => 0,
        ];

        if ($pagination) {
            $paginationRequiredKeys = ['currentPage', 'pageSize'];

            if (isset($pagination['pageSize']) && (int) $pagination['pageSize'] == 0) {
                $pagination['pageSize'] = $paginationDefault['pageSize'];
            }

            if (!empty(array_diff_key(array_flip($paginationRequiredKeys), $pagination))) {
                return array_merge($paginationDefault, $pagination);
            }

            return $pagination;
        }

        return $paginationDefault;
    }
}
