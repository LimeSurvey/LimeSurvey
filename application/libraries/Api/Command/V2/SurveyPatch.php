<?php

namespace LimeSurvey\Api\Command\V2;

use LimeSurvey\Api\Command\{
    CommandInterface,
    Request\Request,
    ResponseData\ResponseDataError
};
use LimeSurvey\Api\Command\Mixin\{
    CommandResponseTrait,
    Auth\AuthSessionTrait,
    Auth\AuthPermissionTrait
};
use Survey;

class SurveyPatch implements CommandInterface
{
    use AuthSessionTrait;
    use AuthPermissionTrait;
    use CommandResponseTrait;

    /**
     * Run survey patch command
     *
     * Apply patch and respond with update patch to be applied to the source (if any).
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        $id = (string) $request->getData('_id');
        $patch = $request->getData('patch');

        if (
            ($response = $this->checkKey($sessionKey)) !== true
        ) {
            return $response;
        }

        $result = null;
        if (is_array($patch)) {
            $result = $this->applyPatches(
                $id,
                $patch
            );
        }

        if (is_array($result) && !empty($result['errors'])) {
            return $this->responseErrorBadRequest(
                (new ResponseDataError(
                    'INVALID_SURVEY_PATCH',
                    'Invalid survey patch',
                    [
                        'errors' => $result['errors']
                    ]
                ))->toArray()
            );
        }

        return $this->responseSuccess($result);
    }

    /**
     * Apply patches
     *
     * @param int $surveyId
     * @param array $patch
     * @return array
     */
    protected function applyPatches($surveyId, $patches)
    {
        $validationResult = $this->validatePatches($patches);

        $result = [
            'updatePatch' => [],
            'errors' => []
        ];

        if ($validationResult !== true) {
            $result['errors'] = $validationResult;
        } else {
            foreach ($patches as $patch) {
                $meta = $this->getPatchMeta($patch);
                if (!$meta) {
                    // unsupported patch
                }
            }
        }

        return  $result;
    }

    protected function getPatchMeta($patch)
    {
        // The order of definition is important
        // - more specific paths should be listed first
        $pathMap = [
            '^/defaultlanguage/[_a-zA-Z0-9]+$' => [
                'modelClass' => Survey::class,
                'collection' => false
            ],
            '^/defaultlanguage$' => [
                'modelClass' => SurveyLanguageSetting::class,
                'collection' => false
            ],
            '^/languages$' => [
                'modelClass' => null, // not supported
                'collection' => false
            ],
            '^/languages/[0-9]+$' => [
                'modelClass' => null, // not supported
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/l10ns/[_a-zA-Z0-9]+$' => [
                'modelClass' => QuestionGroupL10n::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/l10ns/[_a-zA-Z0-9]+/[_a-zA-Z0-9]+$' => [
                'modelClass' => QuestionGroupL10n::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/[_a-zA-Z0-9]+$' => [
                'modelClass' => QuestionGroupL10n::class,
                'collection' => true
            ],
            '^/questionGroups' => 'question_groups_collection',
        ];

        $result = null;
        foreach ($pathMap as $pattern => $meta) {
            if (preg_match('#' . $pattern . '#', $patch['path']) === 1) {
                $result = $meta;
                break;
            }
        }

        return $result;
    }

    /**
     * Validate patches
     *
     * @param array $patch
     * @return boolean|array
     */
    protected function validatePatches($patches)
    {
        $errors = [];
        foreach ($patches as $k => $patch) {
            $patchErrors = $this->validatePatch($patch);
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
    protected function validatePatch($patch)
    {
        $errors = [];
        if (!isset($patch['op'])) {
            $errors[] = 'Invalid operation';
        }
        if (!isset($patch['path'])) {
            $errors[] = 'Invalid path';
        }
        if (array_key_exists('value', $patch)) {
            $errors[] = 'No value set';
        }
        return empty($errors) ?: $errors;
    }
}
