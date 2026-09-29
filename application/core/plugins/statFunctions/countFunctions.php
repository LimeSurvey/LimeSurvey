<?php

/**
 * This file is part of statFunctions plugin
 * @version 0.2.1
 */

namespace statFunctions;

use Yii;
use CHtml;
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
            if (Permission::model()->hasSurveyPermission($surveyId, 'surveycontent')) { // update ???
                return sprintf(gT("Invalid question code %s"), CHtml::encode($qCode));
            }
            return "";
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
        $oCriteria->compare($sQuotedColumn, $comparaison);
        return intval(SurveyDynamic::model($surveyId)->count($oCriteria));
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
