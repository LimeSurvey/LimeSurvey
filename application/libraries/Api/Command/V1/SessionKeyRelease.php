<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Mixin\CommandResponseTrait,
    Request\Request,
    Response\ResponseFactory
};

class SessionKeyRelease implements CommandInterface
{
    use CommandResponseTrait;

    protected ?AuthSession $authSession = null;
    protected ?ResponseFactory $responseFactory = null;

    /**
     * Constructor
     *
     * @param AuthSession $authSession
     */
    public function __construct(
        AuthSession $authSession,
        ResponseFactory $responseFactory
    )
    {
        $this->authSession = $authSession;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Run session key release command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $this->authSession->doLogout(
            $request
            ->getData('sessionKey')
        );
        return $this->responseFactory
            ->makeSuccess('OK');
    }
}
