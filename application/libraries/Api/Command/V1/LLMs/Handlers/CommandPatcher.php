<?php

namespace LimeSurvey\Libraries\Api\Command\V1\LLMs\Handlers;


class CommandPatcher
{

    private $handlers = [];
    private Command $command;
    private AIClientInterface $client;

    public function __construct(Command $command, AIClientInterface $client)
    {
        $this->registerHandlers();
        $this->command = $command;
        $this->client = $client;
    }

    public function apply()
    {
        foreach ($this->handlers as $handler) {
            $op = (new $handler());
            if ($op->canHandle($this->command) == true) {
                return $op->execute($this->command, $this->client);
            }
        }
    }

    public function registerHandlers(): void
    {
        $this->addHandler(ExtendTextHandler::class);
        $this->addHandler(GrammarCheckHandler::class);
        $this->addHandler(RephraseHandler::class);
        $this->addHandler(ShortenTextHandler::class);
        $this->addHandler(TranslateHandler::class);
        $this->addHandler(AnswerHandler::class);
    }

    public function addHandler($handler): void
    {
        $this->handlers[] = $handler;
    }
}
