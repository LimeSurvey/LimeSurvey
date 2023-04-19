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
            $transaction = \Yii::app()->db->beginTransaction();
            try {
                foreach ($patches as $patch) {
                    $match = $this->getPatchMatch($patch);
                    if (!$match) {
                        // unsupported patch
                        continue;
                    }
                    [ $meta, $matches ] = $match;

                    $model = ${$meta['modelClass']}::find($matches[1]);

                    if (is_array($patch['value'])) {
                        foreach ($patch['value'] as $prop => $value) {
                            $model->{$prop} = $value;
                        }
                    }

                    $model->save();
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollback();
                throw $e;
            }
        }

        return  $result;
    }

    protected function getPatchMatch($patch)
    {
        // The order of definition is important
        // - more specific paths should be listed first
        $pathMap = [
            '^/defaultLanguage$' => [
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
            '^/questionGroups$' => [
                'modelClass' => QuestionGroup::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+$' => [
                'modelClass' => QuestionGroup::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/l10ns$' => [
                'modelClass' => QuestionGroupL10n::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/l10ns/[_\-a-zA-Z0-9]+$' => [
                'modelClass' => QuestionGroupL10n::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/questions$' => [
                'modelClass' => Question::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+$' => [
                'modelClass' => Question::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/l10ns$' => [
                'modelClass' => QuestionL10n::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/l10ns/[_\-a-zA-Z0-9]+$' => [
                'modelClass' => QuestionL10n::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/attributes$' => [
                'modelClass' => QuestionAttribute::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/attributes/[_\-a-zA-Z0-9]+$' => [
                'modelClass' => QuestionAttribute::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/subquestions$' => [
                'modelClass' => Question::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/subquestions/[0-9]+$' => [
                'modelClass' => Question::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/subquestions/[0-9+/l10ns$' => [
                'modelClass' => QuestionL10n::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/subquestions/[0-9]+/l10ns/[_\-a-zA-Z0-9]+$' => [
                'modelClass' => QuestionL10n::class,
                'collection' => false
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/subquestions/[0-9]+/attributes$' => [
                'modelClass' => QuestionAttribute::class,
                'collection' => true
            ],
            '^/questionGroups/[0-9]+/questions/[0-9]+/subquestions/[0-9]+/attributes/[_\-a-zA-Z0-9]+$' => [
                'modelClass' => QuestionAttribute::class,
                'collection' => false
            ],
        ];

        $result = null;
        foreach ($pathMap as $pattern => $meta) {
            $matches = [];
            if (
                preg_match(
                '#' . $pattern . '#',
                $patch['path'],
                $matches
                ) === 1
            ) {
                $result = [$meta, $matches];
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
