<?php

namespace LimeSurvey\ObjectPatch;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\Op\OpStandard;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

class Patcher
{
    private $opHandlers = [];

    /**
     * Apply patch
     *
     * @throws ObjectPatchException
     */
    public function applyPatch($patch)
    {
        $operationsApplied = 0;
        if (is_array($patch) && !empty($patch)) {
            foreach ($patch as $patchOpData) {
                $op = OpStandard::factory(
                    $patchOpData['entity'] ?? null,
                    $patchOpData['op'] ?? null,
                    $patchOpData['id'] ?? null,
                    $patchOpData['props'] ?? null
                );
                $this->handleOp($op);
                $operationsApplied++;
            }
        }
        return $operationsApplied;
    }

    /**
     * Add operation handler
     *
     * @param OpHandlerInterface $opHandler
     * @return void
     */
    public function addOpHandler(OpHandlerInterface $opHandler)
    {
        $this->opHandlers[] = $opHandler;
    }

    /**
     * Apply operation
     *
     * @param OpInterface $op
     * @param array $params
     * @throws ObjectPatchException
     * @return void
     */
    private function handleOp(OpInterface $op)
    {
        $handled = false;
        foreach ($this->opHandlers as $opHandler) {
            if (!$opHandler->canHandle($op)) {
                continue;
            }
            $opHandler->handle($op);
            $handled = true;
            break;
        }

        if (!$handled) {
            throw new ObjectPatchException(
                sprintf(
                    'No operation handler found for "%s":"%s":"%s"',
                    $op->getEntityType(),
                    $op->getType()->getId(),
                    json_encode($op->getEntityId())
                )
            );
        }
    }
}
