<?php

/**
 * This file is part of statFunctions plugin
 */

namespace statFunctions;

use Yii;
use Survey;
use SurveyDynamic;
use CDbCriteria;

class questionCodeHelper
{
    /** @var integer $surveyId **/
    public $surveyId = 0;

    /**
     * @param integer $surveyId
     */
    public function __construct($surveyId)
    {
        $this->surveyId = $surveyId;
        /* Throw error if surveyid is invalid ? */
    }

    /**
     * Check the survey
     * @param string $qCode question SGQA
     * @return string|null : the final column name, null if not found
     */
    public function getColumnByQCode($qCode)
    {
        $availableColumns = SurveyDynamic::model($this->surveyId)->getAttributes();
        /* Sample : Q01.sgqa Q01_SQ01.sgqa */
        if (array_key_exists($qCode, $availableColumns)) {
            return $qCode;
        }

        /* @todo : allow "Q0" and "Q0_SQ0" …
         * But without using LimeExpressionManager::ProcessString or LimeExpressionManager::getLEMqcode2sgqa
         * Because break logic file
         * Wait for OK to merge to start it …
         */
        return null;
    }
}
