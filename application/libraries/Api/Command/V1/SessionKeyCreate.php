<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Mixin\CommandResponseTrait,
    Request\Request
};

class SessionKeyCreate implements CommandInterface
{
    use CommandResponseTrait;

    /**
     * Run session key create command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $authSession = new AuthSession();

        $username = (string) $request->getData('username');
        $password = (string) $request->getData('password');
        $plugin = (string) $request->getData(
            'plugin',
            'Authdb'
        );

        try {
            return $this->responseSuccess(
        $authSession->doLogin(
                    $username,
                    $password,
                    $plugin
                )
            );
        } catch (ExceptionInvalidUser $e) {
            return $this->responseErrorUnauthorised(
                ['status' => 'Invalid user name or password']
            );
        }
    }
}
