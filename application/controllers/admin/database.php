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
        $iSurveyID = (isset($_POST['sid'])) ? $_POST['sid'] : returnGlobal('sid') ;

        $iQuestionGroupID=returnGlobal('gid');
        $iQuestionID=returnGlobal('qid');

        // TODO: This variable seems to be never set or used in any function call?
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
                if(Yii::app()->request->getPost('close-after-save') === 'true')
                    $this->getController()->redirect(array('admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));

                $this->getController()->redirect(array('admin/questions/sa/editdefaultvalues/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
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
            /* for already activated survey and rank question type : fix the maxDbAnswer before deleting answers */
            /* @todo : add it to upgrage DB system, and see for the lsa */
            if($sQuestionType=="R" && Survey::model()->findByPk($iSurveyID)->active=="Y")
            {
                QuestionAttribute::model()->find(
                    "qid = :qid AND attribute = 'max_subquestions'",
                    array(':qid' => $iQuestionID)
                );

                $answerCount=Answer::model()->countByAttributes(array('qid' => $iQuestionID,'language'=>Survey::model()->findByPk($iSurveyID)->language));
                $oQuestionAttribute = new QuestionAttribute();
                $oQuestionAttribute->qid = $iQuestionID;
                $oQuestionAttribute->attribute = 'max_subquestions';
                $oQuestionAttribute->value = $answerCount;
                $oQuestionAttribute->save();

            }

            //First delete all answers
            Answer::model()->deleteAllByAttributes(array('qid'=>$iQuestionID));
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
            for ($iScaleID=0;$iScaleID<$iScaleCount;$iScaleID++)
            {
                $iMaxCount=(int) Yii::app()->request->getPost('answercount_'.$iScaleID);
                for ($iSortOrderID=1;$iSortOrderID<$iMaxCount;$iSortOrderID++)
                {
                    $sCode=sanitize_paranoid_string(Yii::app()->request->getPost('code_'.$iSortOrderID.'_'.$iScaleID));
                    //var_dump($sCode);
                    $iAssessmentValue=(int) Yii::app()->request->getPost('assessment_'.$iSortOrderID.'_'.$iScaleID);
                    foreach ($aSurveyLanguages as $sLanguage)
                    {
                        $sAnswerText=Yii::app()->request->getPost('answer_'.$sLanguage.'_'.$iSortOrderID.'_'.$iScaleID);

                        // Fix bug with FCKEditor saving strange BR types
                        $sAnswerText=$oFixCKeditor->fixCKeditor($sAnswerText);

                        // Now we insert the answers
                        $oAnswer = new Answer;
                        $oAnswer->code              = $sCode;
                        $oAnswer->answer            = $sAnswerText;
                        $oAnswer->qid               = $iQuestionID;
                        $oAnswer->sortorder         = $iSortOrderID;
                        $oAnswer->language          = $sLanguage;
                        $oAnswer->assessment_value  = $iAssessmentValue;
                        $oAnswer->scale_id          = $iScaleID;

                        if (!$oAnswer->save())
                        {
                            $sErrors = '<br/>';
                            foreach ( $oAnswer->getErrors() as $sError)
                            {
                                $sErrors .= $sError[0].'<br/>';
                            }

                            // Let's give a new to code to the answer to save it, so user entries are not lost
                            $bAnswerSave = false;

                            while( !$bAnswerSave )
                            {
                                $oAnswer->code       = rand ( 11111 , 99999 );  // If the random code already exist (very low probablilty), answer will not be save and a new code will be generated
                                if($oAnswer->save())
                                {
                                    $sError = '<strong>'.sprintf (gT('A code has been updated to %s.'),$oAnswer->code).'</strong><br/>';
                                    $bAnswerSave = true;
                                }
                            }

                            Yii::app()->setFlashMessage(gT("Failed to update answer: ").$sCode.$sErrors,'error');
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
                Yii::app()->setFlashMessage(gT("Not all answer options were saved. This usually happens due to server limitations ( PHP setting max_input_vars) - please contact your system administrator."),'error');
            }
            else
            {
                Yii::app()->setFlashMessage(gT("Answer options were successfully saved."));
            }
            LimeExpressionManager::SetDirtyFlag();
            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                if(Yii::app()->request->getPost('close-after-save') === 'true')
                    $this->getController()->redirect(array('admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));

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

                    // Give to subquestions to edit a temporary random title to avoid title duplication on update
                    foreach ($aRows[$iScaleID][$sLanguage] as $subquestionkey=>$subquestionvalue)
                    {
                        if (substr($subquestionkey,0,3)!='new')
                        {
                            $oSubQuestion=Question::model()->find("qid=:qid AND language=:language",array(":qid"=>$subquestionkey,':language'=>$sLanguage));

                            $bAnswerSave = false;

                            while( !$bAnswerSave )
                            {
                                $oSubQuestion->title       = (string) rand ( 11111 , 99999 );  // If the random code already exist (very low probablilty), answer will not be save and a new code will be generated
                                if($oSubQuestion->save())
                                {
                                    $bAnswerSave = true;
                                }
                            }
                        }
                    }


                    foreach ($aRows[$iScaleID][$sLanguage] as $subquestionkey=>$subquestionvalue)
                    {

                        if (substr($subquestionkey,0,3)!='new')           //update record
                        {

                            //

                            $oSubQuestion=Question::model()->find("qid=:qid AND language=:language",array(":qid"=>$subquestionkey,':language'=>$sLanguage));
                            if(!is_object($oSubQuestion))
                            {
                                throw new CHttpException(502,"could not find subquestion $subquestionkey !");
                            }

                            $oSubQuestion->question_order=$iPosition+1;
                            $oSubQuestion->title=$aCodes[$iScaleID][$iPosition];
                            $oSubQuestion->question=$subquestionvalue;
                            $oSubQuestion->scale_id=$iScaleID;
                            $oSubQuestion->relevance=isset($aRelevance[$iScaleID][$iPosition]) ? $aRelevance[$iScaleID][$iPosition] : "";
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
                                $oSubQuestion->relevance=isset($aRelevance[$iScaleID][$iPosition]) ? $aRelevance[$iScaleID][$iPosition] : "";
                            }
                            else                                                //new record: additional language
                            {
                                $oSubQuestion=Question::model()->find("qid=:qid AND language=:language",array(":qid"=>$aInsertQID[$iScaleID][$iPosition],':language'=>$sLanguage));
                                if(!$oSubQuestion){
                                    $oSubQuestion=new Question;
                                }
                                $oSubQuestion->sid=$iSurveyID;
                                $oSubQuestion->gid=$iQuestionGroupID;
                                $oSubQuestion->qid=$aInsertQID[$iScaleID][$iPosition];
                                $oSubQuestion->question_order=$iPosition+1;
                                $oSubQuestion->title=$aCodes[$iScaleID][$iPosition];
                                $oSubQuestion->question=$subquestionvalue;
                                $oSubQuestion->parent_qid=$iQuestionID;
                                $oSubQuestion->language=$sLanguage;
                                $oSubQuestion->scale_id=$iScaleID;
                                $oSubQuestion->relevance=isset($aRelevance[$iScaleID][$iPosition]) ? $aRelevance[$iScaleID][$iPosition] : "";
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
                                foreach($aErrors as $sAttribute=>$aStringErrors)
                                {
                                    foreach($aStringErrors as $sStringErrors)
                                        Yii::app()->setFlashMessage(sprintf(gT("Error on %s for subquestion %s: %s"), $sAttribute,$aCodes[$iScaleID][$iPosition],$sStringErrors),'error');
                                }

                                // Let's give a new to code to the answer to save it, so user entries are not lost
                                $bAnswerSave = false;

                                while( !$bAnswerSave )
                                {
                                    $oSubQuestion->title       = rand ( 11111 , 99999 );  // If the random code already exist (very low probablilty), answer will not be save and a new code will be generated
                                    if($oSubQuestion->save())
                                    {
                                        $sError = '<strong>'.sprintf (gT('A code has been updated to %s.'),$oSubQuestion->title).'</strong><br/>';
                                        Yii::app()->setFlashMessage($sError,'error');
                                        $bAnswerSave = true;
                                    }
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
                echo 'Problem in database controller: ' . $sDBOutput;
            }
            else
            {
                if(Yii::app()->request->getPost('close-after-save') === 'true')
                {
                    $this->getController()->redirect(array('/admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
                }

                $this->getController()->redirect(array('/admin/questions/sa/subquestions/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
            }
        }


        /**
        * Insert / Copy question
        */
        if (in_array($sAction, array('insertquestion', 'copyquestion')) && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent','create'))
        {
            $survey = Survey::model()->findByPk($iSurveyID);
            $sBaseLanguage = $survey->language;

            // Abort if survey is active
            if ($survey->active !== 'N')
            {
                Yii::app()->setFlashMessage(gT("You can't insert a new question when the survey is active."),'error');
                $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/".$survey->sid), "refresh");
            }

            if (strlen(Yii::app()->request->getPost('title')) < 1)
            {
                Yii::app()->setFlashMessage(gT("The question could not be added. You must enter at least a question code."),'error');
            }
            else
            {

                // For Bootstrap Version usin YiiWheels switch :
                $_POST['mandatory'] = ( Yii::app()->request->getPost('mandatory') == '1' ) ? 'Y' : 'N' ;
                $_POST['other'] = ( Yii::app()->request->getPost('other') == '1' ) ? 'Y' : 'N' ;

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

                // For Bootstrap Version usin YiiWheels switch :
                $oQuestion->mandatory = Yii::app()->request->getPost('mandatory');
                $oQuestion->other = Yii::app()->request->getPost('other');

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
                        if (returnGlobal('copysubquestions') == 1)
                        {
                            $aSQIDMappings = array();
                            $r1 = Question::model()->getSubQuestions(returnGlobal('oldqid'));
                            $aSubQuestions = $r1->readAll();

                            foreach ($aSubQuestions as $qr1)
                            {
                                $qr1['parent_qid'] = $iQuestionID;
                                $oldqid= '';
                                if (isset($aSQIDMappings[$qr1['qid']]))
                                {
                                    $qr1['qid'] = $aSQIDMappings[$qr1['qid']];
                                }
                                else
                                {
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
                        if (returnGlobal('copyanswers') == 1)
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

                        /**
                        * Copy attribute
                        */
                        if (returnGlobal('copyattributes') == 1)
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
                        $validAttributes=\ls\helpers\questionHelper::getQuestionAttributesSettings(Yii::app()->request->getPost('type'));
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
                //admin/survey/sa/view/surveyid/
                $this->getController()->redirect(array('admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
            }
        }



        /**
        * Update question
        */
        if ($sAction == "updatequestion" && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent','update'))
        {

            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);

            $cqr=Question::model()->findByAttributes(array('qid'=>$iQuestionID));
            $oldtype=$cqr['type'];
            $oldgid=$cqr['gid'];

            $survey = Survey::model()->findByPk($iSurveyID);
            // If the survey is activate the question type may not be changed
            if ($survey->active !== 'N')
            {
                $sQuestionType=$oldtype;
            }
            else
            {
                $sQuestionType=Yii::app()->request->getPost('type');
            }


            // Remove invalid question attributes on saving


            $criteria = new CDbCriteria;
            $criteria->compare('qid',$iQuestionID);
            $validAttributes=\ls\helpers\questionHelper::getQuestionAttributesSettings($sQuestionType);
            foreach ($validAttributes as  $validAttribute)
            {
                $criteria->compare('attribute', '<>'.$validAttribute['name']);
            }
            QuestionAttribute::model()->deleteAll($criteria);
            $aLanguages=array_merge(array(Survey::model()->findByPk($iSurveyID)->language),Survey::model()->findByPk($iSurveyID)->additionalLanguages);
            foreach ($validAttributes as $validAttribute)
            {
                if ($validAttribute['i18n'])
                {
                    /* Delete invalid language : not needed but cleaner */
                    $langCriteria = new CDbCriteria;
                    $langCriteria->compare('qid',$iQuestionID);
                    $langCriteria->compare('attribute',$validAttribute['name']);
                    $langCriteria->addNotInCondition('language',$aLanguages);
                    QuestionAttribute::model()->deleteAll($langCriteria);
                    /* But not in don't work for null value : then delete IS NULL */
                    QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid AND language IS NULL',array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID));

                    foreach ($aLanguages as $sLanguage)
                    {// TODO sanitise XSS
                        $value=Yii::app()->request->getPost($validAttribute['name'].'_'.$sLanguage);
                        $iInsertCount = QuestionAttribute::model()->countByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$iQuestionID, 'language'=>$sLanguage));
                        if ($iInsertCount>0)
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
                    $value=Yii::app()->request->getPost($validAttribute['name'],'');
                    if ($validAttribute['name']=='multiflexible_step' && trim($value)!='') {
                        $value=floatval($value);
                        if ($value==0) $value=1;
                    };
                    /* we must have only one element, and this element must be null, then reset always (see #11980)*/
                    /* We can update, but : this happen only for admin and not a lot, then : delete + add */
                    QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$iQuestionID));
                    if($value!=$validAttribute['default'] && trim($value)!==""){
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
            $iAnswerScales = $aQuestionTypeList[$sQuestionType]['answerscales'];
            $iSubquestionScales = $aQuestionTypeList[$sQuestionType]['subquestions'];

            // These are the questions types that have the other option therefore we set everything else to 'No Other'
            if (($sQuestionType!= "L") && ($sQuestionType!= "!") && ($sQuestionType!= "P") && ($sQuestionType!="M"))
            {
                $_POST['other']='N';
            }

            // These are the questions types that have no validation - so zap it accordingly

            if ($sQuestionType== "!" || $sQuestionType== "L" || $sQuestionType== "M" || $sQuestionType== "P" ||
            $sQuestionType== "F" || $sQuestionType== "H" ||
            $sQuestionType== "X" || $sQuestionType== "")
            {
                $_POST['preg']='';
            }


            // For Bootstrap Version usin YiiWheels switch :
            $_POST['mandatory'] = ( Yii::app()->request->getPost('mandatory') == '1' ) ? 'Y' : 'N' ;
            $_POST['other'] = ( Yii::app()->request->getPost('other') == '1' ) ? 'Y' : 'N' ;

            // These are the questions types that have no mandatory property - so zap it accordingly
            if ($sQuestionType== "X" || $sQuestionType== "|")
            {
                $_POST['mandatory']='N';
            }


            if ($oldtype != $sQuestionType)
            {
                // TMSW Condition->Relevance:  Do similar check via EM, but do allow such a change since will be easier to modify relevance
                //Make sure there are no conditions based on this question, since we are changing the type
                $ccresult = Condition::model()->findAllByAttributes(array('cqid'=>$iQuestionID));
                $cccount=count($ccresult);
                foreach ($ccresult as $ccr) {$qidarray[]=$ccr['qid'];}
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
                                'type' => $sQuestionType,
                                'title' => Yii::app()->request->getPost('title'),
                                'question' => $sQuestionText,
                                'preg' => Yii::app()->request->getPost('preg'),
                                'help' => $sQuestionHelp,
                                'gid' => $iQuestionGroupID,
                                'other' => Yii::app()->request->getPost('other'),
                                'mandatory' => Yii::app()->request->getPost('mandatory'),
                                'relevance' => Yii::app()->request->getPost('relevance'),
                            );

                            // Update question module
                            if(Yii::app()->request->getPost('module_name')!='')
                            {
                                // The question module is not empty. So it's an external question module.
                                $udata['modulename'] = Yii::app()->request->getPost('module_name');
                            }
                            else
                            {
                                // If it was a module before, we must
                                $udata['modulename'] = '';
                            }

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
                            {
                                $oQuestion->$k = $v;
                            }

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
                    // Update subquestions
                    if ($oldtype != $sQuestionType)
                    {
                        Question::model()->updateAll(array('type'=>$sQuestionType), 'parent_qid=:qid', array(':qid'=>$iQuestionID));
                    }

                    // Update subquestions if question module
                    if(Yii::app()->request->getPost('module_name')!='')
                    {
                        // The question module is not empty. So it's an external question module.
                        Question::model()->updateAll(array('modulename'=>Yii::app()->request->getPost('module_name')), 'parent_qid=:qid', array(':qid'=>$iQuestionID));
                    }
                    else
                    {
                        // If it was a module before, we must
                        Question::model()->updateAll(array('modulename'=>''), 'parent_qid=:qid', array(':qid'=>$iQuestionID));
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
                $closeAfterSave = Yii::app()->request->getPost('close-after-save') === 'true';

                if ($closeAfterSave)
                {
                    // Redirect to summary
                    $this->getController()->redirect(array('admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
                }
                else
                {
                    // Redirect to edit
                    $this->getController()->redirect(array('admin/questions/sa/editquestion/surveyid/'.$iSurveyID.'/gid/'.$iQuestionGroupID.'/qid/'.$iQuestionID));
                    // This works too: $this->getController()->redirect(Yii::app()->request->urlReferrer);
                }
            }
        }

        /**
        * updatesurveylocalesettings
        */
        if (($sAction == "updatesurveylocalesettings") && (Permission::model()->hasSurveyPermission($iSurveyID,'surveylocale','update') || Permission::model()->hasSurveyPermission($iSurveyID,'surveysettings','update')))
        {
            $languagelist = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $languagelist[]=Survey::model()->findByPk($iSurveyID)->language;

            Yii::app()->loadHelper('database');

            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'update'))
            {

                foreach ($languagelist as $langname)
                {
                    if ($langname)
                    {

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
                Yii::app()->setFlashMessage(gT("Survey text elements successfully saved."));
            }
            ////////////////////////////////////////////////////////////////////////////////////
            // General settings (copy / paste from surveyadmin::update)
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))
            {
                // Preload survey
                $oSurvey=Survey::model()->findByPk($iSurveyID);

                // Save plugin settings : actually leave it before saving core : we are sure core settings is saved in LS way.
                $pluginSettings = App()->request->getPost('plugin', array());
                foreach($pluginSettings as $plugin => $settings)
                {
                    $settingsEvent = new PluginEvent('newSurveySettings');
                    $settingsEvent->set('settings', $settings);
                    $settingsEvent->set('survey', $iSurveyID);
                    App()->getPluginManager()->dispatchEvent($settingsEvent, $plugin);
                }

                /* Start to fix some param before save (TODO : use models directly ?) */
                /* Date management */
                Yii::app()->loadHelper('surveytranslator');
                $formatdata=getDateFormatData(Yii::app()->session['dateformat']);
                Yii::app()->loadLibrary('Date_Time_Converter');
                $startdate = App()->request->getPost('startdate');
                if (trim($startdate)=="")
                {
                    $startdate=null;
                }
                else
                {
                    Yii::app()->loadLibrary('Date_Time_Converter');
                    $datetimeobj = new date_time_converter($startdate,$formatdata['phpdate'].' H:i'); //new Date_Time_Converter($startdate,$formatdata['phpdate'].' H:i');
                    $startdate=$datetimeobj->convert("Y-m-d H:i:s");
                }
                $expires = App()->request->getPost('expires');
                if (trim($expires)=="")
                {
                    $expires=null;
                }
                else
                {
                    $datetimeobj = new date_time_converter($expires, $formatdata['phpdate'].' H:i'); //new Date_Time_Converter($expires, $formatdata['phpdate'].' H:i');
                    $expires=$datetimeobj->convert("Y-m-d H:i:s");
                }

                // Only owner and superadmins may change the survey owner
                if ($oSurvey->owner_id == Yii::app()->session['loginID'] || Permission::model()->hasGlobalPermission('superadmin','read'))
                {
                    $oSurvey->owner_id = Yii::app()->request->getPost('owner_id');
                }
                $oSurvey->admin =  Yii::app()->request->getPost('admin');
                $oSurvey->expires =  $expires;
                $oSurvey->startdate =  $startdate;
                $oSurvey->faxto = App()->request->getPost('faxto');
                $oSurvey->format = App()->request->getPost('format');
                $oSurvey->template = Yii::app()->request->getPost('template');
                $oSurvey->assessments = App()->request->getPost('assessments')=='1'?'Y':'N';
                $oSurvey->additional_languages =  implode(' ',Yii::app()->request->getPost('additional_languages',array()));
                if ($oSurvey->active!='Y')
                {
                    $oSurvey->anonymized = App()->request->getPost('anonymized')=='1'?'Y':'N';
                    $oSurvey->savetimings = App()->request->getPost('savetimings')=='1'?'Y':'N';
                    $oSurvey->datestamp = App()->request->getPost('datestamp')=='1'?'Y':'N';
                    $oSurvey->ipaddr = App()->request->getPost('ipaddr')=='1'?'Y':'N';
                    $oSurvey->refurl = App()->request->getPost('refurl')=='1'?'Y':'N';
                }
                $oSurvey->publicgraphs = App()->request->getPost('publicgraphs')=='1'?'Y':'N';
                $oSurvey->usecookie = App()->request->getPost('usecookie')=='1'?'Y':'N';
                $oSurvey->allowregister = App()->request->getPost('allowregister')=='1'?'Y':'N';
                $oSurvey->allowsave = App()->request->getPost('allowsave')=='1'?'Y':'N';
                $oSurvey->navigationdelay = App()->request->getPost('navigationdelay');
                $oSurvey->printanswers = App()->request->getPost('printanswers')=='1'?'Y':'N';
                $oSurvey->publicstatistics = App()->request->getPost('publicstatistics')=='1'?'Y':'N';
                $oSurvey->autoredirect = App()->request->getPost('autoredirect')=='1'?'Y':'N';
                $oSurvey->showxquestions = App()->request->getPost('showxquestions')=='1'?'Y':'N';
                $oSurvey->showgroupinfo = App()->request->getPost('showgroupinfo');
                $oSurvey->showqnumcode = App()->request->getPost('showqnumcode');
                $oSurvey->shownoanswer = App()->request->getPost('shownoanswer')=='1'?'Y':'N';
                $oSurvey->showwelcome = App()->request->getPost('showwelcome')=='1'?'Y':'N';
                $oSurvey->allowprev = App()->request->getPost('allowprev')=='1'?'Y':'N';
                $oSurvey->questionindex = App()->request->getPost('questionindex');
                $oSurvey->nokeyboard = App()->request->getPost('nokeyboard')=='1'?'Y':'N';
                $oSurvey->showprogress = App()->request->getPost('showprogress')=='1'?'Y':'N';
                $oSurvey->listpublic = App()->request->getPost('listpublic')=='1'?'Y':'N';
                $oSurvey->htmlemail = App()->request->getPost('htmlemail')=='1'?'Y':'N';
                $oSurvey->sendconfirmation = App()->request->getPost('sendconfirmation')=='1'?'Y':'N';
                $oSurvey->tokenanswerspersistence = App()->request->getPost('tokenanswerspersistence')=='1'?'Y':'N';
                $oSurvey->alloweditaftercompletion = App()->request->getPost('alloweditaftercompletion')=='1'?'Y':'N';
                $oSurvey->usecaptcha = Survey::transcribeCaptchaOptions();
                $oSurvey->emailresponseto = App()->request->getPost('emailresponseto');
                $oSurvey->emailnotificationto = App()->request->getPost('emailnotificationto');
                $oSurvey->googleanalyticsapikeysetting = App()->request->getPost('googleanalyticsapikeysetting');
                if( $oSurvey->googleanalyticsapikeysetting == "Y")
                {
                    $oSurvey->googleanalyticsapikey = App()->request->getPost('googleanalyticsapikey');
                }
                else if( $oSurvey->googleanalyticsapikeysetting == "G")
                {
                    $oSurvey->googleanalyticsapikey = "9999useGlobal9999";
                }
                else if( $oSurvey->googleanalyticsapikeysetting == "N")
                {
                    $oSurvey->googleanalyticsapikey = "";
                }

                $oSurvey->googleanalyticsstyle = App()->request->getPost('googleanalyticsstyle');
                $oSurvey->tokenlength = (App()->request->getPost('tokenlength')<5  || App()->request->getPost('tokenlength')>36)?15:App()->request->getPost('tokenlength');
                $oSurvey->adminemail = App()->request->getPost('adminemail');
                $oSurvey->bounce_email = App()->request->getPost('bounce_email');

                $event = new PluginEvent('beforeSurveySettingsSave');
                $event->set('modifiedSurvey', $oSurvey);
                App()->getPluginManager()->dispatchEvent($event);

                if ($oSurvey->save())
                {
                    Yii::app()->setFlashMessage(gT("Survey settings were successfully saved."));
                }
                else
                {
                    Yii::app()->setFlashMessage(gT("Survey could not be updated."),"error");
                    tracevar($oSurvey->getErrors());
                }
            }
            /* Reload $oSurvey (language are fixed : need it ?) */
            $oSurvey=Survey::model()->findByPk($iSurveyID);

            /* Delete removed language cleanLanguagesFromSurvey do it already why redo it (cleanLanguagesFromSurvey must be moved to model) ?*/
            $aAvailableLanguage=$oSurvey->getAllLanguages();
            $oCriteria = new CDbCriteria;
            $oCriteria->compare('surveyls_survey_id',$iSurveyID);
            $oCriteria->addNotInCondition('surveyls_language',$aAvailableLanguage);
            SurveyLanguageSetting::model()->deleteAll($oCriteria);

            /* Add new language fixLanguageConsistency do it ?*/
            foreach ($oSurvey->additionalLanguages as $sLang)
            {
                if ($sLang)
                {
                    $oLanguageSettings = SurveyLanguageSetting::model()->find('surveyls_survey_id=:surveyid AND surveyls_language=:langname', array(':surveyid'=>$iSurveyID,':langname'=>$sLang));
                    if(!$oLanguageSettings)
                    {
                        $oLanguageSettings= new SurveyLanguageSetting;
                        $languagedetails=getLanguageDetails($sLang);
                        $oLanguageSettings->surveyls_survey_id = $iSurveyID;
                        $oLanguageSettings->surveyls_language = $sLang;
                        $oLanguageSettings->surveyls_title = ''; // Not in default model ?
                        $oLanguageSettings->surveyls_dateformat = $languagedetails['dateformat'];
                        if(!$oLanguageSettings->save())
                        {
                            Yii::app()->setFlashMessage(gT("Survey language could not be created."),"error");
                            tracevar($oLanguageSettings->getErrors());
                        }
                    }
                }
            }
            /* Language fix : remove and add question/group */
            cleanLanguagesFromSurvey($iSurveyID,implode(" ",$oSurvey->additionalLanguages));
            fixLanguageConsistency($iSurveyID,implode(" ",$oSurvey->additionalLanguages));

            // Url params in json
            $aURLParams=json_decode(Yii::app()->request->getPost('allurlparams'),true);
            SurveyURLParameter::model()->deleteAllByAttributes(array('sid'=>$iSurveyID));
            if(isset($aURLParams))
            {
                foreach($aURLParams as $aURLParam)
                {
                    $aURLParam['parameter']=trim($aURLParam['parameter']);
                    if ($aURLParam['parameter']=='' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/',$aURLParam['parameter']) || $aURLParam['parameter']=='sid' || $aURLParam['parameter']=='newtest' || $aURLParam['parameter']=='token' || $aURLParam['parameter']=='lang')
                    {
                        continue;  // this parameter name seems to be invalid - just ignore it
                    }
                    $aURLParam['targetqid']  = $aURLParam['qid'];
                    $aURLParam['targetsqid'] = $aURLParam['sqid'];
                    unset($aURLParam['actionBtn']);
                    unset($aURLParam['title']);
                    unset($aURLParam['id']);
                    unset($aURLParam['qid']);
                    unset($aURLParam['targetQuestionText']);
                    unset($aURLParam['sqid']);
                    if ($aURLParam['targetqid']=='') $aURLParam['targetqid']=NULL;
                    if ($aURLParam['targetsqid']=='') $aURLParam['targetsqid']=NULL;
                    $aURLParam['sid']=$iSurveyID;
                    $param = new SurveyURLParameter;
                    foreach ($aURLParam as $k => $v)
                        $param->$k = $v;
                    $param->save();
                }
            }

            ////////////////////////////////////////



            if ($sDBOutput != '')
            {
                echo $sDBOutput;
            }
            else
            {
                if(Yii::app()->request->getPost('close-after-save') === 'true')
                    $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$iSurveyID));

                $this->getController()->redirect(array('/admin/survey/sa/editlocalsettings/surveyid/'.$iSurveyID));
            }
        }

        $this->getController()->redirect(array("/admin"),"refresh");

    }

    /**
    * This is a convenience function to update/delete answer default values. If the given
    * $defaultvalue is empty then the entry is removed from table defaultvalues
    *
    * @param mixed $qid   Question ID
    * @param integer $scale_id  Scale ID
    * @param string $specialtype  Special type (i.e. for  'Other')
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
