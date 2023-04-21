<?php

namespace LimeSurvey\Models\Services\JsonPatch;

/**
 * Patch handler interface
 */
interface OperationHandlerInterface
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
     * @param Operation $operation
     * @param PathMatch $pathMatch
     * @param array $context
     * @return void
     */
    public function applyPatch(Operation $operation, PathMatch $pathMatch, $context);
}
