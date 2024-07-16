<?php

namespace LimeSurvey\Api\Command\V1;

use SurveysGroups;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveyGroup;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthTokenSimple;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyGroupList implements CommandInterface
{
    use AuthPermissionTrait;

    protected SurveysGroups $surveyGroup;
    protected AuthTokenSimple $auth;
    protected TransformerOutputSurveyGroup $transformerOutputSurveyGroup;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param SurveysGroups $surveyGroup
     * @param AuthTokenSimple $auth
     * @param TransformerOutputSurveyGroup $transformerOutputSurveyGroup
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        SurveysGroups $surveyGroup,
        AuthTokenSimple $auth,
        TransformerOutputSurveyGroup $transformerOutputSurveyGroup,
        ResponseFactory $responseFactory
    ) {
        $this->surveyGroup = $surveyGroup;
        $this->auth = $auth;
        $this->transformerOutputSurveyGroup = $transformerOutputSurveyGroup;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run surveys group list command
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $authToken = (string) $request->getData('authToken');

        if (
            !$this->auth
                ->isAuthenticated($authToken)
        ) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        $dataProvider = $this->surveyGroup->search();

        $data = $this->transformerOutputSurveyGroup
                ->transformAll($dataProvider->getData());

        return $this->responseFactory
            ->makeSuccess(['surveyGroups' => $data]);
    }
}
