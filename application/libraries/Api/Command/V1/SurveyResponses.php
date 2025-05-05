<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use DI\FactoryInterface;
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
    protected Survey $diFactory;
    protected ResponseFactory $responseFactory;
    protected FilterPatcher $responseFilterPatcher;
    protected TransformerOutputSurveyResponses $transformerOutputSurveyResponses;
    private TransformerOutputQuestion $transformerQuestion;
    /**
     * Constructor
     *
     * @param Survey $survey
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyResponses
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Survey $survey,
        TransformerOutputSurveyResponses $transformerOutputSurveyResponses,
        FilterPatcher $responseFilterPatcher,
        ResponseFactory $responseFactory,
        TransformerOutputQuestion $transformerOutputQuestion,
        TransformerOutputQuestionGroup $transformerOutputQuestionGroup
    ) {
        $this->survey = $survey;
        $this->transformerOutputSurveyResponses = $transformerOutputSurveyResponses;
        $this->responseFactory = $responseFactory;
        $this->responseFilterPatcher = $responseFilterPatcher;
        $this->transformerQuestion = $transformerOutputQuestion;
        $this->transformerQuestionGroup = $transformerOutputQuestionGroup;
    }

    /**
     * Run survey detail command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $surveyId = (string) $request->getData('_id');
        $searchParams = $request->getData('search', null);
        $pagination = $request->getData('pagination', [
            'pageSize' => 15,
            'currentPage' => 0,
        ]);

        $model = \SurveyDynamic::model($surveyId);

        $criteria = new \LSDbCriteria();
        $sort     = new \CSort();


//        $searchParams = [
//            "sort" => [
//                "id" => 'asc',
//            ],
//            "search" => [
//                [
//                    "survey" => "198895",
//                    "group" => "135",
//                    "question" => "2142",
//                    "value" => "AO4",
//                    "type" => "option",
//                ],
//            ]
//        ];


        if ($searchParams) {
            $this->responseFilterPatcher->apply($searchParams, $criteria, $sort);
        }

        $dataProvider = new \LSCActiveDataProvider($model, array(
            'sort' => $sort,
            'criteria' => $criteria,
            'pagination' => $pagination,
        ));

        $data = $this->transformerOutputSurveyResponses->transform($dataProvider);

        $questionGroups = $this->survey->findByPk($surveyId)->groups;
        $data['surveyGroup'] = $this->transform_($questionGroups);

        return $this->responseFactory
            ->makeSuccess(['responses' => $data]);
    }

    public function transform_($data, $options = [])
    {
        $options = $options ?? [];

        // transformAll() can apply required entity sort so we must retain the sort order going forward
        // - We use a lookup array later to access entities without needing to know their position in the collection
        $survey['questionGroups'] = $this->transformerQuestionGroup->transformAll(
            $data,
            $options
        );

        // An array of groups indexed by gid for easy look up
        // - helps us to retain sort order when looping over models
        $groupLookup = $this->createCollectionLookup(
            'gid',
            $survey['questionGroups']
        );

        foreach ($data as $questionGroupModel) {
            // Order of groups from the model relation may be different than from the transformed data
            // - so we use the lookup to get a reference to the required entity without needing to
            // - know its position in the output array
            // If we don't assign by reference here the, additions to $group will create a new array
            // - rather than modifying the original array
            $group = &$groupLookup[$questionGroupModel->gid];

            // transformAll() can apply required entity sort so we must retain the sort order going forward
            // - We use a lookup array later to access entities without needing to know their position in the collection
            $group['questions'] = $this->transformerQuestion->transformAll(
                $questionGroupModel->questions,
                $options
            );
        }

        return $survey;
    }

    private function createCollectionLookup($key, &$entityArray)
    {
        $output = [];
        foreach ($entityArray as &$entity) {
            if (is_array($entity) && isset($entity[$key])) {
                $output[$entity[$key]] = &$entity;
            }
        }
        return $output;
    }
}
