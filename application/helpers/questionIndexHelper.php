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
     * Index type : 0 : none, 1 incremental, 2 full
     * @var int indexType
     */
    public $indexType;
    /**
     * Survey format : A : all in one, G: Group, S: questions
     * @var string surveyFormat
     */
    public $surveyFormat;

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
        /* Put all in contruct variable ? */
        $this->iSurveyId=LimeExpressionManager::getLEMsurveyId();
        $oSurvey=Survey::model()->findByPk($this->iSurveyId);
        if($oSurvey){
            $this->indexType=$oSurvey->questionindex;
            $this->surveyFormat=$oSurvey->format;
        }else{
            $this->indexType=0;
            $this->surveyFormat=null;
        }
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
        if(!$this->indexType){
            return array();
        }
        /* @todo Find if we where in preview mode : deactivate index or not set if preview */
        switch ($this->surveyFormat){
            case 'A': //All in one : no indexItems
                $this->indexItems=array();
                return $this->indexItems;
            case 'G': //Group at a time
                $this->indexItems=$this->getIndexItemsGroups($this->indexType);
                return $this->indexItems;
            case 'S': //Question at a time
                $this->indexItems=$this->getIndexItemsQuestions($this->indexType);
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
            /* EM step start at 1, we start at 0*/
            $groupInfo['step'] = $step + 1;
            if( ($type>1 || $groupInfo['step'] <= $sessionLem['maxstep'])  // type==1 : incremental : must control step
                && LimeExpressionManager::GroupIsRelevant($groupInfo['gid']) // always add it if unrelevant/hidden : only tested after when try to view it (in EM)
            ){
                /* string to EM : leave fix (remove script , flatten other ...) to view */
                $stepIndex[$step]=array(
                    'gid'=>$groupInfo['gid'],
                    'text'=>LimeExpressionManager::ProcessString($groupInfo['group_name']),
                    'description'=>LimeExpressionManager::ProcessString($groupInfo['description']),
                    'step'=>$groupInfo['step'],
                    'url'=>Yii::app()->getController()->createUrl("survey/index",array('sid'=>$this->iSurveyId,'move'=>$groupInfo['step'])),
                    'submit'=>ls_json_encode(array('move'=>$groupInfo['step'])),
                    'stepStatus'=>array(
                        'index-item-before' => ($groupInfo['step'] < $sessionLem['step']), /* did we need a before ? seen seems better */
                        'index-item-seen' => ($groupInfo['step'] <= $sessionLem['maxstep']),
                        'index-item-current' => ($groupInfo['step'] == $sessionLem['step']),
                    ),/* order have importance for css : last on is apply */
                );
                if($this->getStepInfo){
                    /* Get the current group info */
                    if ($groupInfo['step'] <= $sessionLem['maxstep'] && $groupInfo['step'] != $sessionLem['step']){
                        /* @todo test until maxstep, but without try to submit */
                        $stepInfo = LimeExpressionManager::singleton()->_ValidateGroup($step);// Danger: Update the actual group, do it only after display all question in the page
                        $stepIndex[$step]['stepStatus']['index-item-error'] = (bool) ($stepInfo['mandViolation'] || !$stepInfo['valid']);
                        $stepIndex[$step]['stepStatus']['index-item-unanswered'] = (bool) $stepInfo['anyUnanswered'];
                    }else{
                        $stepIndex[$step]['stepStatus']['index-item-error'] = null;
                        $stepIndex[$step]['stepStatus']['index-item-unanswered'] = null;
                    }
                }else{
                    $stepIndex[$step]['stepStatus']['index-item-error'] = null;
                    $stepIndex[$step]['stepStatus']['index-item-unanswered'] = null;
                }
                /* contruct coreClass */
                $aClass=array_filter($stepIndex[$step]['stepStatus']);
                $stepIndex[$step]['coreClass']=implode(" ",array_merge(array('index-item'),array_keys($aClass)));
            }
        }
        return $stepIndex;
    }

    /**
     * return the index item in question by question mode
     * @param integer $type : 0 : None , 1 : Incremental, 2: full
     * @return array[][] : array of question in array of group
     */
    private function getIndexItemsQuestions($type)
    {
        $sessionLem=Yii::app()->session["survey_{$this->iSurveyId}"];
        /* get field map : have more info*/
        $questionList=$sessionLem['fieldmap'];
        /* get group list : for information about group ...*/
        $groupList=$sessionLem['grouplist'];
        /* get the step infor from LEM : for alreay seen questin : give if error/show and answered ...*/
        $stepInfos = LimeExpressionManager::GetStepIndexInfo();

        /* The final step index to return */
        $stepIndex=array();
        $prevStep=-1;
        $prevGroupSeq=-1;
        foreach($sessionLem['fieldmap'] as $step=>$questionFieldmap)
        {
            if(isset($questionFieldmap['questionSeq']) && $questionFieldmap['questionSeq']!=$prevStep ){ // Sub question have same questionSeq, and no questionSeq : must be hidden (lastpage, id, seed ...)
                /* This question can be in index */
                $questionStep=$questionFieldmap['questionSeq']+1;
                $stepInfo=isset($stepInfos[$questionFieldmap['questionSeq']]) ? $stepInfos[$questionFieldmap['questionSeq']]: array('show'=>true,'anyUnanswered'=>null,'anyErrors'=>null);
                if( ($questionStep <= $sessionLem['maxstep'] ) // || $type>1 : index can be shown : but next step is disable somewhere
                   && $stepInfo['show']// attribute hidden + relevance : @todo review EM function ?
                ) {
                    /* Control if we are in a new group : always true at first question*/
                    //$GroupId=(isset($questionFieldmap['random_gid']) && $questionFieldmap['random_gid']) ? $questionFieldmap['random_gid'] : $questionFieldmap['gid'];
                    if($questionFieldmap['groupSeq']!=$prevGroupSeq){
                        // add the previous group if it's not empty (all question hidden etc ....)
                        if(!empty($questionInGroup)){
                            $actualGroup['questions']=$questionInGroup;
                            $stepIndex[]=$actualGroup;
                        }
                        //add the group
                        $groupInfo=$groupList[$questionFieldmap['groupSeq']];
                        $actualGroup=array(
                            'gid'=>$groupInfo['gid'],
                            'text'=>LimeExpressionManager::ProcessString($groupInfo['group_name']),
                            'description'=>LimeExpressionManager::ProcessString($groupInfo['description']),
                        );
                        /* The 'show' question in this group */
                        $questionInGroup=array();
                        $prevGroupSeq=$questionFieldmap['groupSeq'];
                    }
                    $questionInfo=array(
                        'qid'=>$questionFieldmap['qid'],
                        'code'=>$questionFieldmap['title'], /* @todo : If survey us set to show question code : we must show it */
                        'text'=>LimeExpressionManager::ProcessString($questionFieldmap['question']),
                        'step'=>$questionStep,
                        'url'=>Yii::app()->getController()->createUrl("survey/index",array('sid'=>$this->iSurveyId,'move'=>$questionStep)),
                        'submit'=>ls_json_encode(array('move'=>$questionStep)),
                        'stepStatus'=>array(
                              'index-item-before' => ($questionStep < $sessionLem['step']), /* did we need a before ? seen seems better */
                              'index-item-seen' => ($questionStep <= $sessionLem['maxstep']),
                              'index-item-current' => ($questionStep == $sessionLem['step']),
                          ),/* order have importance for css : last on is apply */

                    );
                    /* Get the step status */
                    if(true || $this->getStepInfo){
                        /* Get the current group info */
                        /* We can not validate a question after step ... @todo : create a new EM function ? Do it manually ? */
                        if ($questionStep <= $sessionLem['maxstep'] && $questionStep != $sessionLem['step']){

                            $questionInfo['stepStatus']['index-item-error'] = (bool) $stepInfo['anyErrors'];
                            $questionInfo['stepStatus']['index-item-unanswered'] = (bool) $stepInfo['anyUnanswered'];
                        }else{
                            $questionInfo['stepStatus']['index-item-error'] = null;
                            $questionInfo['stepStatus']['index-item-unanswered'] = null;
                        }
                    }else{
                        $questionInfo['stepStatus']['index-item-error'] = null;
                        $questionInfo['stepStatus']['index-item-unanswered'] = null;
                    }
                    $aClass=array_filter($questionInfo['stepStatus']);
                    $questionInfo['coreClass']=implode(" ",array_merge(array('index-item'),array_keys($aClass)));
                    $questionInGroup[$questionStep]=$questionInfo;
                }
                /* Update the previous step */
                $prevStep=$questionFieldmap['questionSeq'];
            }
        }
        /* Add the last group */
        if(!empty($questionInGroup)){
            $actualGroup['questions']=$questionInGroup;
            $stepIndex[]=$actualGroup;
        }
        return $stepIndex;
    }

    /**
     * Return html for list of link
     * @return string : html to be used
     */
    public function getIndexLink()
    {
        $indexItems=$this->getIndexItems();
        if(!empty($indexItems)){
            Yii::app()->getClientScript()->registerScript("activateActionLink","activateActionLink();",\CClientScript::POS_END);
            return $this->getIndexHtml($this->surveyFormat,'link');
        }else{
            return '';
        }
    }

    /**
     * Return html for list of button
     * @return string : html to be used
     */
    public function getIndexButton()
    {
        $indexItems=$this->getIndexItems();
        if(!empty($indexItems)){
            Yii::app()->getClientScript()->registerScript("manageIndex","manageIndex();",\CClientScript::POS_END);
            return $this->getIndexHtml($this->surveyFormat);
        }else{
            return '';
        }
    }
    /**
     * Return html with params
     * @param string : $surveyFormat (G|S)
     * @param string : $viewType (link|button)
     *
     * @return string : html to be used
     */
    private function getIndexHtml($surveyFormat,$viewType='button')
    {
        switch($surveyFormat){
            case 'G':
                $viewFile="groupIndex";
                break;
            case 'S':
                $viewFile="questionIndex";
                break;
            default:
                Yii::log("Uknow survey format for question index, must be G or S.", 'error','application.helpers.questionIndexHelper');
                return "";
        }
        switch($viewType){
            case 'button':
                break;
            case 'link':
                $viewFile.="MenuLink";
                break;
            default:
                Yii::log("Uknow view type for question index, must be button or link.", 'error','application.helpers.questionIndexHelper');
                return "";
        }
        Yii::import('application.helpers.viewHelper', true);
        return Yii::app()->getController()->renderPartial("/survey/system/surveyIndex/{$viewFile}",array(
            'type'=>$this->indexType,
            'indexItems'=>$this->indexItems,
        ),true);
    }
}
