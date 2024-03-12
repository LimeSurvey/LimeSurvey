<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

interface AIClientInterface
{
    public function generateContent(): string;
}
