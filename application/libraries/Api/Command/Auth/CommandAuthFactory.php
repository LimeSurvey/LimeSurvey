<?php

namespace LimeSurvey\Api\Command\Auth;

use DI\FactoryInterface;

class CommandAuthFactory
{
    protected FactoryInterface $diFactory;

    /**
     * @param FactoryInterface $diFactory
     */
    public function __construct(FactoryInterface $diFactory)
    {
        $this->diFactory = $diFactory;
    }

    /**
     *  Get auth service
     *
     * @param string $name
     * @return CommandAuthInterface
     */
    public function getCommandAuth($name)
    {
        $class = '\\LimeSurvey\\Api\\Command\\Auth\\CommandAuth'
            . ucfirst($name);
        return $this->diFactory->make($class);
    }
}
