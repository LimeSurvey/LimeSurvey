<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class GrammerCheckHandler implements CommandHandlerInterface
{
    public function canHandle(Command $command)
    {
        $cmd = strtolower($command->getOperation());
        if (str_contains($cmd, 'grammer')) {
            return true;
        }
        return false;
    }

    public function execute(Command $command, AIClientInterface $client)
    {
        $command->setOperation('fix grammer errors and typos then print the plain text');
        return $client->generateContent();
    }
}
