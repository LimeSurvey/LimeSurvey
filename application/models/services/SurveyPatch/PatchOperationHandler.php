<?php

namespace LimeSurvey\Model\Service\SurveyPatch;

/**
 * Patch handler interface
 */
interface PatchOperationHandlerInterface
{
    /**
     * Get model class
     *
     * @return string
     */
    public function getModelClass();

    /**
     * Get path type
     *
     * @return string
     */
    public function getPathType();

    /**
     * Get operation type
     *
     * @return string
     */
    public function getOperationType();

    /**
     * Apply patch
     *
     * @return string
     */
    public function applyPatch(PatchOperation $operation, PathMatch $pathMatch);
}
