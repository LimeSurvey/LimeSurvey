<?php

namespace LimeSurvey\JsonPatch;

use LimeSurvey\JsonPatch\Op\OpInterface;
use LimeSurvey\JsonPatch\Op\OpStandard;
use LimeSurvey\JsonPatch\OpHandler\{
    OpHandlerInterface,
    OpHandlerGroupableInterface
};

class Patcher
{
    private $opHandlers = [];
    private $params = [];
    private $opGroups = [];

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
                $this->handleOp($op, $params);
                $operationsApplied++;
            }
            $this->applyGroupedOps();
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
    private function handleOp(OpInterface $op, $params = [])
    {
        $handled = false;
        $opTypeId = $op->getType()->getId();
        foreach ($this->opHandlers as $opHandler) {
            if ($opHandler->getOpType()->getId() !== $opTypeId) {
                continue;
            }

            $matches = [];
            $patternString = $opHandler->getPattern()->getRegex();
            if (
                preg_match(
                    '#' . $patternString . '#',
                    $op->getPath(),
                    $matches
                ) !== 1
            ) {
                continue;
            }

            if (!empty($matches)) {
                array_shift($matches);
            }
            $params = array_merge(
                $matches,
                is_array($params) ? $params : []
            );

            if ($opHandler instanceof OpHandlerGroupableInterface) {
                $this->queueToOpGroup(
                    $op,
                    $opHandler,
                    $params
                );
            } else {
                $opHandler->applyOperation($params, $op->getValue());
            }

            $handled = true;

            break;
        }

        if (!$handled) {
            throw new JsonPatchException(
                sprintf(
                    'No operation handler found for "%s":"%s"',
                    $op->getType()->getId(),
                    $op->getValue()
                )
            );
        }
    }

    /**
     * Queue operation to op group
     *
     * A group of operations are handled by a single handler.
     *
     * @param OpInterface $op
     * @param OpHandlerGroupableInterface $opHandler
     * @param array $params
     * @return void
     */
    private function queueToOpGroup(OpInterface $op, OpHandlerGroupableInterface $opHandler, $params)
    {
        $groupByParams = $opHandler->getGroupByParams();
        $groupValues = [];
        foreach ($groupByParams as $groupByParam) {
            if (array_key_exists($groupByParam, $params)) {
                $groupValues[$groupByParam] = $params[$groupByParam];
            }
        }
        $regex = $opHandler->getPattern()->getRegex();
        $opTypeId = $op->getType()->getId();
        $groupId = implode(
        '|', [
            $regex,
            $opTypeId,
            implode(',', $groupValues)
        ]);
        if (!isset($this->opGroups[$groupId])) {
            $this->opGroups[$groupId] = [
                'opHandler' => $opHandler,
                'params' => $params,
                'values' => []
            ];
        }

        $valueKey = null;
        if ($valueKeyParam = $opHandler->getValueKeyParam()) {
            $valueKey = $params[$valueKeyParam];
        }

        if ($valueKey) {
            $this->opGroups[$groupId]['values'][$valueKey] = $op->getValue();
        } else {
            $this->opGroups[$groupId]['values'][] = $op->getValue();
        }
    }

    /**
     * Apply grouped operations
     *
     * A group operation is a group of operations which are handled by a single handler.
     *
     * @return void
     */
    private function applyGroupedOps()
    {
        if ($this->opGroups) {
            foreach ($this->opGroups as $opGroup) {
                $opGroup['opHandler']->applyOperation(
                    $opGroup['params'],
                    $opGroup['values']
                );
            }
        }
    }
}
