<?php

namespace LimeSurvey\Api\Command\V2;

use LimeSurvey\Api\Auth\ApiAuthSession;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Mixin\CommandResponseTrait,
    Request\Request
};

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
        (new ApiAuthSession())->doLogout(
            $request
            ->getData('sessionKey')
        );
        return $this->responseSuccess('OK');
    }
}
