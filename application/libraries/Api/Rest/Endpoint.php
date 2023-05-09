<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Command\Request\Request;

/**
 * RestEndpoint
 *
 */
class Endpoint
{
    protected $config = [];
    protected $commandParams = [];

    /**
     * Constructor
     *
     * @param array $config
     * @param array $commandParams
     * @return string|null
     */
    public function __construct($config, $commandParams)
    {
        $this->config = $config;
        $this->commandParams = $commandParams;
    }

    /**
     * Get Command
     *
     * @return LimeSurvey\Api\Command\CommandInterface
     */
    protected function getCommand()
    {
        return new $this->config['commandClass']();
    }

    protected function getResponseRenderer()
    {
        $apiVersion = ucfirst($this->config['apiVersion']);
        $class = 'LimeSurvey\Api\Rest\\'
            . $apiVersion
            . '\RestRenderer' . $apiVersion;
        return new $class;
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
