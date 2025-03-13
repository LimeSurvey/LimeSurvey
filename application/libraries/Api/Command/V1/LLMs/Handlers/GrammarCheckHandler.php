<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class GrammarCheckHandler implements CommandHandlerInterface
{
    public function canHandle(Command $command): bool
    {
        $cmd = strtolower($command->getOperation());
        if (str_contains($cmd, 'grammar')) {
            return true;
        }
        return false;
    }

    public function execute(Command $command, AIClientInterface $client): string
    {
        $command->setOperation('Only correct grammar errors and typos, then print the plain text without altering the sentence type');
        return $client->generateContent();
    }
}
