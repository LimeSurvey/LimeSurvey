<?php

namespace LimeSurvey\Model\Service;

use LimeSurvey\Model\Service\SurveyPatch\Path;
use LimeSurvey\Model\Service\SurveyPatch\PathMatch;
use LimeSurvey\Model\Service\SurveyPatch\Patch;
use LimeSurvey\Model\Service\SurveyPatch\PatchOperation;
use LimeSurvey\Model\Service\SurveyPatch\PatchHandlerInterface;
use LimeSurvey\Model\Service\SurveyPatch\Exception;

class SurveyPatch
{
    protected $operationHandlers = [];

    /**
     * Apply patches
     *
     * @param int $surveyId
     * @param array $patchData
     * @throws \LimeSurvey\Model\Service\SurveyPatch\Exception
     * @return array
     */
    public function apply($surveyId, $patchData)
    {
        $patch = Patch::factory($patchData);

        $result = [
            'updatePatch' => []
        ];

        $paths = Path::getDefaults();
        foreach ($patch as $patchOperation) {
            $pathMatch = $this->getPathMatch($patchOperation->getPath(), $paths);
            if (!$pathMatch) {
                throw new Exception('Unsupported path "' . $patchOperation->getPath() . '"');
            }
            $this->handleOperation($pathMatch, $patchOperation);
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
     * @param PatchOperation $patchOperation
     * @return void
     */
    public function handleOperation(PathMatch $pathMatch, PatchOperation $patchOperation)
    {
        $this->getOperationHandler($pathMatch, $patchOperation)
            ->applyPatch($patchOperation, $pathMatch);
    }

    /**
     * Register operation handler
     *
     * @param PatchHandlerInterface $patchHandler
     * @return void
     */
    public function registerOperationHandler(PatchHandlerInterface $patchHandler)
    {
        $handlerKey = implode('_', [
            $patchHandler->getModelClass(),
            $patchHandler->getPathType(),
            $patchHandler->getOperationType()
        ]);

        $this->operationHandlers[$handlerKey] = $patchHandler;
    }

    /**
     * Get operation handler
     *
     * @param PathMatch $pathMatch
     * @param PatchOperation $patchOperation
     * @return PatchHandler
     */
    public function getOperationHandler(PathMatch $pathMatch, PatchOperation $patchOperation)
    {
        $handlerKey = implode('_', [
            $pathMatch->getModelClass(),
            $pathMatch->getType(),
            $patchOperation->getType()
        ]);

        if (!isset($this->operationHandlers[$handlerKey])) {
            throw new Exception('Survey patch handler not found with key "' . $handlerKey . '"');
        }

        return $this->operationHandlers[$handlerKey];
    }
}
