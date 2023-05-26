<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Mixin\CommandResponseTrait,
    Request\Request
};

class SessionKeyRelease implements CommandInterface
{
    use CommandResponseTrait;

    protected ?AuthSession $authSession = null;

    /**
     * Constructor
     *
     * @param AuthSession $authSession
     */
    public function __construct(AuthSession $authSession)
    {
        $this->authSession = $authSession;
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
        return $this->responseSuccess('OK');
    }
}
