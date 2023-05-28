<?php

namespace LimeSurvey\Models\Services;

use SurveyURLParameter;
use LimeSurvey\Models\Services\Exception\{
    ExceptionPersistError,
    ExceptionNotFound,
    ExceptionPermissionDenied
};

/**
 * Service SurveyUpdaterUrlParams
 *
 * Dependencies are injected to enable mocking.
 */
class SurveyUpdaterUrlParams
{
    private ?SurveyURLParameter $modelSurveyUrlParameter = null;

    public function __construct(SurveyURLParameter $modelSurveyUrlParameter)
    {
        $this->modelSurveyUrlParameter = $modelSurveyUrlParameter;
    }

    /**
     * Update
     *
     * @param int $surveyId
     * @param array $params
     * @throws ExceptionPersistError
     * @throws ExceptionNotFound
     * @throws ExceptionPermissionDenied
     * @return boolean
     */
    public function update($surveyId, $params)
    {
        $params = is_array($params) && !empty($params)
            ? $params
            : [];

        $this->modelSurveyUrlParameter
            ->deleteAllByAttributes(
                array('sid' => $surveyId)
            );

        foreach ($params as $param) {
            $param['parameter'] = trim((string) $param['parameter']);
            if (
                $param['parameter'] == ''
                || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $param['parameter'])
                || $param['parameter'] == 'sid'
                || $param['parameter'] == 'newtest'
                || $param['parameter'] == 'token'
                || $param['parameter'] == 'lang'
            ) {
                continue; // this parameter name seems to be invalid - just ignore it
            }
            $param['targetqid'] = $param['qid'];
            $param['targetsqid'] = $param['sqid'];
            unset($param['actionBtn']);
            unset($param['title']);
            unset($param['id']);
            unset($param['qid']);
            unset($param['targetQuestionText']);
            unset($param['sqid']);
            if ($param['targetqid'] == '') {
                $param['targetqid'] = null;
            }
            if ($param['targetsqid'] == '') {
                $param['targetsqid'] = null;
            }
            $param['sid'] = $surveyId;

            $urlParam = $this->modelSurveyUrlParameter->create();
            foreach ($param as $k => $v) {
                $urlParam->$k = $v;
            }
            $urlParam->save();
        }
    }
}
