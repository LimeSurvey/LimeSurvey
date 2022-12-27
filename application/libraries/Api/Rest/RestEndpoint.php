<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;

/**
 * RestEndpoint
 *
 */
class RestEndpoint
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
    public function getCommand()
    {
        return new $this->config['commandClass']();
    }

    public function getResponseRenderer()
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
     * @return Response
     */
    public function runCommand()
    {
        return $this->getCommand()->run(
            new Request($this->commandParams)
        );
    }
}
