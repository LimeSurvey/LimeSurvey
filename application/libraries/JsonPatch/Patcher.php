<?php

namespace LimeSurvey\JsonPatch;

use LimeSurvey\JsonPatch\OpHandler\OpHandlerInterface;

class Patcher
{
    private $opHandlers = [];
    private $params = [];

    public function applyPatch($patch, $params = [])
    {
        if (is_array($patch) && !empty($patch)) {
            $params = array_merge($params, $this->params);

            $validationResult = $this->validatePatch($patch);
            if ($validationResult !== true) {
                throw new JsonPatchException('Invalid patch');
            }

            foreach ($patch as $patchOp) {
                $this->applyOp($patchOp, $params);
            }
        }
    }

    public function addOpHandler(OpHandlerInterface $opHandler)
    {
        $this->opHandlers[] = $opHandler;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    protected function applyOp($patchOp, $params)
    {
        foreach ($this->opHandlers as $opHandler) {
            if ($opHandler->getOp()->_toString() !== $patchOp['op']) {
                continue;
            }

            $matches = [];
            if (
                preg_match(
                    '#' . $opHandler->getPattern() . '#',
                    $patchOp['path'],
                    $matches
                ) !== 1
            ) {
                continue;
            }

            $params = array_merge(
                $matches,
                $params
            );

            $opHandler->applyOperation($params, $patchOp['value']);
        }
    }

    /**
     * Validate patches
     *
     * @param array $patch
     * @return boolean|array
     */
    protected function validatePatch($patch)
    {
        $errors = [];
        foreach ($patch as $k => $patchOp) {
            $patchErrors = $this->validatePatchOp($patchOp);
            if ($patchErrors !== true) {
                $errors[$k] = $patchErrors;
            }
        }
        return empty($errors) ?: $errors;
    }

    /**
     * Validate patch
     *
     * @param array $patch
     * @return boolean|array
     */
    protected function validatePatchOp($patchOp)
    {
        $errors = [];
        if (!isset($patchOp['op'])) {
            $errors[] = 'Invalid operation';
        }
        if (!isset($patchOp['path'])) {
            $errors[] = 'Invalid path';
        }
        if (!array_key_exists('value', $patchOp)) {
            $errors[] = 'No value set';
        }
        return empty($errors) ?: $errors;
    }
}
