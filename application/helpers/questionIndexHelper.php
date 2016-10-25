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
//~ namespace ls\helpers;
//~ use Yii;
//~ use LimeExpressionManager;
//~ use viewHelper;

class questionIndexHelper {
    /**
     * Actual survey id
     * @var int surveyid
     */
    private $iSurveyId;

    /**
     * Indexed actual items,
     * @var array[]
     */
    private $indexItems;

    /**
     * Add the step information to the indexed items from ExpressionManager
     * Actually get the step information set the value of current step, can be done after done the current page, not before
     * @var boolean
     */
    public $getStepInfo=false;

    /**
     * Set the surveyid when construct
     */
    public function __construct()
    {
        /* Not needed actually : we have only one survey at a time */
        //~ if(isset($this->indexItems) && $this->iSurveyId!=LimeExpressionManager::getLEMsurveyId()){
            //~ $this->indexItems=null;
        //~ }
        $this->iSurveyId=LimeExpressionManager::getLEMsurveyId();
    }

    /**
     * Get the array of all step for this session
     *
     * @return array
     */
    public function getIndexItems()
    {
        //~ if(is_array($this->indexItems)){
            //~ return $this->indexItems;
        //~ }
        $oSurvey=\Survey::model()->findByPk($this->iSurveyId);
        /* No survey => no index, don't set indexItems, maybe we where in survey after */
        if(!$oSurvey){
            return array();
        }
        /* @todo Find if we where in preview mode : deactivate index or not set if preview */

        $this->indexType=$oSurvey->questionindex;
        switch ($oSurvey->format){
            case 'A': //All in one : no indexItems
                $this->indexItems=array();
                return $this->indexItems;
            case 'G': //Group at a time
                $this->indexItems=$this->getIndexItemsGroups($this->indexType);
                return $this->indexItems;
            case 'Q': //Group at a time
                $this->indexItems=$this->getIndexItemsQuestions($this->indexType);
                return $this->indexItems;
            default:
        }
    }

    private function getIndexStep()
    {
        $indexItems=$this->getIndexItems();
    }
    /**
     * return the index item in goup by group mode
     * @var integer $type : 0 : None , 1 : Incremental, 2: full
     */
    private function getIndexItemsGroups($type)
    {
        if(!$type){
            return array();
        }
        $sessionLem=Yii::app()->session["survey_{$this->iSurveyId}"];
        if(empty($sessionLem['grouplist'])){
            return array();
        }
        $stepIndex=array();
        foreach($sessionLem['grouplist'] as $step=>$groupInfo)
        {
            $groupInfo['step'] = $step + 1; /* We don't have a step if group is not relevant ? */
            if( ($type>1 || $groupInfo['step'] <= $sessionLem['maxstep']) // type==1 : incremental : must control step (start at or -1 ?)
                && LimeExpressionManager::GroupIsRelevant($groupInfo['gid'])
            ){
                $stepIndex[$step]=array(
                    'gid'=>$groupInfo['gid'],
                    'text'=>$groupInfo['group_name'],
                    'description'=>$groupInfo['description'],
                    'step'=>$groupInfo['step'],
                    'url'=>Yii::app()->getController()->createUrl("survey/index",array('sid'=>$this->iSurveyId,'move'=>$groupInfo['step'])),
                    'submit'=>ls_json_encode(array('move'=>$groupInfo['step'])),
                );
                if($this->getStepInfo){
                    /* Get the current group info */
                    if ($groupInfo['step'] <= $sessionLem['maxstep'] && $groupInfo['step'] != $sessionLem['step']){
                        /* @todo test until maxstep, but without try to submit */
                        $stepInfo = LimeExpressionManager::singleton()->_ValidateGroup($step);// Danger: Update the actual group, do it only after display all question in the page
                        $stepIndex[$step]['stepStatus']=array(
                            'has-error'      => (bool) ($stepInfo['mandViolation'] || !$stepInfo['valid']),
                            'has-unanswered' => (bool) $stepInfo['anyUnanswered'],
                            'is-before'      => true,
                            'is-current'     => false,
                        );
                    }elseif($groupInfo['step'] == $sessionLem['step']){
                        $stepIndex[$step]['stepStatus']=array(
                            'has-error'      => false,
                            'has-unanswered' => false,
                            'is-before'      => false,
                            'is-current'     => true,
                        );
                    }else{
                        $stepIndex[$step]['stepStatus']=array(
                            'has-error'      => false,
                            'has-unanswered' => false,
                            'is-before'      => false,
                            'is-current'     => false,
                        );
                    }
                }else{
                    if($groupInfo['step'] < $sessionLem['step']){
                        $stepIndex[$step]['stepStatus']=array(
                            'has-error'      => null,
                            'has-unanswered' => null,
                            'is-before'      => true,
                            'is-current'     => false,
                        );
                    }elseif($groupInfo['step'] == $sessionLem['step']){
                        $stepIndex[$step]['stepStatus']=array(
                            'has-error'      => null,
                            'has-unanswered' => null,
                            'is-before'      => false,
                            'is-current'     => true,
                        );
                    }else{
                        $stepIndex[$step]['stepStatus']=array(
                            'has-error'      => null,
                            'has-unanswered' => null,
                            'is-before'      => false,
                            'is-current'     => false,
                        );
                    }
                }
            }
        }
        return $stepIndex;
    }

    /**
     * return the index item in question by question mode
     * @var integer $type : 0 : None , 1 : Incremental, 2: full
     */
    private function getIndexItemsQuestions($type)
    {
        return array("I do it ...");
    }

    /**
     * Return html for list of link
     */
    public function getIndexLink()
    {
        $indexItems=$this->getIndexItems();
        if(!empty($indexItems)){
            Yii::app()->getClientScript()->registerScript("activateActionLink","activateActionLink();",\CClientScript::POS_END);
            return Yii::app()->getController()->renderPartial("/survey/system/surveyIndex/groupIndexMenuLink",array(
                'type'=>$this->indexType,
                'indexItems'=>$this->indexItems,
            ),true);
        }else{
            return '';
        }
    }

    /**
     * Return html for list of button
     */
    public function getIndexButton()
    {
        $indexItems=$this->getIndexItems();
        if(!empty($indexItems)){
            Yii::app()->getClientScript()->registerScript("manageIndex","manageIndex();",\CClientScript::POS_END);
            return Yii::app()->getController()->renderPartial("/survey/system/surveyIndex/groupIndex",array(
                'type'=>$this->indexType,
                'indexItems'=>$this->indexItems,
            ),true);
        }else{
            return '';
        }
    }
}
