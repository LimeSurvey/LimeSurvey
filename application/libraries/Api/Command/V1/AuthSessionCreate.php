<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\{
    Request\Request,
    Response\Response,
    ResponseData\ResponseDataError,
};

class AuthSessionCreate extends AuthTokenSimpleCreate
{
    /**
     * Run session key create command.
     *
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $username = (string) $request->getData('username');
        $password = (string) $request->getData('password');

        try {
            $responseData = $this->auth->login(
                $username,
                $password
            );
            return $this->responseFactory->makeSuccess(
                $responseData['token']
            );
        } catch (ExceptionInvalidUser $e) {
            return $this->responseFactory->makeErrorUnauthorised(
                (new ResponseDataError(
                    'INVALID_USER',
                    $e->getMessage()
                ))->toArray()
            );
        }
    }
}
