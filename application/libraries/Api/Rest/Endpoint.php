<?php

namespace LimeSurvey\Api\Rest;

use LimeSurvey\Api\Rest\Renderer\RendererBasic;
use LimeSurvey\Api\Command\{
    CommandInterface,
    Auth\CommandAuthFactory,
    Auth\CommandAuthInterface,
    Request\Request
};
use LimeSurvey\Api\Rest\Renderer\RendererInterface;
use DI\FactoryInterface;

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
    protected CommandAuthFactory $commandAuthFactory;
    protected FactoryInterface $diFactory;


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
        CommandAuthFactory $commandAuthFactory,
        FactoryInterface $diFactory
    ) {
        $this->config = $config;
        $this->commandParams = $commandParams;
        $this->commandAuthFactory = $commandAuthFactory;
        $this->diFactory = $diFactory;
    }

    /**
     * Get auth service
     *
     * @return ?CommandAuthInterface
     */
    protected function getCommandAuth()
    {
        return !empty($this->config['auth'])
        ? $this->commandAuthFactory->getCommandAuth(
            $this->config['auth']
        )
        : null;
    }

    /**
     * Get Command
     *
     * @return CommandInterface
     */
    protected function getCommand()
    {
        return $this->diFactory->make(
            $this->config['commandClass'],
            [
                'commandAuth' => $this->getCommandAuth()
            ]
        );
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
        return $this->diFactory->make($class);
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
