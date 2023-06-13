<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request
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
    protected ContainerInterface $diContainer;

    /**
     * Constructor
     *
     * @param array $config
     * @param array $commandParams
     * @param ContainerInterface $diContainer
     * @return string|null
     */
    public function __construct($config, $commandParams, ContainerInterface $diContainer)
    {
        $this->config = $config;
        $this->commandParams = $commandParams;
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
     * Get Response Renderer
     *
     * @return RendererInterface
     */
    protected function getResponseRenderer()
    {
        $apiVersion = ucfirst($this->config['apiVersion']);
        $class = 'LimeSurvey\Api\Rest\\'
            . $apiVersion
            . '\RestRenderer' . $apiVersion;
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
        try {
            $response = $this->getCommand()->run(
                new Request($this->commandParams)
            );
            $renderer->returnResponse($response);
        } catch (\Exception $e) {
            $renderer->returnException($e);
        }
    }
}
