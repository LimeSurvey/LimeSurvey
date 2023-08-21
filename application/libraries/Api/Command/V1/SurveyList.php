<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyList implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected AuthSession $authSession;
    protected TransformerOutputSurvey $transformerOutputSurvey;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param Survey $survey
     * @param AuthSession $authSession
     * @param TransformerOutputSurvey $transformerOutputSurvey
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Survey $survey,
        AuthSession $authSession,
        TransformerOutputSurvey $transformerOutputSurvey,
        ResponseFactory $responseFactory
    ) {
        $this->survey = $survey;
        $this->authSession = $authSession;
        $this->transformerOutputSurvey = $transformerOutputSurvey;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run survey list command
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

        $dataProvider = $this->survey
            ->with('defaultlanguage')
            ->search();

        $data = $this->transformerOutputSurvey
            ->transformAll($dataProvider->getData());

        return $this->responseFactory
            ->makeSuccess(['surveys' => $data]);
    }
}
