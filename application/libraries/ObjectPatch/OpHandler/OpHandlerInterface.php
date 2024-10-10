<?php

namespace LimeSurvey\ObjectPatch\OpHandler;

use LimeSurvey\ObjectPatch\Op\OpInterface;

interface OpHandlerInterface
{
    public function canHandle(OpInterface $op): bool;

    public function handle(OpInterface $op);

    /*
     * Should be implemented AND called in handle methods separately
     * Needs to return empty array when everything is fine,
     * or an array of errors
     */
    public function validateOperation(OpInterface $op): array;
}
