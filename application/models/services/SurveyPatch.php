<?php

namespace LimeSurvey\Model\Service;

use LimeSurvey\Model\Service\SurveyPatch\Path;
use LimeSurvey\Model\Service\SurveyPatch\Exception;

class SurveyPatch
{
    /**
     * Apply patches
     *
     * @param int $surveyId
     * @param array $patch
     * @throws \LimeSurvey\Model\Service\SurveyPatch\Exception
     * @return array
     */
    public function apply($surveyId, $patch)
    {
        $validationResult = $this->validatePatch($patch);

        $result = [
            'updatePatch' => [],
            'errors' => []
        ];

        if ($validationResult !== true) {
            $result['errors'] = $validationResult;
        } else {
            foreach ($patch as $operation) {
                $match = $this->getPathMatch($operation['path']);
                if (!$match) {
                    throw new Exception('Unsupported path "' . $operation['path'] . '"');
                }

                $modelClass = $match->getModelClass();
                if ($modelClass == null) {
                    // null model class indicates patches should be ignored
                    continue;
                }

                switch ($match->getType()) {
                    case Path::PATH_TYPE_OBJECT:
                    case Path::PATH_TYPE_PROP:
                        break;
                    case Path::PATH_TYPE_COLLECTION:
                        break;
                }

            }
        }

        return  $result;
    }

    protected function getPathMatch($patch)
    {
        // The order of definition is important
        // - more specific paths should be listed first
        $defaults = Path::getDefaults();

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
     * Validate patch
     *
     * @param array $patch
     * @return boolean|array
     */
    protected function validatePatch($patch)
    {
        $errors = [];
        foreach ($patch as $k => $operation) {
            $operationErrors = $this->validatePatchOperation($operation);
            if ($operationErrors !== true) {
                $errors[$k] = $operationErrors;
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
    protected function validatePatchOperation($operation)
    {
        $errors = [];
        if (!isset($operation['op'])) {
            $errors[] = 'Invalid operation';
        }
        if (!isset($operation['path'])) {
            $errors[] = 'Invalid path';
        }
        if (array_key_exists('value', $operation)) {
            $errors[] = 'No value set';
        }
        return empty($errors) ?: $errors;
    }
}
