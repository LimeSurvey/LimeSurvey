<?php

namespace LimeSurvey\Api\Command;

use LimeSurvey\Api\Command\Request\Request;

interface CommandInterface
{
    public function run(Request $request);
}
