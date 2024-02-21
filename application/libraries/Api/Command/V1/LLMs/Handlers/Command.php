<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class Command
{
    protected $prompt = null;
    protected $operation = null;

    public function __construct($operation, $prompt)
    {
        $this->prompt = $prompt;
        $this->operation = $operation;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function getPrompt()
    {
        return $this->prompt;
    }

    public function setOperation($operation)
    {
        $this->operation = $operation;
    }
}
