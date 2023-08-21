<?php

namespace LimeSurvey\ObjectPatch\OpHandler;

use LimeSurvey\ObjectPatch\Op\OpInterface;

interface OpHandlerInterface
{
    public function canHandle(OpInterface $op): bool;
    public function handle(OpInterface $op);
}
