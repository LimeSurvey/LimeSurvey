<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Auth\AuthSession;
use LimeSurvey\Api\Command\V1\Exception\ExceptionInvalidUser;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    Mixin\CommandResponseTrait,
    ResponseData\ResponseDataError,
    Response\ResponseFactory
};

class SessionKeyCreate implements CommandInterface
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
     * Run session key create command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $username = (string) $request->getData('username');
        $password = (string) $request->getData('password');
        $plugin = (string) $request->getData(
            'plugin',
            'Authdb'
        );

        try {
            return $this->responseFactory->makeSuccess(
                $this->authSession->doLogin(
                    $username,
                    $password,
                    $plugin
                )
            );
        } catch (ExceptionInvalidUser $e) {
            return $this->responseFactory->makeErrorUnauthorised(
                (new ResponseDataError(
                    'INVALID_USER',
                    'Invalid user name or password'
                ))->toArray()
            );
        }
    }
}
