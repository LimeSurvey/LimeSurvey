<?php

namespace LimeSurvey\Api\Command\V1;

use Survey;
use LimeSurvey\Api\Command\V1\Transformer\Output\TransformerOutputSurvey;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Auth\CommandAuthInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Api\Command\Mixin\Auth\AuthPermissionTrait;

class SurveyList implements CommandInterface
{
    use AuthPermissionTrait;

    protected Survey $survey;
    protected CommandAuthInterface $commandAuth;
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
        CommandAuthInterface $commandAuth,
        Survey $survey,
        TransformerOutputSurvey $transformerOutputSurvey,
        ResponseFactory $responseFactory
    ) {
        $this->commandAuth = $commandAuth;
        $this->survey = $survey;
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
        if (
            !$this->commandAuth
            || !$this->commandAuth
                ->isAuthenticated($request)
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
