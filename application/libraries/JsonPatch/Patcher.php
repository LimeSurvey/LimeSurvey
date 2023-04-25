<?php

namespace LimeSurvey\JsonPatch;

use LimeSurvey\JsonPatch\Op\OpInterface;
use LimeSurvey\JsonPatch\Op\OpStandard;
use LimeSurvey\JsonPatch\OpHandler\OpHandlerInterface;


class Patcher
{
    private $opHandlers = [];
    private $params = [];

    /**
     * Apply patch
     *
     * @throws JsonPatchException
     */
    public function applyPatch($patch, $params = [])
    {
        $operationsApplied = 0;
        if (is_array($patch) && !empty($patch)) {
            $params = array_merge($params, $this->params);
            foreach ($patch as $patchOpData) {
                $op = OpStandard::factory(
                    $patchOpData['path'] ?? null,
                    $patchOpData['op'] ?? null,
                    $patchOpData['value'] ?? null
                );
                $this->applyOp($op, $params);
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
     * Set params
     *
     * @param array $params
     * @return void
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Apply operation
     *
     * @param OpInterface $op
     * @param array $params
     * @throws JsonPatchException
     * @return void
     */
    protected function applyOp($op, $params = [])
    {
        $handled = false;
        foreach ($this->opHandlers as $opHandler) {
            if ($opHandler->getOpType()->getId() !== $op->getType()->getId()) {
                continue;
            }

            $matches = [];
            if (
                preg_match(
                    '#' . $opHandler->getPattern()->getRaw() . '#',
                    $op->getPath(),
                    $matches
                ) !== 1
            ) {
                print_r($opHandler->getPattern()->getRaw()); exit;
                continue;
            }

            if (!empty($matches)) {
                array_shift($matches);
            }
            $params = array_merge(
                $matches,
                is_array($params) ? $params : []
            );

            $opHandler->applyOperation($params, $op->getValue());
            $handled = true;

            break;
        }

        if (!$handled) {
            throw new JsonPatchException(
                sprintf(
                    'No oepration handler found for "%s":"%s"',
                    $op->getType()->getId(),
                    $op->getValue()
                )
            );
        }
    }
}
