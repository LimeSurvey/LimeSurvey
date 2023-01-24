<?php

namespace LimeSurvey\Model\Service\SurveyPatch;


use LimeSurvey\Model\Service\SurveyPatch\PatchOperation;

/**
 * Patch
 */
class Patch
{
    protected $operations = [];

    public function __construct($operations = [])
    {
        $this->operations = $operations;
    }

    /**
     * Get operations
     *
     * @return array
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Factory
     *
     * @return Patch
     */
    public static function factory($patchArray)
    {
        return new Patch(
            array_map(function ($operation){
                return new PatchOperation(
                    $operation['op'],
                    $operation['path'],
                    $operation['value']
                );
            }, $patchArray)
        );
    }
}
