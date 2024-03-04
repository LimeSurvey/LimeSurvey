<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class ExtendTextHandler implements CommandHandlerInterface
{
    public function canHandle(Command $command)
    {
        $cmd = strtolower($command->getOperation());
        if (str_contains($cmd, 'longer')) {
            return true;
        }
        return false;
    }

    public function execute(command $command, AIClientInterface $client)
    {
        $command->setOperation('Expand the text slightly, adding no more than 10 extra words');
        return $client->generateContent();
    }
}
