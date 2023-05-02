<?php

namespace LimeSurvey\JsonPatch\OpHandler;

interface OpHandlerGroupableInterface extends OpHandlerInterface
{
    public function getGroupByParams();
    public function getValueKeyParam();
}
