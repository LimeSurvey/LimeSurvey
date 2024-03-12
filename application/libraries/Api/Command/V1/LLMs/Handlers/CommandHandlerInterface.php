<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;

interface CommandHandlerInterface
{
    public function canHandle(Command $command): bool;
    public function execute(Command $command, AIClientInterface $client): string;
}
