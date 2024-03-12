<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class Command
{
    protected string $prompt;
    protected string $operation;

    public function __construct(string $operation, string $prompt)
    {
        $this->prompt = $prompt;
        $this->operation = $operation;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }
}
