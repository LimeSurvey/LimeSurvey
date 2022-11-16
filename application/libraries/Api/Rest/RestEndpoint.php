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

    /**
     * Run Command
     *
     * @return Response
     */
    public function runCommand()
    {
        $command = new $this->config['commandClass']();
        $request = new Request($this->commandParams);
        return $command->run($request);
    }
}
