<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\{CommandInterface,
    Mixin\Auth\AuthPermissionTrait,
    Request\Request,
    Response\Response,
    Response\ResponseFactory};
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Gemini;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\Command;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\LimeSurveyLLM;

class LLM implements CommandInterface
{
    use AuthPermissionTrait;

    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ResponseFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run session key release command.
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $prompt = (string) $request->getData('command');
        $operation = (string) $request->getData('operation');

        if (!empty($prompt)) {
            $cmd = new Command($operation, $prompt);

            $llm = new LimeSurveyLLM($cmd);
            $response = $llm->run();

            return $this->responseFactory
                ->makeSuccess(json_encode($response));
        }

        return $this->responseFactory
            ->makeError('Invalid request parameters');
    }
}
