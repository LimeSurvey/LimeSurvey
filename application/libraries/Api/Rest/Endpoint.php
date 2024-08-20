<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Rest\Renderer\RendererBasic;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Response\ResponseFactory,
    Request\Request
};
use LimeSurvey\Api\Authentication\{
    AuthenticationInterface,
    AuthenticationTokenSimple
};
use LimeSurvey\Api\Rest\Renderer\RendererInterface;
use Psr\Container\ContainerInterface;

/**
 * RestEndpoint
 *
 */
class Endpoint
{
    /** @var array */
    protected $config = [];
    /** @var array */
    protected $commandParams = [];
    protected ResponseFactory $responseFactory;
    protected ContainerInterface $diContainer;

    /**
     * Constructor
     *
     * @param array $config
     * @param array $commandParams
     * @param ContainerInterface $diContainer
     * @return string|null
     */
    public function __construct(
        $config,
        $commandParams,
        ResponseFactory $responseFactory,
        ContainerInterface $diContainer
    ) {
        $this->config = $config;
        $this->commandParams = $commandParams;
        $this->responseFactory = $responseFactory;
        $this->diContainer = $diContainer;
    }

    /**
     * Get Command
     *
     * @return CommandInterface
     */
    protected function getCommand()
    {
        return $this->diContainer->get($this->config['commandClass']);
    }

    /**
     * Get Authenticator
     *
     * @return AuthenticationInterface
     */
    protected function getAuthenticator()
    {
        $authenticatorClass = !empty($this->config['authenticatorClass'])
            ? $this->config['authenticatorClass']
            : AuthenticationTokenSimple::class;
        return $this->diContainer->get($authenticatorClass);
    }

    /**
     * Get Response Renderer
     *
     * @return RendererInterface
     */
    protected function getResponseRenderer()
    {
        $apiVersion = isset($this->config['apiVersion'])
            ? ucfirst($this->config['apiVersion'])
            : false;
        if ($apiVersion) {
            $class = 'LimeSurvey\Api\Rest\\'
                . $apiVersion
                . '\RestRenderer' . $apiVersion;
        } else {
            // non version specific command use RendererBasic
            $class = RendererBasic::class;
        }
        return $this->diContainer->get($class);
    }

    /**
     * Run Command
     *
     * @return void
     */
    public function run()
    {
        $renderer = $this->getResponseRenderer();

        if (!empty($this->config['auth'])) {
            if (
                !$this->getAuthenticator()
                ->isAuthenticated(
                    $this->commandParams['authToken']
                )
            ) {
                $renderer->returnResponse(
                    $this->responseFactory
                        ->makeErrorUnauthorised()
                );
                return;
            }
        }

        $response = $this->getCommand()->run(
            new Request($this->commandParams)
        );
        $renderer->returnResponse($response);
    }
}
