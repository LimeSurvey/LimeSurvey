<?php

namespace LimeSurvey\Libraries\Api\Command\V1;

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

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param TransformerOutputSurveyResponses $transformerOutputSurveyDetail
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Survey $survey,
        TransformerOutputSurveyResponses $transformerOutputSurveyDetail,
        ResponseFactory $responseFactory
    ) {
        $this->survey = $survey;
        $this->transformerOutputSurveyDetail = $transformerOutputSurveyDetail;
        $this->responseFactory = $responseFactory;
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
        $responses = \SurveyDynamic::model($surveyId);


        $responses = $this->transformerOutputSurveyDetail
            ->transform($responses);


        return $this->responseFactory
            ->makeSuccess(['responses' => $responses]);
    }
}
