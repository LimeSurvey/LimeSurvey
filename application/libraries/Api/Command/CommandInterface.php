<?php

namespace LimeSurvey\Api\Command;

interface CommandInterface
{
    public function run(CommandRequest $request);
}
