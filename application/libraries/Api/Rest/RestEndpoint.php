<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Command\CommandInterface;
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

    public function getCommand()
    {
        return new $this->config['commandClass']();
    }

    public function getResponseRenderer()
    {
        $class = 'LimeSurvey\Api\Rest\Renderer\RestRenderer'
            . ucfirst($this->config['apiVersion']);
        return new $class();
    }

    /**
     * Run Command
     *
     * @return Response
     */
    public function runCommand()
    {
        $request = new Request($this->commandParams);
        return $this->getCommand()->run($request);
    }
}
