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
use LimeSurvey\PluginManager\LimesurveyApi as LimesurveyApi;

class countFunctions
{
    /**
     * Return the count of response on current ExpressionScript Engine survey equal to a specific value
     * @param string $qCode : code of question, currently must be existing sgqa. Sample Q01.sgqa.
     * @param string $comparaison : comparre with value. Can use < or > … see https://www.yiiframework.com/doc/api/1.1/CDbCriteria#compare-detail
     * @param boolean $submitted (or not) response
     * @param boolean $self include (or not) current response
     * @return integer|string
     */
    public static function statCountIf($qCode, $comparaison, $submitted = true, $self = true)
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
        $oCriteria = new CDbCriteria();
        $oCriteria->condition = "$sQuotedColumn IS NOT NULL";
        if ($submitted) {
            $oCriteria->addCondition("submitdate IS NOT NULL");
        }
        if (!$self && isset($_SESSION['responses_' . $surveyId]['srid'])) {
            $srid = $_SESSION['responses_' . $surveyId]['srid'];
            $oCriteria->compare("id", "<>" . $srid);
        }
        /* Check if question is encrypted */
        $encrypted = false;
        if (isset($_SESSION['responses_' . $surveyId]['fieldmap'])) {
            $fieldmap = $_SESSION['responses_' . $surveyId]['fieldmap'];
            $encrypted = isset($fieldmap[$column]['encrypted']) && $fieldmap[$column]['encrypted'] == 'Y';
            if ($encrypted) {
                $surveyEncryptionmethod = Survey::model()->findByPk($surveyId)->oOptions->encryption_method;
                if ($surveyEncryptionmethod == 'H') {
                    return self::setErrorText($surveyId, sprintf(gT("Question code %s is crypted, unable to get statistics with hardened encryption method."), CHtml::encode($qCode)));
                }
                /* Unable to compare with <, > (and <=, >=), but allow <> */
                if (str_starts_with($comparaison, '>') ||(str_starts_with($comparaison, '<') && !str_starts_with($comparaison, '<>'))) {
                    return self::setErrorText($surveyId, sprintf(gT("Question code %s is crypted, unable to get statistics with comparisons."), CHtml::encode($qCode)));
                }
                /* Encrypt the value and keep <> and = operator */
                if (preg_match('/^\s*(<>|=)?(.*)$/', $comparaison, $matches)) {
                    $op = $matches[1];
                    $comparaison = $matches[2];
                } else {
                    $op = "";
                }
                $comparaison = $op . LSActiveRecord::encryptSingle($comparaison, $surveyEncryptionmethod); // $surveyEncryptionmethod is B currently
            }
        }
        $oCriteria->compare($sQuotedColumn, $comparaison);
        return intval(SurveyDynamic::model($surveyId)->count($oCriteria));
    }

    /**
     * Return the text of user have suretycontent permission, else empty string
     * @param integer $surveyId
     * @param string $errorText
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

        $sCastedColumn = $sQuotedColumn = Yii::app()->db->quoteColumnName($column);
        if (Yii::app()->db->driverName == 'pgsql') {
            $sCastedColumn = "CAST($sQuotedColumn as text)";
        }
        $oCriteria = new CDbCriteria();
        $oCriteria->condition = "$sQuotedColumn IS NOT NULL and $sCastedColumn <> ''";
        if ($submitted) {
            $oCriteria->addCondition("submitdate IS NOT NULL");
        }
        if (!$self && isset($_SESSION['responses_' . $surveyId]['srid'])) {
            $srid = $_SESSION['responses_' . $surveyId]['srid'];
            $oCriteria->compare("id", "<>" . $srid);
        }
        return intval(SurveyDynamic::model($surveyId)->count($oCriteria));
    }
}
