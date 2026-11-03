<?php

/**
 * This file is part of statFunctions plugin
 * @version 0.3.0
 */

namespace statFunctions;

use Yii;
use CHtml;
use LSActiveRecord;
use LimeExpressionManager;
use Survey;
use SurveyDynamic;
use CDbCriteria;
use Permission;
use LimeSurvey\Models\Services\SurveySessionState;
use LimeSurvey\PluginManager\LimesurveyApi as LimesurveyApi;

class countFunctions
{
    /**
     * Return the count of response on current ExpressionScript Engine survey equal to a specific value
     * @param string $qCode : code of question, currently must be existing sgqa. Sample Q01.sgqa.
     * @param string $comparison : compare with value. Can use < or > … see https://www.yiiframework.com/doc/api/1.1/CDbCriteria#compare-detail
     * @param boolean $submitted (or not) response
     * @param boolean $self include (or not) current response
     * @return integer|string
     */
    public static function statCountIf($qCode, $comparison, $submitted = true, $self = true)
    {
        $api = new LimesurveyApi();
        $surveyId = $api->getCurrentSurveyid(true);
        if (!$surveyId) {
            return 0;
        }
        $questionCodeHelper = new \statFunctions\questionCodeHelper($surveyId);
        $column = $questionCodeHelper->getColumnByQCode($qCode);
        if (is_null($column)) {
            return self::setErrorText($surveyId, sprintf(gT("Invalid question code %s."), CHtml::encode($qCode)));
        }
        $sQuotedColumn = Yii::app()->db->quoteColumnName($column);
        $sComparedColumn = $sQuotedColumn;
        // A value that is not a number (after the optional operator, see CDbCriteria::compare()) is compared as text
        preg_match('/^(?:\s*(?:<>|<=|>=|<|>|=))?(.*)$/s', (string) $comparison, $matches);
        if (!is_numeric(trim($matches[1]))) {
            $sComparedColumn = self::castToText($sQuotedColumn);
        }
        $oCriteria = new CDbCriteria();
        $oCriteria->condition = "$sQuotedColumn IS NOT NULL";
        if ($submitted) {
            $oCriteria->addCondition("submitdate IS NOT NULL");
        }
        $sessionState = SurveySessionState::forSurvey((int) $surveyId);
        $srid = $sessionState->getResponseId();
        if (!$self && $srid !== null) {
            $oCriteria->compare("id", "<>" . $srid);
        }
        /* Check if question is encrypted */
        if ($sessionState->hasFieldMap()) {
            $fieldmap = $sessionState->getFieldMap();
        } else {
            /* No survey session (e.g. survey logic file): Use the fieldmap of the survey */
            $survey = Survey::model()->findByPk($surveyId);
            $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);
        }
        $encrypted = isset($fieldmap[$column]['encrypted']) && $fieldmap[$column]['encrypted'] == 'Y';
        if ($encrypted) {
            $surveyEncryptionmethod = Survey::model()->findByPk($surveyId)->oOptions->encryption_method;
            if ($surveyEncryptionmethod == 'H') {
                return self::setErrorText($surveyId, sprintf(gT("Question code %s is encrypted, unable to get statistics with hardened encryption method."), CHtml::encode($qCode)));
            }
            /* Get the operator with the same rules than CDbCriteria::compare */
            $op = "";
            $value = (string) $comparison;
            if (preg_match('/^(?:\s*(<>|<=|>=|<|>|=))?(.*)$/', $value, $matches)) {
                $op = $matches[1];
                $value = $matches[2];
            }
            /* Unable to compare with <, > (and <=, >=), but allow <> and = */
            if (in_array($op, ['<', '>', '<=', '>='])) {
                return self::setErrorText($surveyId, sprintf(gT("Question code %s is encrypted, unable to get statistics with comparisons."), CHtml::encode($qCode)));
            }
            /* Encrypt only the value and keep the operator */
            $comparison = $op . LSActiveRecord::encryptSingle($value, $surveyEncryptionmethod); // $surveyEncryptionmethod is B currently
        }
        $oCriteria->compare($sComparedColumn, $comparison);
        return intval(SurveyDynamic::model($surveyId)->count($oCriteria));
    }

    /**
     * Return the text of user have suretycontent permission, else empty string
     * @param integer $surveyId
     * @param string $string
     * @return string
     */
    private static function setErrorText($surveyId, $string)
    {
        if (Permission::model()->hasSurveyPermission($surveyId, 'surveycontent')) {
            return $string;
        }
        return "";
    }

    /**
     * Return the count of response on current ExpressionScript Engine survey equal to a specific value
     * @param string $qCode : code of question, currently must be existing sgqa. Sample Q01.sgqa.
     * @param boolean $submitted (or not)  response
     * @param boolean $self include (or not) current response
     * @return integer|string
     */
    public static function statCount($qCode, $submitted = true, $self = true)
    {
        $api = new LimesurveyApi();
        $surveyId = $api->getCurrentSurveyid(true);
        if (!$surveyId) {
            return 0;
        }
        $questionCodeHelper = new \statFunctions\questionCodeHelper($surveyId);
        $column = $questionCodeHelper->getColumnByQCode($qCode);
        if (is_null($column)) {
            if (Permission::model()->hasSurveyPermission($surveyId, 'surveycontent')) { // update ???
                return sprintf(gT("Invalid question code %s"), CHtml::encode($qCode));
            }
            return "";
        }

        $sQuotedColumn = Yii::app()->db->quoteColumnName($column);
        $sCastedColumn = self::castToText($sQuotedColumn);
        $oCriteria = new CDbCriteria();
        $oCriteria->condition = "$sQuotedColumn IS NOT NULL and $sCastedColumn <> ''";
        if ($submitted) {
            $oCriteria->addCondition("submitdate IS NOT NULL");
        }
        $srid = SurveySessionState::forSurvey((int) $surveyId)->getResponseId();
        if (!$self && $srid !== null) {
            $oCriteria->compare("id", "<>" . $srid);
        }
        return intval(SurveyDynamic::model($surveyId)->count($oCriteria));
    }

    /**
     * Returns the SQL expression that casts a response column to text, so it can be compared with text
     * on every database: PostgreSQL and MSSQL fail when comparing a number or a date with text,
     * MySQL converts the text to a number instead (so '' or 'NOT' would match 0).
     * @param string $quotedColumn The quoted column name
     * @return string
     */
    private static function castToText($quotedColumn)
    {
        switch (Yii::app()->db->driverName) {
            case 'pgsql':
                return "CAST($quotedColumn AS text)";
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
                return "CAST($quotedColumn AS NVARCHAR(MAX))";
            default:
                return "CAST($quotedColumn AS CHAR)";
        }
    }
}
