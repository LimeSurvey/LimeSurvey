<?php

namespace LimeSurvey\Models\Services\EditorService;

use CHttpRequest;
use Yii;

class EditorRequestHelper
{
    /**
     * Find survey id in the current request
     *
     * There is inconsistency in how survey id is specified in a request
     * this method finds the survey id in the current request when
     * we dont know in advance how the survey id was specified.
     *
     * The long term solution for this is to ensure the application
     * alwatys uses the same format for specifying survey id.
     *
     * @param CHttpRequest ?$request
     * @return string|null
     */
    public static function findSurveyId(CHttpRequest $request = null)
    {
        $request = $request ? $request : Yii::app()->request;

        $possibleSurveyIdValues = [
            $request->getParam('sid'),
            $request->getParam('surveyId'),
            $request->getParam('surveyid'),
            $request->getParam('iSurveyId'),
            $request->getParam('isurveyid'),
            $request->getParam('iSurveyID')
        ];

        $surveyId = null;
        foreach ($possibleSurveyIdValues as $value) {
            $sanitizedValue = sanitize_int($value);
            if ($sanitizedValue == '0' || !empty($sanitizedValue)) {
                $surveyId = $sanitizedValue;
                break;
            }
        }
        return $surveyId;
    }

    /**
     * Find question id in the current request
     *
     * Searches for the question id across different parameter names
     * due to inconsistency in how question id is specified in requests.
     *
     * @param CHttpRequest|null $request The HTTP request object. If null, uses the application's current request.
     * @return string|null The question ID if found in the request, null otherwise.
     */
    public static function findQuestionId(CHttpRequest $request = null)
    {
        $request = $request ? $request : Yii::app()->request;

        $possibleQuestionIdValues = [
            $request->getParam('qid'),
            $request->getParam('questionId'),
        ];

        $questionId = null;
        foreach ($possibleQuestionIdValues as $value) {
            $sanitizedValue = sanitize_int($value);
            if ($sanitizedValue == '0' || !empty($sanitizedValue)) {
                $questionId = $sanitizedValue;
                break;
            }
        }
        return $questionId;
    }

    /**
     * Find question group id in the current request
     *
     * Searches for the question group id across different parameter names
     * due to inconsistency in how question group id is specified in requests.
     *
     * @param CHttpRequest|null $request The HTTP request object. If null, uses the application's current request.
     * @return string|null The question group ID if found in the request, null otherwise.
     */
    public static function findQuestionGroupId(CHttpRequest $request = null)
    {
        $request = $request ? $request : Yii::app()->request;

        $possibleQuestionGroupIdValues = [
            $request->getParam('gid'),
            $request->getParam('groupId'),
        ];

        $questionGroupId = null;
        foreach ($possibleQuestionGroupIdValues as $value) {
            $sanitizedValue = sanitize_int($value);
            if ($sanitizedValue == '0' || !empty($sanitizedValue)) {
                $questionGroupId = $sanitizedValue;
                break;
            }
        }
        return $questionGroupId;
    }
}
