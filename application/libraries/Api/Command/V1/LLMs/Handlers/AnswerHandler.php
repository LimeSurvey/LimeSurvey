<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

class AnswerHandler implements CommandHandlerInterface
{
    public function canHandle(Command $command): bool
    {
        return true;
    }

    public function execute(Command $command, AIClientInterface $client): string
    {
        $command->setOperation("Answer briefly with no more than 15 words");
        return $client->generateContent();
    }
}
