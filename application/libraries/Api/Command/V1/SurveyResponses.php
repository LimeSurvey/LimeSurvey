<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

use DI\FactoryInterface;
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
    protected TransformerOutputSurveyResponses $transformerOutputSurveyDetail;
    protected ResponseFactory $responseFactory;
    protected FilterPatcher $responseFilterPatcher;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyDetail
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        FactoryInterface $diFactory,
        TransformerOutputSurveyResponses $transformerOutputSurveyDetail,
        FilterPatcher $responseFilterPatcher,
        ResponseFactory $responseFactory
    ) {
        $this->diFactory = $diFactory;
        $this->transformerOutputSurveyDetail = $transformerOutputSurveyDetail;
        $this->responseFactory = $responseFactory;
        $this->responseFilterPatcher = $responseFilterPatcher;
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
        $searchParams = (string) $request->getData('search', null);
        $model = \SurveyDynamic::model($surveyId);

        $criteria = new \LSDbCriteria();
        $sort     = new \CSort();


//        $searchParams = [
//            "sort" => [
//                "id" => 'asc',
//            ],
//            "search" => [
//                [
//                    "survey" => "132241",
//                    "group" => "130",
//                    "question" => "2110",
//                    "operator" => "LIKE",
//                    "value" => "Question#",
//                    "type" => "text",
//                ],
//                [
//                    "survey" => "132241",
//                    "group" => "130",
//                    "question" => "2111",
//                    "operator" => "EQUAL",
//                    "value" => "A001",
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
            'pagination' => [
                'pageSize' => 3,
                'currentPage' => 1,
            ],
        ));

        return $this->responseFactory
            ->makeSuccess(['responses' => [$request->test(), $dataProvider->getData()]]);
    }
}
