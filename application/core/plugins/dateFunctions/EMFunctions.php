<?php

/**
 * This file is part of dateFunctions plugin
 * @version 1.0.0
 */

namespace dateFunctions;

use LimeExpressionManager;
use Survey;

class EMFunctions
{
    /**
     * Formats a date according to the Survey's date format for the specified language
     * @param string $date : a date in "Y-m-d H:i:s" format. Example: VALIDFROM.
     * @param string|null $language : the language used for localization. Defaults to current session language. If the current language is not configured in the survey, the survey base language will be used. When using in email templates, please use the token language as parameter as to set the expected language. Example: TOKEN:LANGUAGE
     * @return string
     */
    public static function localize_date($date, $language = null)
    {
        if (empty($date)) {
            return '';
        }

        // Try to get Survey ID from the EM
        $surveyId = LimeExpressionManager::getLEMsurveyId();

        // If it's not set, try to get it from the session
        if (empty($surveyId)) {
            $surveyId = \Yii::app()->session['LEMsid'];
        }
        if (empty($surveyId)) {
            return '';
        }

        $survey = Survey::model()->findByPk($surveyId);
        if (empty($survey)) {
            return '';
        }

        if (empty($language)) {
            $language = \Yii::app()->getLanguage();
        }

        // If the specified language is not one of the survey's languages, fallback to the survey's base language.
        if (!in_array($language, $survey->getAllLanguages()) || empty($survey->languagesettings[$language])) {
            $language = $survey->language;
        }

        $dateFormat = $survey->languagesettings[$language]->surveyls_dateformat;
        $dateFormatDetails = getDateFormatData($dateFormat);
        $datetimeobj = new \Date_Time_Converter($date, "Y-m-d H:i:s");
        return $datetimeobj->convert($dateFormatDetails['phpdate']);
    }
}
