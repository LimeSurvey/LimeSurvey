<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Command\Request\Request;
use Psr\Container\ContainerInterface;

/**
 * RestEndpoint
 *
 */
class Endpoint
{
    protected $config = [];
    protected $commandParams = [];
    protected ?ContainerInterface $diContainer = null;

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
     * @return LimeSurvey\Api\Command\CommandInterface
     */
    protected function getCommand()
    {
        return $this->diContainer->get($this->config['commandClass']);
    }

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
            return $renderer->returnResponse($response);
        } catch (\Exception $e) {
            $renderer->returnException($e);
        }
    }
}
