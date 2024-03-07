<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class GrammarCheckHandler implements CommandHandlerInterface
{
    public function canHandle(Command $command)
    {
        $cmd = strtolower($command->getOperation());
        if (str_contains($cmd, 'grammar')) {
            return true;
        }
        return false;
    }

    public function execute(Command $command, AIClientInterface $client)
    {
        $command->setOperation('only fix grammar errors and typos then print the plain text and without changing the sentence type');
        return $client->generateContent();
    }
}
