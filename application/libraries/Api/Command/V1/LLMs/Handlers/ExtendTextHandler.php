<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class ExtendTextHandler implements CommandHandlerInterface
{
    public function canHandle(Command $command): bool
    {
        $cmd = strtolower($command->getOperation());
        if (str_contains($cmd, 'longer')) {
            return true;
        }
        return false;
    }

    public function execute(Command $command, AIClientInterface $client): string
    {
        $command->setOperation("Slightly expand the text, incorporating no more than 10 additional words, while maintaining the sentence type");
        return $client->generateContent();
    }
}
