<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Response\Response,
    Response\ResponseFactory
};
use LimeSurvey\Libraries\Api\Command\V1\LLMs\GoogleGeminiPro;
use LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers\Command;

class LLM implements CommandInterface
{
    protected AuthSession $authSession;
    protected ResponseFactory $responseFactory;

    /**
     * Constructor
     *
     * @param AuthSession $authSession
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
//        AuthSession $authSession,
        ResponseFactory $responseFactory
    ) {
//        $this->authSession = $authSession;
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
        $sessionKey = (string) $request->getData('sessionKey');
//        if (
//            !$this->authSession->checkKey($sessionKey)
//        ) {
//            return $this->responseFactory
//                ->makeErrorUnauthorised();
//        }

        $prompt = (string) $request->getData('command');
        $operation = (string) $request->getData('operation');

        if (!empty($prompt) && !empty($operation)) {
            $cmd = new Command($operation, $prompt);
            $gemini = new GoogleGeminiPro($cmd);
            $response = $gemini->run();

            return $this->responseFactory
                ->makeSuccess(json_encode($response));
        }

        return $this->responseFactory
            ->makeSuccess('No');
    }
}
