<?php
/**
 * This file is part of statFunctions plugin
 */
namespace statFunctions;

use Yii;
use CHtml;
use LimeExpressionManager;
use Survey;
use SurveyDynamic;
use CDbCriteria;
use Permission;

class countFunctions
{
    /**
     * Return the count of reponse on current Expression Manager survey equal to a specific value
     * @param string $qCode : code of question, currently must be existing sgqa. Sample Q01.sgqa.
     * @param string $comparaison : comparre with value. Can use < or > … see https://www.yiiframework.com/doc/api/1.1/CDbCriteria#compare-detail
     * @param boolean $submitted (or not) response
     * @return integer|string
     */
    public static function statCountIf($qCode, $comparaison, $submitted = true)
    {
        $surveyId = LimeExpressionManager::getLEMsurveyId();
        $checkSurveyId = self::_checkSurveyId($surveyId);
        if (!is_null($checkSurveyId)) {
            return $checkSurveyId;
        }
        $column = self::_getColumnByQCode($surveyId, $qCode);
        if (is_null($column)) {
            if (Permission::model()->hasSurveyPermission($surveyId, 'surveycontent')) { // update ???
                return sprintf(gT("Invalid question code %s"), CHtml::encode($qCode));
            }
            return "";
        }
        $sQuotedColumn=Yii::app()->db->quoteColumnName($column);
        $oCriteria = new CDbCriteria;
        $oCriteria->condition= "$sQuotedColumn IS NOT NULL";
        if ($submitted) {
            $oCriteria->addCondition("submitdate IS NOT NULL");
        }
        $oCriteria->compare($sQuotedColumn, $comparaison);
        return intval(SurveyDynamic::model($surveyId)->count($oCriteria));
    }

    /**
     * Return the count of reponse on current Expression Manager survey equal to a specific value
     * @param string $qCode : code of question, currently must be existing sgqa. Sample Q01.sgqa.
     * @param boolean $submitted (or not)  response
     * @return integer|string
     */
    public static function statCount($qCode, $submitted = true)
    {
        $surveyId = LimeExpressionManager::getLEMsurveyId();
        $checkSurveyId = self::_checkSurveyId($surveyId);
        if (!is_null($checkSurveyId)) {
            return $checkSurveyId;
        }
        $column = self::_getColumnByQCode($surveyId, $qCode);
        if (is_null($column)) {
            if (Permission::model()->hasSurveyPermission($surveyId, 'surveycontent')) { // update ???
                return sprintf(gT("Invalid question code %s"), CHtml::encode($qCode));
            }
            return "";
        }

        $sQuotedColumn=Yii::app()->db->quoteColumnName($column);
        $oCriteria = new CDbCriteria;
        $oCriteria->condition= "$sQuotedColumn IS NOT NULL and $sQuotedColumn <> ''";
        if ($submitted) {
            $oCriteria->addCondition("submitdate IS NOT NULL");
        }
        return intval(SurveyDynamic::model($surveyId)->count($oCriteria));
    }

    /**
     * Check the survey
     * @param $surveyId
     * @return integer|string|null : return 0 or string : value to be directly shown, null : final value must be evaluated
     */
    private static function _checkSurveyId($surveyId)
    {
        $oSurvey = Survey::model()->findByPk($surveyId);
        if (!$oSurvey) {
            return "Invalid survey"; // Can not happen … (hope)
        }
        if (!$oSurvey->getIsActive()) {
            return 0;
        }
    }

    /**
     * Get the column name from a code.
     * @param integer $surveyId
     * @param string $qCode
     * @return null|string : null mean an invalid column/code, string is the column in reponse database, null if not found
     */
    private static function _getColumnByQCode($surveyId, $qCode)
    {
        $availableColumns = SurveyDynamic::model($surveyId)->getAttributes();
        /* Sample : Q01.sgqa Q01_SQ01.sgqa */
        if (array_key_exists($qCode, $availableColumns)) {
            return $qCode;
        }
        /* @todo : allow "Q0" and "Q0_SQ0" …
         * But without using LimeExpressionManager::ProcessString or LimeExpressionManager::getLEMqcode2sgqa
         * Because break logic file
         */
        return null;
    }
}
