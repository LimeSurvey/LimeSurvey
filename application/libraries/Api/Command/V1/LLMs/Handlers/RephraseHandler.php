<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class RephraseHandler implements CommandHandlerInterface
{
    public function canHandle(Command $command)
    {
        $cmd = strtolower($command->getOperation());
        if (str_contains($cmd, 'rephrase')) {
            return true;
        }
        return false;
    }

    public function execute(Command $command, AIClientInterface $client)
    {
        $command->setOperation('Provide just one alternative rephrasing in simple text while keeping the same sentence structure');
        return $client->generateContent();
    }
}
