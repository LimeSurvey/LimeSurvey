<?php

namespace LimeSurvey\SurveyPatch;

use LimeSurvey\SurveyPatch\OpHandler\OpHandlerInterface;

class Patcher
{
    private $opHandlers = [];

    public function applyPatch($surveyId, $patch)
    {
        if (is_array($patch) && !empty($patch)) {
            $validationResult = $this->validatePatch($patch);
            if ($validationResult !== true) {
                throw new SurveyPatchException('Invalid patch');
            }

            $params = ['surveyId' => $surveyId];
            foreach ($patch as $patchOp) {
                $this->applyOp($patchOp, $params);
            }
        }
    }

    public function applyOp($patchOp, $params)
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

    public function addOpHandler(OpHandlerInterface $opHandler)
    {
        $this->opHandlers[] = $opHandler;
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
