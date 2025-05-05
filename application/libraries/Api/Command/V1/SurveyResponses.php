<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\FilterPatcher;
use Survey;
use LimeSurvey\Api\Command\{CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory,
    V1\Transformer\Output\TransformerOutputQuestion,
    V1\Transformer\Output\TransformerOutputQuestionGroup};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;
use LimeSurvey\Libraries\Api\Command\V1\Transformer\Output\TransformerOutputSurveyResponses;

class SurveyResponses implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected ResponseFactory $responseFactory;
    protected FilterPatcher $responseFilterPatcher;
    protected TransformerOutputSurveyResponses $transformerOutputSurveyResponses;
    private TransformerOutputQuestion $transformerQuestion;
    private TransformerOutputQuestionGroup $transformerQuestionGroup;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param FilterPatcher $responseFilterPatcher
     * @param ResponseFactory $responseFactory
     * @param TransformerOutputQuestion $transformerOutputQuestion
     * @param TransformerOutputQuestionGroup $transformerOutputQuestionGroup
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     */
    public function __construct(
        Survey $survey,
        FilterPatcher $responseFilterPatcher,
        ResponseFactory $responseFactory,
        TransformerOutputQuestion $transformerOutputQuestion,
        TransformerOutputQuestionGroup $transformerOutputQuestionGroup,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses
    ) {
        $this->survey = $survey;
        $this->responseFactory = $responseFactory;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerQuestion = $transformerOutputQuestion;
        $this->transformerQuestionGroup = $transformerOutputQuestionGroup;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
    }

    /**
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     * @throws TransformerException
     */
    public function run(Request $request)
    {
        $this->getSurvey($request);
        $model = $this->getSurveyDynamicModel($request);
        $questionGroups = $this->getSurveyGroup();
        [$criteria, $sort] = $this->buildCriteria($request);
        $pagination = $this->buildPagination($request);

        $dataProvider = new \LSCActiveDataProvider($model, array(
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => $pagination,
        ));

        $data = $this->transformerOutputSurveyResponses->transform($dataProvider);
        $data['surveyGroup'] = $this->transformQuestionGroups($questionGroups);
        $data['_meta'] = [
            'pagination' => [
                'pageSize' => $pagination['pageSize'],
                'currentPage' => $pagination['currentPage'],
                'totalItems' => $dataProvider->getTotalItemCount(),
                'totalPages' => ceil($dataProvider->getTotalItemCount() / $pagination['pageSize']),
            ],
            'filters' => $request->getData('search', []),
            'sort' => $request->getData('sort', []),
        ];

        return $this->responseFactory
            ->makeSuccess(['responses' => $data]);
    }

    public function transformQuestionGroups($data, $options = []): array
    {
        $options = $options ?? [];

        $survey['questionGroups'] = $this->transformerQuestionGroup->transformAll(
            $data,
            $options
        );

        $groupLookup = $this->createCollectionLookup(
            'gid',
            $survey['questionGroups']
        );

        foreach ($data as $questionGroupModel) {
            $group = &$groupLookup[$questionGroupModel->gid];

            $group['questions'] = $this->transformerQuestion->transformAll(
                $questionGroupModel->questions,
                $options
            );
        }

        return $survey;
    }

    private function createCollectionLookup($key, &$entityArray): array
    {
        $output = [];
        foreach ($entityArray as &$entity) {
            if (is_array($entity) && isset($entity[$key])) {
                $output[$entity[$key]] = &$entity;
            }
        }
        return $output;
    }

    private function getSurvey($request): void
    {
        $this->survey = $this->survey->findByPk($this->getSurveyId($request));
    }

    private function getSurveyGroup()
    {
        return $this->survey->groups ?? [];
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
        $searchParams = $request->getData('search', null);

        $sort = new \CSort();
        $criteria = new \LSDbCriteria();


        if ($searchParams) {
            $this->responseFilterPatcher->apply($searchParams, $criteria, $sort);
        }
        return [$criteria, $sort];
    }
    private function buildPagination(Request $request)
    {
        return $request->getData('pagination', [
            'pageSize' => 15,
            'currentPage' => 0,
        ]);
    }
}
