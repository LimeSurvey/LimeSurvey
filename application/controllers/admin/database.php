<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/
/**
* Database
*g
* @package LimeSurvey
* @author
* @copyright 2011
* @access public
*/
class database extends Survey_Common_Action
{
    /**
    * Database::index()
    *
    * @param mixed $sa
    * @return
    */
    function index($sa = null)
    {
        $sAction=Yii::app()->request->getPost('action');
        $iSurveyID=returnGlobal('sid');
        $iQuestionGroupID=returnGlobal('gid');
        $iQuestionID=returnGlobal('qid');
        $sDBOutput = '';

        $oFixCKeditor= new LSYii_Validators;
        $oFixCKeditor->fixCKeditor=true;
        $oFixCKeditor->xssfilter=false;

        if ($sAction == "updatedefaultvalues" && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent','update'))
        {

            $aSurveyLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
            array_unshift($aSurveyLanguages,$sBaseLanguage);

            Question::model()->updateAll(array('same_default'=> Yii::app()->request->getPost('samedefault')?1:0), 'sid=:sid ANd qid=:qid', array(':sid'=>$iSurveyID, ':qid'=>$iQuestionID));

            $arQuestion = Question::model()->findByAttributes(array('qid'=>$iQuestionID));
            $sQuestionType = $arQuestion['type'];

            $aQuestionTypeList=getQuestionTypeList('','array');
            if ($aQuestionTypeList[$sQuestionType]['answerscales']>0 && $aQuestionTypeList[$sQuestionType]['subquestions']==0)
            {
                for ($iScaleID=0;$iScaleID<$aQuestionTypeList[$sQuestionType]['answerscales'];$iScaleID++)
                {
                    foreach ($aSurveyLanguages as $sLanguage)
                    {
                        if (!is_null(Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage)))
                        {
                            $this->_updateDefaultValues($iQuestionID,0,$iScaleID,'',$sLanguage,Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage),true);
                        }
                        if (!is_null(Yii::app()->request->getPost('other_'.$iScaleID.'_'.$sLanguage)))
                        {
                            $this->_updateDefaultValues($iQuestionID,0,$iScaleID,'other',$sLanguage,Yii::app()->request->getPost('other_'.$iScaleID.'_'.$sLanguage),true);
                        }
                    }
                }
            }
            if ($aQuestionTypeList[$sQuestionType]['subquestions']>0)
            {

                foreach ($aSurveyLanguages as $sLanguage)
                {

                    $arQuestions = Question::model()->findAllByAttributes(array('sid'=>$iSurveyID, 'gid'=>$iQuestionGroupID, 'parent_qid'=>$iQuestionID, 'language'=>$sLanguage, 'scale_id'=>0));

                    for ($iScaleID=0;$iScaleID<$aQuestionTypeList[$sQuestionType]['subquestions'];$iScaleID++)
                    {

                        foreach ($arQuestions as $aSubquestionrow)
                        {
                            if (!is_null(Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage.'_'.$aSubquestionrow['qid'])))
                            {
                                $this->_updateDefaultValues($iQuestionID,$aSubquestionrow['qid'],$iScaleID,'',$sLanguage,Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage.'_'.$aSubquestionrow['qid']),true);
                            }
                        }
                    }
                }
            }
            if ($aQuestionTypeList[$sQuestionType]['answerscales']==0 && $aQuestionTypeList[$sQuestionType]['subquestions']==0)
            {
                foreach ($aSurveyLanguages as $sLanguage)
                {
                    // Qick and dirty insert for yes/no defaul value
                    // write the the selectbox option, or if "EM" is slected, this value to table
                    if ($sQuestionType == 'Y'){
                        /// value for all langs
                        if (Yii::app()->request->getPost('samedefault') == 1){
                            $sLanguage = $aSurveyLanguages[0];   // turn
                        }else{
                            $sCurrentLang = $sLanguage; // edit the next lines
                        }
                        if ( Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage) == 'EM')  { // Case EM, write expression to database
                            $this->_updateDefaultValues($iQuestionID,0,0,'',$sLanguage,Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage.'_EM'),true);
                        }
                        else{
                            // Case "other", write list value to database
                            $this->_updateDefaultValues($iQuestionID,0,0,'',$sLanguage,Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage),true);
                        }
                        ///// end yes/no
                    }else{
                        if (!is_null(Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage.'_0')))
                        {
                            $this->_updateDefaultValues($iQuestionID,0,0,'',$sLanguage,Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage.'_0'),true);
                        }
                    }
               }
            }
            Yii::app()->session['flashmessage'] = gT("Default value settings were successfully saved.");
            LimeExpressionManager::SetDirtyFlag();

            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
            }
        }


        if ($sAction == "updateansweroptions" && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent','update'))
        {
            Yii::app()->loadHelper('database');
            $aSurveyLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
            array_unshift($aSurveyLanguages,$sBaseLanguage);
            $arQuestion = Question::model()->findByAttributes(array('qid'=>$iQuestionID));
            $sQuestionType = $arQuestion['type'];    // Checked)
            $aQuestionTypeList=getQuestionTypeList('','array');
            $iScaleCount=$aQuestionTypeList[$sQuestionType]['answerscales'];
            //First delete all answers
            Answer::model()->deleteAllByAttributes(array('qid'=>$iQuestionID));
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
            for ($iScaleID=0;$iScaleID<$iScaleCount;$iScaleID++)
            {
                $iMaxCount=(int) Yii::app()->request->getPost('answercount_'.$iScaleID);

                for ($iSortOrderID=1;$iSortOrderID<$iMaxCount;$iSortOrderID++)
                {
                    $sCode=sanitize_paranoid_string(Yii::app()->request->getPost('code_'.$iSortOrderID.'_'.$iScaleID));

                    $iAssessmentValue=(int) Yii::app()->request->getPost('assessment_'.$iSortOrderID.'_'.$iScaleID);
                    foreach ($aSurveyLanguages as $sLanguage)
                    {
                        $sAnswerText=Yii::app()->request->getPost('answer_'.$sLanguage.'_'.$iSortOrderID.'_'.$iScaleID);

                        // Fix bug with FCKEditor saving strange BR types
                        $sAnswerText=$oFixCKeditor->fixCKeditor($sAnswerText);
                        // Now we insert the answers
                        $iInsertCount=Answer::model()->insertRecords(array('code'=>$sCode,
                        'answer'=>$sAnswerText,
                        'qid'=>$iQuestionID,
                        'sortorder'=>$iSortOrderID,
                        'language'=>$sLanguage,
                        'assessment_value'=>$iAssessmentValue,
                        'scale_id'=>$iScaleID));
                        if (!$iInsertCount) // Checked
                        {
                            Yii::app()->setFlashMessage(gT("Failed to update answers"),'error');
                        }
                    } 
                    // Updating code (oldcode!==null) => update condition with the new code
                    $sOldCode=Yii::app()->request->getPost('oldcode_'.$iSortOrderID.'_'.$iScaleID);
                    if(isset($sOldCode) && $sCode !== $sOldCode) {
                        Condition::model()->updateAll(array('value'=>$sCode), 'cqid=:cqid AND value=:value', array(':cqid'=>$iQuestionID, ':value'=>$sOldCode));
                    }
                }  // for ($sortorderid=0;$sortorderid<$maxcount;$sortorderid++)
            }  //  for ($scale_id=0;

            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);
            if (!Yii::app()->request->getPost('bFullPOST'))
            {
                Yii::app()->setFlashMessage(gT("Not all answer options were saved. This usually happens due to server limitations ( PHP setting max_input_vars) - please contact your system administrator."));
            }
            else
            {
                Yii::app()->session['flashmessage']= gT("Answer options were successfully saved.");
            }
            LimeExpressionManager::SetDirtyFlag();
            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                $this->getController()->redirect(array('/admin/questions/sa/answeroptions/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
            }
        }

        if ($sAction == "updatesubquestions" && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent','update'))
        {
            Yii::app()->loadHelper('database');
            $aSurveyLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
            array_unshift($aSurveyLanguages,$sBaseLanguage);
            $arQuestion = Question::model()->findByAttributes(array('qid'=>$iQuestionID));
            $sQuestionType = $arQuestion['type'];    // Checked
            $aQuestionTypeList=getQuestionTypeList('','array');
            $iScaleCount=$aQuestionTypeList[$sQuestionType]['subquestions'];
            // First delete any deleted ids
            $aDeletedQIDs=explode(' ', trim(Yii::app()->request->getPost('deletedqids')));

            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
            $aDeletedQIDs=array_unique($aDeletedQIDs,SORT_NUMERIC);
            foreach ($aDeletedQIDs as $iDeletedQID)
            {
                $iDeletedQID=(int)$iDeletedQID;
                if ($iDeletedQID>0)
                { // don't remove undefined
                    $iInsertCount = Question::model()->deleteAllByAttributes(array('qid'=>$iDeletedQID));
                    if (!$iInsertCount)
                    {
                        Yii::app()->setFlashMessage(gT("Failed to delete answer"),'error');
                    }
                }
            }

            //Determine ids by evaluating the hidden field
            $aRows=array();
            $aCodes=array();
            $aOldCodes=array();
            $aRelevance=array();
            foreach ($_POST as $sPOSTKey=>$sPOSTValue)
            {
                $sPOSTKey=explode('_',$sPOSTKey);
                if ($sPOSTKey[0]=='answer')
                {
                    $aRows[$sPOSTKey[3]][$sPOSTKey[1]][$sPOSTKey[2]]=$sPOSTValue;
                }
                if ($sPOSTKey[0]=='code')
                {
                    $aCodes[$sPOSTKey[2]][]=$sPOSTValue;
                }
                if ($sPOSTKey[0]=='oldcode')
                {
                    $aOldCodes[$sPOSTKey[2]][]=$sPOSTValue;
                }
                if ($sPOSTKey[0]=='relevance')
                {
                    $aRelevance[$sPOSTKey[2]][]=$sPOSTValue;
                }
            }
            $aInsertQID =array();
            for ($iScaleID=0;$iScaleID<$iScaleCount;$iScaleID++)
            {
                foreach ($aSurveyLanguages as $sLanguage)
                {
                    $iPosition=0;
                    foreach ($aRows[$iScaleID][$sLanguage] as $subquestionkey=>$subquestionvalue)
                    {
                        if (substr($subquestionkey,0,3)!='new')           //update record
                        {
                            $oSubQuestion=Question::model()->find("qid=:qid AND language=:language",array(":qid"=>$subquestionkey,':language'=>$sLanguage));
                            $oSubQuestion->question_order=$iPosition+1;
                            $oSubQuestion->title=$aCodes[$iScaleID][$iPosition];
                            $oSubQuestion->question=$subquestionvalue;
                            $oSubQuestion->scale_id=$iScaleID;
                            //dual matrix, text/number matrix: subQ relevance per line not per scale, so ScaleID is always 0
                            $oSubQuestion->relevance=$aRelevance[0][$iPosition];
                        }
                        else  // new record
                        {
                            if (!isset($aInsertQID[$iScaleID][$iPosition]))     //new record: first (default) language
                            {
                                $oSubQuestion=new Question;
                                $oSubQuestion->sid=$iSurveyID;
                                $oSubQuestion->gid=$iQuestionGroupID;
                                $oSubQuestion->question_order=$iPosition+1;
                                $oSubQuestion->title=$aCodes[$iScaleID][$iPosition];
                                $oSubQuestion->question=$subquestionvalue;
                                $oSubQuestion->parent_qid=$iQuestionID;
                                $oSubQuestion->language=$sLanguage;
                                $oSubQuestion->scale_id=$iScaleID;
                                $oSubQuestion->relevance=$aRelevance[0][$iPosition];
                            }
                            else                                                //new record: additional language
                            {
                                $oSubQuestion=Question::model()->find("qid=:qid AND language=:language",array(":qid"=>$aInsertQID[$iScaleID][$iPosition],':language'=>$sLanguage));
                                if(!$oSubQuestion)
                                    $oSubQuestion=new Question;
                                $oSubQuestion->sid=$iSurveyID;
                                $oSubQuestion->qid=$aInsertQID[$iScaleID][$iPosition];
                                $oSubQuestion->gid=$iQuestionGroupID;
                                $oSubQuestion->question_order=$iPosition+1;
                                $oSubQuestion->title=$aCodes[$iScaleID][$iPosition];
                                $oSubQuestion->question=$subquestionvalue;
                                $oSubQuestion->parent_qid=$iQuestionID;
                                $oSubQuestion->language=$sLanguage;
                                $oSubQuestion->scale_id=$iScaleID;
                                $oSubQuestion->relevance=$aRelevance[$iScaleID][$iPosition];
                            }
                        }
                        if ($oSubQuestion->qid) {
                            switchMSSQLIdentityInsert('questions',true);
                            $bSubQuestionResult=$oSubQuestion->save();
                            switchMSSQLIdentityInsert('questions',false);
                        }else
                        {
                            $bSubQuestionResult=$oSubQuestion->save();
                        }
                        if($bSubQuestionResult)
                        {
                            if(substr($subquestionkey,0,3)!='new' && isset($aOldCodes[$iScaleID][$iPosition]) && $aCodes[$iScaleID][$iPosition] !== $aOldCodes[$iScaleID][$iPosition])
                            {
                                Condition::model()->updateAll(array('cfieldname'=>'+'.$iSurveyID.'X'.$iQuestionGroupID.'X'.$iQuestionID.$aCodes[$iScaleID][$iPosition], 'value'=>$aCodes[$iScaleID][$iPosition]), 'cqid=:cqid AND cfieldname=:cfieldname AND value=:value', array(':cqid'=>$iQuestionID, ':cfieldname'=>$iSurveyID.'X'.$iQuestionGroupID.'X'.$iQuestionID, ':value'=>$aOldCodes[$iScaleID][$iPosition]));
                            }
                            if (!isset($aInsertQID[$iScaleID][$iPosition]))
                            {
                                $aInsertQID[$iScaleID][$iPosition]=$oSubQuestion->qid;
                            }
                        }
                        else
                        {
                            $aErrors=$oSubQuestion->getErrors();
                            if(count($aErrors))
                            {
                                //$sErrorMessage=gT("Question could not be updated with this errors:");
                                foreach($aErrors as $sAttribute=>$aStringErrors)
                                {
                                    foreach($aStringErrors as $sStringErrors)
                                        Yii::app()->setFlashMessage(sprintf(gT("Error on %s for subquestion %s: %s"), $sAttribute,$aCodes[$iScaleID][$iPosition],$sStringErrors),'error');
                                }
                            }
                            else
                            {
                                Yii::app()->setFlashMessage(sprintf(gT("Subquestions %s could not be updated."),$aCodes[$iScaleID][$iPosition]),'error');
                            }
                        }
                        $iPosition++;
                    }

                }
            }

            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);// Do it only if there are no error ?
            if(!isset($aErrors) || !count($aErrors))
            {
                if (!Yii::app()->request->getPost('bFullPOST'))
                {
                    Yii::app()->session['flashmessage'] = gT("Not all subquestions were saved. This usually happens due to server limitations ( PHP setting max_input_vars) - please contact your system administrator.");
                }
                else
                {
                    Yii::app()->session['flashmessage'] = gT("Subquestions were successfully saved.");
                }
            }
            //$action='editsubquestions';
            LimeExpressionManager::SetDirtyFlag();
            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                $this->getController()->redirect(array('/admin/questions/sa/subquestions/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
            }
        }

        if (in_array($sAction, array('insertquestion', 'copyquestion')) && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent','create'))
        {
            $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
            if (strlen(Yii::app()->request->getPost('title')) < 1)
            {
                Yii::app()->setFlashMessage(gT("The question could not be added. You must enter at least a question code."),'error');
            }
            else
            {
                if (Yii::app()->request->getPost('questionposition',"")!="")
                {
                    $iQuestionOrder= intval(Yii::app()->request->getPost('questionposition'));
                    //Need to renumber all questions on or after this
                    $sQuery = "UPDATE {{questions}} SET question_order=question_order+1 WHERE gid=:gid AND question_order >= :order";
                    Yii::app()->db->createCommand($sQuery)->bindValues(array(':gid'=>$iQuestionGroupID, ':order'=>$iQuestionOrder))->query();
                } else {
                    $iQuestionOrder=(getMaxQuestionOrder($iQuestionGroupID,$iSurveyID));
                    $iQuestionOrder++;
                }
                $sQuestionText=Yii::app()->request->getPost('question_'.$sBaseLanguage,'');
                $sQuestionHelp=Yii::app()->request->getPost('help_'.$sBaseLanguage,'');
                // Fix bug with FCKEditor saving strange BR types : in rules ?
                $sQuestionText=$oFixCKeditor->fixCKeditor($sQuestionText);
                $sQuestionHelp=$oFixCKeditor->fixCKeditor($sQuestionHelp);

                $iQuestionID=0;
                $oQuestion= new Question;
                $oQuestion->sid = $iSurveyID;
                $oQuestion->gid = $iQuestionGroupID;
                $oQuestion->type = Yii::app()->request->getPost('type');
                $oQuestion->title = Yii::app()->request->getPost('title');
                $oQuestion->question = $sQuestionText;
                $oQuestion->preg = Yii::app()->request->getPost('preg');
                $oQuestion->help = $sQuestionHelp;
                $oQuestion->other = Yii::app()->request->getPost('other');
                $oQuestion->mandatory = Yii::app()->request->getPost('mandatory');
                $oQuestion->relevance = Yii::app()->request->getPost('relevance');
                $oQuestion->question_order = $iQuestionOrder;
                $oQuestion->language = $sBaseLanguage;
                $oQuestion->save();
                if($oQuestion)
                {
                    $iQuestionID=$oQuestion->qid;
                }
                $aErrors=$oQuestion->getErrors();
                if(count($aErrors))
                {
                    foreach($aErrors as $sAttribute=>$aStringErrors)
                    {
                        foreach($aStringErrors as $sStringErrors)
                            Yii::app()->setFlashMessage(sprintf(gT("Question could not be created with error on %s: %s"), $sAttribute,$sStringErrors),'error');
                    }
                }
                // Add other languages
                if ($iQuestionID)
                {
                    $addlangs = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
                    foreach ($addlangs as $alang)
                    {
                        if ($alang != "")
                        {
                            $langqid=0;
                            $oQuestion= new Question;
                            $oQuestion->qid = $iQuestionID;
                            $oQuestion->sid = $iSurveyID;
                            $oQuestion->gid = $iQuestionGroupID;
                            $oQuestion->type = Yii::app()->request->getPost('type');
                            $oQuestion->title = Yii::app()->request->getPost('title');
                            $oQuestion->question = Yii::app()->request->getPost('question_'.$alang);
                            $oQuestion->preg = Yii::app()->request->getPost('preg');
                            $oQuestion->help = Yii::app()->request->getPost('help_'.$alang);
                            $oQuestion->other = Yii::app()->request->getPost('other');
                            $oQuestion->mandatory = Yii::app()->request->getPost('mandatory');
                            $oQuestion->relevance = Yii::app()->request->getPost('relevance');
                            $oQuestion->question_order = $iQuestionOrder;
                            $oQuestion->language = $alang;
                            switchMSSQLIdentityInsert('questions',true);// Not sure for this one ?
                            $oQuestion->save();
                            switchMSSQLIdentityInsert('questions',false);
                            if($oQuestion)
                            {
                                $langqid=$oQuestion->qid;
                            }
                            $aErrors=$oQuestion->getErrors();
                            if(count($aErrors))
                            {
                                foreach($aErrors as $sAttribute=>$aStringErrors)
                                {
                                    foreach($aStringErrors as $sStringErrors)
                                        Yii::app()->setFlashMessage(sprintf(gT("Question in language %s could not be created with error on %s: %s"), $alang, $sAttribute,$sStringErrors),'error');
                                }
                            }
#                            if (!$langqid)
#                            {
#                                Yii::app()->setFlashMessage(gT("Question in language %s could not be created."),'error');
#                            }
                        }
                    }
                }


                if (!$iQuestionID)
                {
                    Yii::app()->setFlashMessage(gT("Question could not be created."),'error');

                } else {
                    if ($sAction == 'copyquestion') {
                        if (returnGlobal('copysubquestions') == "Y")
                        {
                            $aSQIDMappings = array();
                            $r1 = Question::model()->getSubQuestions(returnGlobal('oldqid'));
                            $aSubQuestions = $r1->readAll();
                            foreach ($aSubQuestions as $qr1)
                            {
                                $qr1['parent_qid'] = $iQuestionID;
                                if (isset($aSQIDMappings[$qr1['qid']]))
                                {
                                    $qr1['qid'] = $aSQIDMappings[$qr1['qid']];
                                } else {
                                    $oldqid = $qr1['qid'];
                                    unset($qr1['qid']);
                                }
                                $qr1['gid'] = $iQuestionGroupID;
                                $iInsertID = Question::model()->insertRecords($qr1);
                                if (!isset($qr1['qid']))
                                {
                                    $aSQIDMappings[$oldqid] = $iInsertID;
                                }
                            }
                        }
                        if (returnGlobal('copyanswers') == "Y")
                        {
                            $r1 = Answer::model()->getAnswers(returnGlobal('oldqid'));
                            $aAnswerOptions = $r1->readAll();
                            foreach ($aAnswerOptions as $qr1)
                            {
                                Answer::model()->insertRecords(array(
                                'qid' => $iQuestionID,
                                'code' => $qr1['code'],
                                'answer' => $qr1['answer'],
                                'assessment_value' => $qr1['assessment_value'],
                                'sortorder' => $qr1['sortorder'],
                                'language' => $qr1['language'],
                                'scale_id' => $qr1['scale_id']
                                ));
                            }
                        }
                        if (returnGlobal('copyattributes') == "Y")
                        {
                            $oOldAttributes = QuestionAttribute::model()->findAll("qid=:qid",array("qid"=>returnGlobal('oldqid')));
                            foreach($oOldAttributes as $oOldAttribute)
                            {
                                $attribute = new QuestionAttribute;
                                $attribute->qid = $iQuestionID;
                                $attribute->value = $oOldAttribute->value;
                                $attribute->attribute = $oOldAttribute->attribute;
                                $attribute->language = $oOldAttribute->language;
                                $attribute->save();
                            }
                        }
                    } else {
                        $qattributes = questionAttributes();
                        $validAttributes = $qattributes[Yii::app()->request->getPost('type')];
                        $aLanguages=array_merge(array(Survey::model()->findByPk($iSurveyID)->language),Survey::model()->findByPk($iSurveyID)->additionalLanguages);

                        foreach ($validAttributes as $validAttribute)
                        {
                            if ($validAttribute['i18n'])
                            {
                                foreach ($aLanguages as $sLanguage)
                                {
                                    $value=Yii::app()->request->getPost($validAttribute['name'].'_'.$sLanguage);
                                    $iInsertCount = QuestionAttribute::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$iQuestionID, 'language'=>$sLanguage));
                                    if (count($iInsertCount)>0)
                                    {
                                        if ($value!='')
                                        {
                                            QuestionAttribute::model()->updateAll(array('value'=>$value), 'attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID, ':language'=>$sLanguage));
                                        }
                                        else
                                        {
                                            QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID, ':language'=>$sLanguage));
                                        }
                                    }
                                    elseif($value!='')
                                    {
                                        $attribute = new QuestionAttribute;
                                        $attribute->qid = $iQuestionID;
                                        $attribute->value = $value;
                                        $attribute->attribute = $validAttribute['name'];
                                        $attribute->language = $sLanguage;
                                        $attribute->save();
                                    }
                                }
                            }
                            else
                            {
                                $value=Yii::app()->request->getPost($validAttribute['name']);

                                if ($validAttribute['name']=='multiflexible_step' && trim($value)!='') {
                                    $value=floatval($value);
                                    if ($value==0) $value=1;
                                };

                                $iInsertCount = QuestionAttribute::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$iQuestionID));
                                if (count($iInsertCount)>0)
                                {
                                    if($value!=$validAttribute['default'] && trim($value)!="")
                                    {
                                        QuestionAttribute::model()->updateAll(array('value'=>$value),'attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID));
                                    }
                                    else
                                    {
                                        QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID));
                                    }
                                }
                                elseif($value!=$validAttribute['default'] && trim($value)!="")
                                {
                                    $attribute = new QuestionAttribute;
                                    $attribute->qid = $iQuestionID;
                                    $attribute->value = $value;
                                    $attribute->attribute = $validAttribute['name'];
                                    $attribute->save();
                                }
                            }
                        }

                    }
                    Question::model()->updateQuestionOrder($iQuestionGroupID, $iSurveyID);
                    Yii::app()->session['flashmessage'] =  gT("Question was successfully added.");

                }

            }

            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting

            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
            }
        }

        if ($sAction == "updatequestion" && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent','update'))
        {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);

            $cqr=Question::model()->findByAttributes(array('qid'=>$iQuestionID));
            $oldtype=$cqr['type'];
            $oldgid=$cqr['gid'];

            // Remove invalid question attributes on saving
            $qattributes=questionAttributes();

            $criteria = new CDbCriteria;
            $criteria->compare('qid',$iQuestionID);
            if (isset($qattributes[Yii::app()->request->getPost('type')])){
                $validAttributes=$qattributes[Yii::app()->request->getPost('type')];
                foreach ($validAttributes as  $validAttribute)
                {
                    $criteria->compare('attribute', '<>'.$validAttribute['name']);
                }
            }
            QuestionAttribute::model()->deleteAll($criteria);

            $aLanguages=array_merge(array(Survey::model()->findByPk($iSurveyID)->language),Survey::model()->findByPk($iSurveyID)->additionalLanguages);


            //now save all valid attributes
            $validAttributes=$qattributes[Yii::app()->request->getPost('type')];

            foreach ($validAttributes as $validAttribute)
            {
                if ($validAttribute['i18n'])
                {
                    foreach ($aLanguages as $sLanguage)
                    {// TODO sanitise XSS
                        $value=Yii::app()->request->getPost($validAttribute['name'].'_'.$sLanguage);
                        $iInsertCount = QuestionAttribute::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$iQuestionID, 'language'=>$sLanguage));
                        if (count($iInsertCount)>0)
                        {
                            if ($value!='')
                            {
                                QuestionAttribute::model()->updateAll(array('value'=>$value), 'attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID, ':language'=>$sLanguage));
                            }
                            else
                            {
                                QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID, ':language'=>$sLanguage));
                            }
                        }
                        elseif($value!='')
                        {
                            $attribute = new QuestionAttribute;
                            $attribute->qid = $iQuestionID;
                            $attribute->value = $value;
                            $attribute->attribute = $validAttribute['name'];
                            $attribute->language = $sLanguage;
                            $attribute->save();
                        }
                    }
                }
                else
                {
                    $value=Yii::app()->request->getPost($validAttribute['name']);

                    if ($validAttribute['name']=='multiflexible_step' && trim($value)!='') {
                        $value=floatval($value);
                        if ($value==0) $value=1;
                    };

                    $iInsertCount = QuestionAttribute::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$iQuestionID));
                    if (count($iInsertCount)>0)
                    {
                        if($value!=$validAttribute['default'] && trim($value)!="")
                        {
                            QuestionAttribute::model()->updateAll(array('value'=>$value),'attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID));
                        }
                        else
                        {
                            QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID));
                        }
                    }
                    elseif($value!=$validAttribute['default'] && trim($value)!="")
                    {
                        $attribute = new QuestionAttribute;
                        $attribute->qid = $iQuestionID;
                        $attribute->value = $value;
                        $attribute->attribute = $validAttribute['name'];
                        $attribute->save();
                    }
                }
            }


            $aQuestionTypeList=getQuestionTypeList('','array');
            // These are the questions types that have no answers and therefore we delete the answer in that case
            $iAnswerScales = $aQuestionTypeList[Yii::app()->request->getPost('type')]['answerscales'];
            $iSubquestionScales = $aQuestionTypeList[Yii::app()->request->getPost('type')]['subquestions'];

            // These are the questions types that have the other option therefore we set everything else to 'No Other'
            if ((Yii::app()->request->getPost('type')!= "L") && (Yii::app()->request->getPost('type')!= "!") && (Yii::app()->request->getPost('type')!= "P") && (Yii::app()->request->getPost('type')!="M"))
            {
                $_POST['other']='N';
            }

            // These are the questions types that have no validation - so zap it accordingly

            if (Yii::app()->request->getPost('type')== "!" || Yii::app()->request->getPost('type')== "L" || Yii::app()->request->getPost('type')== "M" || Yii::app()->request->getPost('type')== "P" ||
            Yii::app()->request->getPost('type')== "F" || Yii::app()->request->getPost('type')== "H" ||
            Yii::app()->request->getPost('type')== "X" || Yii::app()->request->getPost('type')== "")
            {
                $_POST['preg']='';
            }

            // These are the questions types that have no mandatory property - so zap it accordingly
            if (Yii::app()->request->getPost('type')== "X" || Yii::app()->request->getPost('type')== "|")
            {
                $_POST['mandatory']='N';
            }


            if ($oldtype != Yii::app()->request->getPost('type'))
            {
                // TMSW Condition->Relevance:  Do similar check via EM, but do allow such a change since will be easier to modify relevance
                //Make sure there are no conditions based on this question, since we are changing the type
                $ccresult = Condition::model()->findAllByAttributes(array('cqid'=>$iQuestionID));
                $cccount=count($ccresult);
                foreach ($ccresult as $ccr) {$qidarray[]=$ccr['qid'];}
                if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
            }
            if (isset($cccount) && $cccount)
            {
                Yii::app()->setFlashMessage(gT("Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions  before you can change the type of this question."),'error');
            }
            else
            {
                if (isset($iQuestionGroupID) && $iQuestionGroupID != "")
                {

                    //                    $array_result=checkMoveQuestionConstraintsForConditions(sanitize_int($surveyid),sanitize_int($qid), sanitize_int($gid));
                    //                    // If there is no blocking conditions that could prevent this move
                    //
                    //                    if (is_null($array_result['notAbove']) && is_null($array_result['notBelow']))
                    //                    {
                    $aSurveyLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
                    $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
                    array_push($aSurveyLanguages,$sBaseLanguage);
                    foreach ($aSurveyLanguages as $qlang)
                    {
                        if (isset($qlang) && $qlang != "")
                        {
                            // &eacute; to é and &amp; to & : really needed ? Why not for answers ? (130307)
                            $sQuestionText=Yii::app()->request->getPost('question_'.$qlang,'');
                            $sQuestionHelp=Yii::app()->request->getPost('help_'.$qlang,'');
                            // Fix bug with FCKEditor saving strange BR types : in rules ?
                            $sQuestionText=$oFixCKeditor->fixCKeditor($sQuestionText);
                            $sQuestionHelp=$oFixCKeditor->fixCKeditor($sQuestionHelp);
                            $udata = array(
                            'type' => Yii::app()->request->getPost('type'),
                            'title' => Yii::app()->request->getPost('title'),
                            'question' => $sQuestionText,
                            'preg' => Yii::app()->request->getPost('preg'),
                            'help' => $sQuestionHelp,
                            'gid' => $iQuestionGroupID,
                            'other' => Yii::app()->request->getPost('other'),
                            'mandatory' => Yii::app()->request->getPost('mandatory'),
                            'relevance' => Yii::app()->request->getPost('relevance'),
                            );

                            if ($oldgid!=$iQuestionGroupID)
                            {

                                if ( getGroupOrder($iSurveyID,$oldgid) > getGroupOrder($iSurveyID,$iQuestionGroupID) )
                                {
                                    // TMSW Condition->Relevance:  What is needed here?

                                    // Moving question to a 'upper' group
                                    // insert question at the end of the destination group
                                    // this prevent breaking conditions if the target qid is in the dest group
                                    $insertorder = getMaxQuestionOrder($iQuestionGroupID,$iSurveyID) + 1;
                                    $udata = array_merge($udata,array('question_order' => $insertorder));
                                }
                                else
                                {
                                    // Moving question to a 'lower' group
                                    // insert question at the beginning of the destination group
                                    shiftOrderQuestions($iSurveyID,$iQuestionGroupID,1); // makes 1 spare room for new question at top of dest group
                                    $udata = array_merge($udata,array('question_order' => 0));
                                }
                            }
                            //$condn = array('sid' => $surveyid, 'qid' => $qid, 'language' => $qlang);
                            $oQuestion = Question::model()->findByPk(array("qid"=>$iQuestionID,'language'=>$qlang));
                            foreach ($udata as $k => $v)
                                $oQuestion->$k = $v;

                            $uqresult = $oQuestion->save();//($uqquery); // or safeDie ("Error Update Question: ".$uqquery."<br />");  // Checked)
                            if (!$uqresult)
                            {
                                $bOnError=true;
                                $aErrors=$oQuestion->getErrors();
                                if(count($aErrors))
                                {
                                    foreach($aErrors as $sAttribute=>$aStringErrors)
                                    {
                                        foreach($aStringErrors as $sStringErrors)
                                            Yii::app()->setFlashMessage(sprintf(gT("Question could not be updated with error on %s: %s"), $sAttribute,$sStringErrors),'error');
                                    }
                                }
                                else
                                {
                                    Yii::app()->setFlashMessage(gT("Question could not be updated."),'error');
                                }
                            }
                        }
                    }


                    // Update the group ID on subquestions, too
                    if ($oldgid!=$iQuestionGroupID)
                    {
                        Question::model()->updateAll(array('gid'=>$iQuestionGroupID), 'qid=:qid and parent_qid>0', array(':qid'=>$iQuestionID));
                        // if the group has changed then fix the sortorder of old and new group
                        Question::model()->updateQuestionOrder($oldgid, $iSurveyID);
                        Question::model()->updateQuestionOrder($iQuestionGroupID, $iSurveyID);
                        // If some questions have conditions set on this question's answers
                        // then change the cfieldname accordingly
                        fixMovedQuestionConditions($iQuestionID, $oldgid, $iQuestionGroupID);
                    }
                    if ($oldtype != Yii::app()->request->getPost('type'))
                    {
                        Question::model()->updateAll(array('type'=>Yii::app()->request->getPost('type')), 'parent_qid=:qid', array(':qid'=>$iQuestionID));
                    }

                    Answer::model()->deleteAllByAttributes(array('qid' => $iQuestionID), 'scale_id >= :scale_id', array(':scale_id' => $iAnswerScales));

                    // Remove old subquestion scales
                    Question::model()->deleteAllByAttributes(array('parent_qid' => $iQuestionID), 'scale_id >= :scale_id', array(':scale_id' => $iSubquestionScales));
                    if(!isset($bOnError) || !$bOnError)// This really a quick hack and need a better system
                        Yii::app()->setFlashMessage(gT("Question was successfully saved."));
                    //                    }
                    //                    else
                    //                    {
                    //
                    //                        // There are conditions constraints: alert the user
                    //                        $errormsg="";
                    //                        if (!is_null($array_result['notAbove']))
                    //                        {
                    //                            $errormsg.=gT("This question relies on other question's answers and can't be moved above groupId:","js")
                    //                            . " " . $array_result['notAbove'][0][0] . " " . gT("in position","js")." ".$array_result['notAbove'][0][1]."\\n"
                    //                            . gT("See conditions:")."\\n";
                    //
                    //                            foreach ($array_result['notAbove'] as $notAboveCond)
                    //                            {
                    //                                $errormsg.="- cid:". $notAboveCond[3]."\\n";
                    //                            }
                    //
                    //                        }
                    //                        if (!is_null($array_result['notBelow']))
                    //                        {
                    //                            $errormsg.=gT("Some questions rely on this question's answers. You can't move this question below groupId:","js")
                    //                            . " " . $array_result['notBelow'][0][0] . " " . gT("in position","js")." ".$array_result['notBelow'][0][1]."\\n"
                    //                            . gT("See conditions:")."\\n";
                    //
                    //                            foreach ($array_result['notBelow'] as $notBelowCond)
                    //                            {
                    //                                $errormsg.="- cid:". $notBelowCond[3]."\\n";
                    //                            }
                    //                        }
                    //
                    //                        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
                    //                        $gid= $oldgid; // group move impossible ==> keep display on oldgid
                    //                    }
                }
                else
                {
                    Yii::app()->setFlashMessage(gT("Question could not be updated"),'error');
                }
            }
            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);

            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                if(Yii::app()->request->getPost('redirection') == "edit") {
                    $this->getController()->redirect(array('admin/questions/sa/editquestion/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
                } else {
                    $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
                }
            }
        }


        if (($sAction == "updatesurveylocalesettings") && Permission::model()->hasSurveyPermission($iSurveyID,'surveylocale','update'))
        {
            $languagelist = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $languagelist[]=Survey::model()->findByPk($iSurveyID)->language;

            Yii::app()->loadHelper('database');

            foreach ($languagelist as $langname)
            {
                if ($langname)
                {
                    $url = Yii::app()->request->getPost('url_'.$langname);
                    if ($url == 'http://') {$url="";}

                    $sURLDescription = html_entity_decode(Yii::app()->request->getPost('urldescrip_'.$langname), ENT_QUOTES, "UTF-8");
                    $sURL = html_entity_decode(Yii::app()->request->getPost('url_'.$langname), ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $short_title = Yii::app()->request->getPost('short_title_'.$langname);
                    $description = Yii::app()->request->getPost('description_'.$langname);
                    $welcome = Yii::app()->request->getPost('welcome_'.$langname);
                    $endtext = Yii::app()->request->getPost('endtext_'.$langname);

                    $short_title=$oFixCKeditor->fixCKeditor($short_title);
                    $description=$oFixCKeditor->fixCKeditor($description);
                    $welcome=$oFixCKeditor->fixCKeditor($welcome);
                    $endtext=$oFixCKeditor->fixCKeditor($endtext);

                    $data = array(
                    'surveyls_title' => $short_title,
                    'surveyls_description' => $description,
                    'surveyls_welcometext' => $welcome,
                    'surveyls_endtext' => $endtext,
                    'surveyls_url' => $sURL,
                    'surveyls_urldescription' => $sURLDescription,
                    'surveyls_dateformat' => Yii::app()->request->getPost('dateformat_'.$langname),
                    'surveyls_numberformat' => Yii::app()->request->getPost('numberformat_'.$langname)
                    );
                    $SurveyLanguageSetting=SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id'=>$iSurveyID, 'surveyls_language'=>$langname));
                    $SurveyLanguageSetting->attributes=$data;
                    $SurveyLanguageSetting->save(); // save the change to database

                }
            }
            Yii::app()->session['flashmessage'] = gT("Survey text elements successfully saved.");

            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$iSurveyID));
            }
        }

        $this->getController()->redirect(array("/admin"),"refresh");

    }

    /**
    * This is a convenience function to update/delete answer default values. If the given
    * $defaultvalue is empty then the entry is removed from table defaultvalues
    *
    * @param mixed $qid   Question ID
    * @param mixed $scale_id  Scale ID
    * @param mixed $specialtype  Special type (i.e. for  'Other')
    * @param mixed $language     Language (defaults are language specific)
    * @param mixed $defaultvalue    The default value itself
    * @param boolean $ispost   If defaultvalue is from a $_POST set this to true to properly quote things
    */
    function _updateDefaultValues($qid,$sqid,$scale_id,$specialtype,$language,$defaultvalue,$ispost)
    {
        if ($defaultvalue=='')  // Remove the default value if it is empty
        {
            DefaultValue::model()->deleteByPk(array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language));
        }
        else
        {
            $arDefaultValue = DefaultValue::model()->findByPk(array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language));

            if (is_null($arDefaultValue))
            {
                $data=array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language, 'defaultvalue'=>$defaultvalue);
                DefaultValue::model()->insertRecords($data);
            }
            else
            {
                DefaultValue::model()->updateByPk(array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language), array('defaultvalue'=>$defaultvalue));
            }
        }
    }
}
