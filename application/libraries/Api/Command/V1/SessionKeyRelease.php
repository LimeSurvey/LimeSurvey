<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
};
use LimeSurvey\Api\Command\Mixin\CommandResponseTrait;

class SessionKeyRelease implements CommandInterface
{
    use CommandResponseTrait;

    /**
     * Run session key release command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        (new AuthSession())->doLogout(
            $request
            ->getData('sessionKey')
        );
        return $this->responseSuccess('OK');
    }
}
