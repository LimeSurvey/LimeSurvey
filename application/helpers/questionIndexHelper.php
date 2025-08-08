<?php

/**
 * Do the index : group by group or question by question
 *
 * @copyright 2016 LimeSurvey <http://www.limesurvey.org>
 * @license GPL v3
 * @version 0.0.2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

namespace LimeSurvey\Helpers;

use Yii;
use LimeExpressionManager;

class questionIndexHelper
{

    /**
     * Singleton
     * @var questionIndexHelper
     */
    private static $instance = null;

    /**
     * Actual survey ID
     * @var int surveyid
     */
    private $iSurveyId;

    /**
     * Index type : 0 : none, 1 incremental, 2 full
     * @var int indexType
     */
    private $indexType;
    /**
     * Survey format : A : all in one, G: Group, S: questions
     * @var string surveyFormat
     */
    private $surveyFormat;

    /**
     * Indexed actual items,
     * @var array[]
     */
    private $indexItems;

    /**
     * Set the surveyid when construct
     */
    private function __construct($iSurveyId)
    {
        /* Not needed actually : we have only one survey at a time */
        //~ if(isset($this->indexItems) && $this->iSurveyId!=LimeExpressionManager::getLEMsurveyId()){
            //~ $this->indexItems=null;
        //~ }
        /* Put all in contruct variable ? */
        $this->iSurveyId = $iSurveyId;
        $oSurvey = \Survey::model()->findByPk($this->iSurveyId);
        if ($oSurvey) {
            $this->indexType = $oSurvey->aOptions['questionindex'];
            $this->surveyFormat = $oSurvey->aOptions['format'];
        } else {
            $this->indexType = 0;
            $this->surveyFormat = null;
        }
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new questionIndexHelper(LimeExpressionManager::getLEMsurveyId());
        }

        return self::$instance;
    }

    /**
     * Get the array of all step for this session
     *
     * @return array
     */
    public function getIndexItems()
    {
        /* Must add control on $this->getStepInfo */
        //~ if(is_array($this->indexItems)){
            //~ return $this->indexItems;
        //~ }
        if (!$this->indexType) {
            return array();
        }
        /* @todo Find if we where in preview mode : deactivate index or not set if preview */
        switch ($this->surveyFormat) {
            case 'A': //All in one : no indexItems
                $this->indexItems = array();
                return $this->indexItems;
            case 'G': //Group at a time
                $this->indexItems = $this->getIndexItemsGroups($this->indexType);
                return $this->indexItems;
            case 'S': //Question at a time
                $this->indexItems = $this->getIndexItemsQuestions();
                return $this->indexItems;
            default:
        }
    }

    /**
     * return the index item in goup by group mode
     * @param integer $type : 0 : None , 1 : Incremental, 2: full
     */
    private function getIndexItemsGroups($type)
    {
        if (!$type) {
            return array();
        }
        $sessionLem = Yii::app()->session["responses_{$this->iSurveyId}"];
        if (empty($sessionLem['grouplist'])) {
            return array();
        }
        /* get the step info from LEM : for already seen group : give if error/show and answered ...*/
        $stepInfos = LimeExpressionManager::GetStepIndexInfo();
        $stepIndex = array();
        foreach ($sessionLem['grouplist'] as $step => $groupInfo) {
            /* EM step start at 1, we start at 0*/
            $groupInfo['step'] = $step + 1;
            $stepInfo = $stepInfos[$step] ?? array('show' => true, 'anyUnanswered' => null, 'anyErrors' => null);
            if (
                ($type > 1 || $groupInfo['step'] <= $sessionLem['maxstep'])
                && LimeExpressionManager::GroupIsRelevant($groupInfo['gid']) // $stepInfo['show'] is incomplete (for irrelevant group after the 'not submitted due to error group') GroupIsRelevant control it really
            ) {
                /* string to EM : leave fix (remove script , flatten other ...) to view */
                $stepIndex[$step] = array(
                    'gid' => $groupInfo['gid'],
                    'text' => LimeExpressionManager::ProcessString($groupInfo['group_name']),
                    'description' => LimeExpressionManager::ProcessString($groupInfo['description']),
                    'step' => $groupInfo['step'],
                    'url' => Yii::app()->getController()->createUrl("survey/index", array('sid' => $this->iSurveyId, 'move' => $groupInfo['step'])),
                    'submit' => ls_json_encode(array('move' => $groupInfo['step'])),
                    'stepStatus' => array(
                        'index-item-before' => ($groupInfo['step'] < $sessionLem['step']), /* did we need a before ? seen seems better */
                        'index-item-seen' => ($groupInfo['step'] <= $sessionLem['maxstep']),
                        'index-item-unanswered' => $stepInfo['anyUnanswered'],
                        'index-item-error' => $stepInfo['anyErrors'],
                        'index-item-current' => ($groupInfo['step'] == $sessionLem['step']),
                    ), /* order have importance for css : last on is apply */
                );
                $stepIndex[$step]['stepStatus']['index-item-error'] = $stepInfo['anyErrors'];
                $stepIndex[$step]['stepStatus']['index-item-unanswered'] = $stepInfo['anyUnanswered'];
                $aClass = array_filter($stepIndex[$step]['stepStatus']);
                $stepIndex[$step]['coreClass'] = implode(" ", array_merge(array('index-item'), array_keys($aClass)));
            }
        }
        return $stepIndex;
    }

    /**
     * return the index item in question by question mode
     * @return array[][] : array of question in array of group
     */
    private function getIndexItemsQuestions()
    {
        $sessionLem = Yii::app()->session["responses_{$this->iSurveyId}"];
        /* get field map : have more info*/
        /* get group list : for information about group ...*/
        $groupList = $sessionLem['grouplist'];
        /* get the step infor from LEM : for alreay seen questin : give if error/show and answered ...*/
        $stepInfos = LimeExpressionManager::GetStepIndexInfo();

        /* The final step index to return */
        $stepIndex = array();
        $prevStep = -1;
        $prevGroupSeq = -1;
        foreach ($sessionLem['fieldmap'] as $step => $questionFieldmap) {
            if (isset($questionFieldmap['questionSeq']) && $questionFieldmap['questionSeq'] != $prevStep) {
// Sub question have same questionSeq, and no questionSeq : must be hidden (lastpage, id, seed ...)
                /* This question can be in index */
                $questionStep = $questionFieldmap['questionSeq'] + 1;
                $stepInfo = $stepInfos[$questionFieldmap['questionSeq']] ?? array('show' => true, 'anyUnanswered' => null, 'anyErrors' => null);
                if (
                    ($questionStep <= $sessionLem['maxstep']) // || $type>1 : index can be shown : but next step is disable somewhere
                    && $stepInfo['show']// attribute hidden + relevance : @todo review EM function ?
                ) {
                    /* Control if we are in a new group : always true at first question*/
                    //$GroupId=(isset($questionFieldmap['random_gid']) && $questionFieldmap['random_gid']) ? $questionFieldmap['random_gid'] : $questionFieldmap['gid'];
                    if ($questionFieldmap['groupSeq'] != $prevGroupSeq) {
                        // add the previous group if it's not empty (all question hidden etc ....)
                        if (!empty($questionInGroup)) {
                            $actualGroup['questions'] = $questionInGroup;
                            $stepIndex[] = $actualGroup;
                        }
                        //add the group
                        $groupInfo = $groupList[$questionFieldmap['groupSeq']];
                        $actualGroup = array(
                            'gid' => $groupInfo['gid'],
                            'text' => LimeExpressionManager::ProcessString($groupInfo['group_name']),
                            'description' => LimeExpressionManager::ProcessString($groupInfo['description']),
                        );
                        /* The 'show' question in this group */
                        $questionInGroup = array();
                        $prevGroupSeq = $questionFieldmap['groupSeq'];
                    }
                    $questionInfo = array(
                        'qid' => $questionFieldmap['qid'],
                        'code' => $questionFieldmap['title'], /* @todo : If survey us set to show question code : we must show it */
                        'text' => LimeExpressionManager::ProcessString($questionFieldmap['question']),
                        'step' => $questionStep,
                        'url' => Yii::app()->getController()->createUrl("survey/index", array('sid' => $this->iSurveyId, 'move' => $questionStep)),
                        'submit' => ls_json_encode(array('move' => $questionStep)),
                        'stepStatus' => array(
                            'index-item-before' => ($questionStep < $sessionLem['step']), /* did we need a before ? seen seems better */
                            'index-item-seen' => ($questionStep <= $sessionLem['maxstep']),
                            'index-item-unanswered' => $stepInfo['anyUnanswered'],
                            'index-item-error' => $stepInfo['anyErrors'],
                            'index-item-current' => ($questionStep == $sessionLem['step']),
                            ), /* order have importance for css : last on is apply */
                    );
                    $aClass = array_filter($questionInfo['stepStatus']);
                    $questionInfo['coreClass'] = implode(" ", array_merge(array('index-item'), array_keys($aClass)));
                    $questionInGroup[$questionStep] = $questionInfo;
                }
                /* Update the previous step */
                $prevStep = $questionFieldmap['questionSeq'];
            }
        }
        /* Add the last group */
        if (!empty($questionInGroup)) {
            $actualGroup['questions'] = $questionInGroup;
            $stepIndex[] = $actualGroup;
        }
        return $stepIndex;
    }
}
