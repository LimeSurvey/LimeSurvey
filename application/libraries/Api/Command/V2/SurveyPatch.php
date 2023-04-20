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
use QuestionAttribute;
use QuestionL10n;
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

        if (
            is_array($result)
            && !empty($result['errors'])
        ) {
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
                    $updateMeta = $this->getUpdateMeta($patch);

                    if (!$updateMeta) {
                        // unsupported patch
                        continue;
                    }
                    [ $meta, $matches ] = $updateMeta;

                    var_dump($updateMeta, $patch); exit;

                    $this->update(
                        $meta['modelClass'],
                        $meta['isCollection'],
                        $matches,
                        $patch['value']
                    );
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollback();
                throw $e;
            }
        }
        return  $result;
    }

    /**
     * Update
     *
     * @param string $class
     * @param bool $isCollection
     * @param string $id
     * @param string $data
     */
    protected function update($class, $isCollection, $ids, $data)
    {
        if (empty($class)) {
            // path not supported
            return;
        }

        if ($isCollection) {
            // handle collection
        } else {
            $this->updateRecord(
                $class,
                $ids,
                $data
            );
        }
    }

    /**
     * Update record
     *
     * @param string $class
     * @param string $id
     * @param string $data
     */
    protected function updateRecord($class, $id, $data)
    {
        print_r([
            'id' => $id,
            'data' => $data
        ]); exit;
        $model = $class::model()->findByPk($id);
        if (!$model) {
            return; // model not found
        }
        if (is_array($data)) {
            foreach ($data as $prop => $value) {
                $model->{$prop} = $value;
            }
        }
        $model->save();
    }

    /**
     * Get pattern for path
     *
     * Converts a path to a regex pattern.
     *
     * '/languages/$languageId' becomes '/languages/(?<languageId>[^\/]+)'
     *
     * @param string $path
     * @return string
     */
    protected function getPatternForPath($path)
    {
        $parts = array_map(function($part){
            if (empty($part)) {
                return '';
            }
            return ($part[0] == '$')
                ? '(?<' . substr($part, 1). '>[^\/]+)'
                : $part;
        }, explode('/', $path));
        return implode('/', $parts);
    }

    /**
     * Get params for path
     *
     * @param string $path
     * @return array
     */
    protected function getParamsForPath($path)
    {
        $parts = array_map(function ($part) {
            return (!empty($part) && $part[0] == '$')
                ? substr($part, 1)
                : null;
        }, explode('/', $path));

        return array_filter($parts, function($part){
            return !empty($part);
        });
    }

    /**
     * Get update meta
     *
     * Gets information on what to update by matching the update patch path.
     *
     * @param array $patch
     * @return meta
     */
    protected function getUpdateMeta($patch)
    {
        $pathMap = [
            'property' => [
                'path' => '/$prop',
                'isProp' => true,
                'modelClass' => Survey::class
            ],
            'defaultLanguage' => [
                'path' => '/defaultLanguage',
                'modelClass' => null // not supported
            ],
            'languages' => [
                'path' => '/languages',
                'modelClass' => null // not supported
            ],
            'language' => [
                'path' => '/languages/$id',
                'modelClass' => null // not supported
            ],
            'questionGroups' => [
                'path' => '/questionGroups',
                'isCollection' => true,
                'modelClass' => QuestionGroup::class
            ],
            'questionGroup' => [
                'path' => '/questionGroups/$id',
                'momodelClasse' => QuestionGroup::class
            ],
            'questionGroupL10ns' => [
                'path' => '/questionGroups/$questionGroupId/l10ns',
                'isCollection' => true,
                'modelClass' => QuestionGroupL10n::class
            ],
            'questionGroupL10nsLang' => [
                'path' => '/questionGroups/$questionGroupId/l10ns/$id',
                'modelClass' => QuestionGroupL10n::class
            ],

            'questions' => [
                'path' => '/questionGroups/$questionGroupId/questions',
                'isCollection' => true,
                'modelClass' => Question::class
            ],
            'question' => [
                'path' => '/questionGroups/$questionGroupId/questions/$id',
                'modelClass' => Question::class
            ],
            'questionL10ns' => [
                'path' => '/questionGroups/$questionGroupId/questions/$id/l10ns',
                'isCollection' => true,
                'modelClass' => QuestionL10n::class
            ],
            'questionL10nsLang' => [
                'path' => '/questionGroups/$questionGroupId/questions/$questionId/l10ns/$id',
                'modelClass' => QuestionL10n::class
            ],
            'questionAttributes' => [
                'path' => '/questionGroups/$questionGroupId/questions/$id/attributes',
                'isCollection' => true,
                'modelClass' => QuestionAttribute::class
            ],
            'questionAttribute' => [
                'path' =>'/questionGroups/$questionGroupId/questions/$questionId/attributes/$id',
                'modelClass' => QuestionAttribute::class
            ],

            'subquestions' => [
                'path' => '/questionGroups/$questionGroupId/questions/$id/subquestions',
                'isCollection' => true,
                'modelClass' => Question::class
            ],
            'subquestion' => [
                'path' => '/questionGroups/$questionGroupId/questions/$questionId'
                    . '/subquestions/$id',
                'modelClass' => Question::class
            ],
            'subquestionL10ns' => [
                'path' => '/questionGroups/$questionGroupId/questions/$questionId'
                    . '/subquestions/$id/l10ns',
                'isCollection' => true,
                'modelClass' => QuestionL10n::class
            ],
            'subquestionL10nsLang' => [
                'path' => '/questionGroups/$questionGroupId/questions/$questionId'
                    . '/subquestions/$subquestionId/l10ns/$id',
                'modelClass' => QuestionL10n::class
            ],
            'subquestionAttributes' => [
                'path' => '/questionGroups/$questionGroupId/questions/$questionId'
                    . '/subquestions/$id/attributes',
                'isCollection' => true,
                'modelClass' => QuestionAttribute::class
            ],
            'subquestionAttribute' => [
                'path' => '/questionGroups/$questionGroupId/questions/$questionId'
                    . '/subquestions/$subquestionId/attributes/$id',
                'modelClass' => QuestionAttribute::class
            ]
        ];

        $result = null;
        foreach ($pathMap as $pathMapMeta) {
            $pathMapMeta['isCollection'] = !empty($pathMapMeta['isCollection']);
            $pathMapMeta['isProp'] = !empty($pathMapMeta['isProp']);

            $pattern = $this->getPatternForPath($pathMapMeta['path']);
            $matches = [];
            if (
                preg_match(
                '#^' . $pattern . '$#',
                $patch['path'],
                $matches
                ) === 1
            ) {
                $result = [$pathMapMeta, $matches];
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
        if (!array_key_exists('value', $patch)) {
            $errors[] = 'No value set';
        }
        return empty($errors) ?: $errors;
    }
}
