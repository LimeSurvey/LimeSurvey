<?php

namespace LimeSurvey\JsonPatch;

use LimeSurvey\JsonPatch\OpHandler\OpHandlerInterface;

class Patcher
{
    private $opHandlers = [];
    private $params = [];

    public function applyPatch($patch, $params = [])
    {
        $operationsApplied = 0;
        if (is_array($patch) && !empty($patch)) {
            $params = array_merge($params, $this->params);

            $validationResult = $this->validatePatch($patch);
            if ($validationResult !== true) {
                throw new JsonPatchException(
                    sprintf(
                        'Invalid patch %s',
                        print_r($patch, true)
                    )
                );
            }

            foreach ($patch as $patchOp) {
                $this->applyOp($patchOp, $params);
                $operationsApplied++;
            }
        }
        return $operationsApplied;
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
        $handled = false;
        foreach ($this->opHandlers as $opHandler) {
            if ($opHandler->getOp()->getId() !== $patchOp['op']) {
                continue;
            }

            $matches = [];
            if (
                preg_match(
                    '#' . $opHandler->getPattern()->getRaw() . '#',
                    $patchOp['path'],
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
                $params
            );

            $opHandler->applyOperation($params, $patchOp['value']);
            $handled = true;

            break;
        }

        if (!$handled) {
            throw new JsonPatchException(
                sprintf(
                    'No oepration handler found for "%s":"%s"',
                    $patchOp['op'],
                    $patchOp['path']
                )
            );
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
            $errors[] = sprintf(
                'Invalid operation for "%s":"%s"',
                $patchOp['op'],
                $patchOp['path']
            );
        }
        if (!isset($patchOp['path'])) {
            $errors[] = sprintf(
                'Invalid path for "%s":"%s"',
                $patchOp['op'],
                $patchOp['path']
            );
        }
        if (!array_key_exists('value', $patchOp)) {
            $errors[] = sprintf(
                'No value provided for "%s":"%s"',
                $patchOp['op'],
                $patchOp['path']
            );
        }
        return empty($errors) ?: $errors;
    }
}
