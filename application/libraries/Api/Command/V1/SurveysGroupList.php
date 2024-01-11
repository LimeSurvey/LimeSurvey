<?php

namespace LimeSurvey\Api\Command\V1;

use SurveysGroups;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurveysGroup;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveysGroupList implements CommandInterface
{
    use AuthPermissionTrait;

    protected SurveysGroups $surveysGroup;
    protected AuthSession $authSession;
    protected TransformerOutputSurveysGroup $transformerOutputSurveysGroup;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param SurveysGroups $surveysGroup
     * @param AuthSession $authSession
     * @param TransformerOutputSurveysGroup $transformerOutputSurveysGroup
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        SurveysGroups $surveysGroup,
        AuthSession $authSession,
        TransformerOutputSurveysGroup $transformerOutputSurveysGroup,
        ResponseFactory $responseFactory
    ) {
        $this->surveysGroups = $surveysGroup;
        $this->authSession = $authSession;
        $this->transformerOutputSurveysGroup = $transformerOutputSurveysGroup;
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
        $sessionKey = (string) $request->getData('sessionKey');

        if (
            !$this->authSession
                ->checkKey($sessionKey)
        ) {
            return $this->responseFactory
                ->makeErrorUnauthorised();
        }

        // $dataProvider = $this->surveysGroup
        //     ->search();

        $dataProvider = SurveysGroups::model()->search();

        $data = 
            $this->transformerOutputSurveysGroup
                ->transformAll($dataProvider->getData());

        return $this->responseFactory
            ->makeSuccess(['surveysGroups' => $data]);
    }
}
