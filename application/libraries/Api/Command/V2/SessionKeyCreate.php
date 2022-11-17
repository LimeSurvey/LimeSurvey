<?php

namespace LimeSurvey\Api\Command\V2;

use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\ApiSession;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class SessionKeyCreate implements CommandInterface
{
    use CommandResponse;

    /**
     * Run session key create command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $apiSession = new ApiSession();

        $username = (string) $request->getData('username');
        $password = (string) $request->getData('password');
        $plugin = (string) $request->getData(
            'plugin',
            'Authdb'
        );

        try {
            return $this->responseSuccess(
        $apiSession->doLogin(
        $username,
        $password,
        $plugin
                )
            );
        } catch (ExceptionInvalidUser $e) {
            return $this->responseErrorUnauthorised(
                [
                    'error' => [
                        'code' => 'INVALID_USER',
                        'message' => 'Invalid user name or password',
                        'data' => []
                    ]
                ]
            );
        }
    }
}
