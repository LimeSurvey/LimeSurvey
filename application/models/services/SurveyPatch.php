<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\JsonPatch\Path;
use LimeSurvey\Models\Services\JsonPatch\PathMatch;
use LimeSurvey\Models\Services\JsonPatch\Patch;
use LimeSurvey\Models\Services\JsonPatch\Operation;
use LimeSurvey\Models\Services\JsonPatch\OperationHandlerInterface;
use LimeSurvey\Models\Services\JsonPatch\Exception;

class JsonPatch
{
    protected $operationHandlers = [];

    /**
     * Apply patches
     *
     * @param int $surveyId
     * @param array $patchData
     * @throws \LimeSurvey\Model\Service\JsonPatch\Exception
     * @return array
     */
    public function apply($surveyId, $patchData)
    {
        // Todo: optimise patch data
        // - convert multiple operations on props of the same object to a single 'update' operation
        $patch = Patch::factory($patchData);
        $context = [
            'surveyId' => $surveyId
        ];

        $result = [
            'updatePatch' => []
        ];

        $paths = Path::getDefaults();
        foreach ($patch as $Operation) {
            $pathMatch = $this->getPathMatch($Operation->getPath(), $paths);
            if (!$pathMatch) {
                throw new Exception('Unsupported path "' . $Operation->getPath() . '"');
            }
            $this->handleOperation($pathMatch, $Operation, $context);
        }

        return  $result;
    }

    /**
     * Get path match
     *
     * @param Patch $patch
     * @param array $paths
     * @return ?PathMatch
     */
    protected function getPathMatch($patch, $paths = null)
    {
        // The order of definition is important
        // - more specific paths should be listed first
        $defaults = $paths ?: Path::getDefaults();

        $result = null;
        foreach ($defaults as $path) {
            if ($match = $path->match($patch)) {
                $result = $match;
                break;
            }
        }

        return $result;
    }

    /**
     * Handle operation
     *
     * @param PathMatch $pathMatch
     * @param Operation $Operation
     * @param array $context
     * @return void
     */
    public function handleOperation(PathMatch $pathMatch, Operation $Operation, $context)
    {
        $this->getOperationHandler($pathMatch, $Operation)
            ->applyPatch($Operation, $pathMatch, $context);
    }

    /**
     * Register operation handler
     *
     * @param PatchHandlerInterface $patchHandler
     * @return void
     */
    public function registerOperationHandler(OperationHandlerInterface $operationHandler)
    {
        $handlerKey = implode('_', [
            $operationHandler->getModelClass(),
            $operationHandler->getPathType(),
            $operationHandler->getOperationType()
        ]);

        $this->operationHandlers[$handlerKey] = $operationHandler;
    }

    /**
     * Get operation handler
     *
     * @param PathMatch $pathMatch
     * @param Operation $Operation
     * @return OperationHandlerInterface
     */
    public function getOperationHandler(PathMatch $pathMatch, Operation $operation)
    {
        $handlerKey = implode('_', [
            $pathMatch->getModelClass(),
            $pathMatch->getType(),
            $operation->getType()
        ]);

        if (!isset($this->operationHandlers[$handlerKey])) {
            throw new Exception('Survey patch handler not found with key "' . $handlerKey . '"');
        }

        return $this->operationHandlers[$handlerKey];
    }
}
