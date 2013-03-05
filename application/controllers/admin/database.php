<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id$
*
*/
/**
* Database
*g
* @package LimeSurvey
* @author
* @copyright 2011
* @version $Id$
* @access public
*/
class database extends Survey_Common_Action
{
    /**
    * Database::index()
    *
    * @param mixed $action
    * @return
    */
    function index($sa = null)
    {

        $action=Yii::app()->request->getPost('action');
        $clang = $this->getController()->lang;
        $postsid=returnGlobal('sid');
        $postgid=returnGlobal('gid');
        $postqid=returnGlobal('qid');
        $postqaid=returnGlobal('qaid');
        $databaseoutput = '';
        $surveyid = returnGlobal('sid');
        $gid = returnGlobal('gid');
        $qid = returnGlobal('qid');
        // if $action is not passed, check post data.

        if(Yii::app()->getConfig('filterxsshtml') && Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
        {
            $filter = new CHtmlPurifier();
            $filter->options = array('URI.AllowedSchemes'=>array(
            'http' => true,
            'https' => true,
            ));
            $xssfilter = true;
        }
        else
            $xssfilter = false;

        if ($action == "updatedefaultvalues" && hasSurveyPermission($surveyid, 'surveycontent','update'))
        {

            $questlangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;
            array_unshift($questlangs,$baselang);

            Questions::model()->updateAll(array('same_default'=> Yii::app()->request->getPost('samedefault')?1:0), 'sid=:sid ANd qid=:qid', array(':sid'=>$surveyid, ':qid'=>$qid));

            $resrow = Questions::model()->findByAttributes(array('qid'=>$qid));
            $questiontype = $resrow['type'];

            $qtproperties=getQuestionTypeList('','array');
            if ($qtproperties[$questiontype]['answerscales']>0 && $qtproperties[$questiontype]['subquestions']==0)
            {
                for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['answerscales'];$scale_id++)
                {
                    foreach ($questlangs as $language)
                    {
                        if (!is_null(Yii::app()->request->getPost('defaultanswerscale_'.$scale_id.'_'.$language)))
                        {
                            $this->_updateDefaultValues($qid,0,$scale_id,'',$language,Yii::app()->request->getPost('defaultanswerscale_'.$scale_id.'_'.$language),true);
                        }
                        if (!is_null(Yii::app()->request->getPost('other_'.$scale_id.'_'.$language)))
                        {
                            $this->_updateDefaultValues($qid,0,$scale_id,'other',$language,Yii::app()->request->getPost('other_'.$scale_id.'_'.$language),true);
                        }
                    }
                }
            }
            if ($qtproperties[$questiontype]['subquestions']>0)
            {

                foreach ($questlangs as $language)
                {

                    $sqresult = Questions::model()->findAllByAttributes(array('sid'=>$surveyid, 'gid'=>$gid, 'parent_qid'=>$qid, 'language'=>$language, 'scale_id'=>0));

                    for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['subquestions'];$scale_id++)
                    {

                        foreach ($sqresult as $aSubquestionrow)
                        {
                            if (!is_null(Yii::app()->request->getPost('defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid'])))
                            {
                                $this->_updateDefaultValues($qid,$aSubquestionrow['qid'],$scale_id,'',$language,Yii::app()->request->getPost('defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid']),true);
                            }
                        }
                    }
                }
            }
            if ($qtproperties[$questiontype]['answerscales']==0 && $qtproperties[$questiontype]['subquestions']==0)
            {
                foreach ($questlangs as $language)
                {
                    if (!is_null(Yii::app()->request->getPost('defaultanswerscale_0_'.$language.'_0')))
                    {
                        $this->_updateDefaultValues($postqid,0,0,'',$language,Yii::app()->request->getPost('defaultanswerscale_0_'.$language.'_0'),true);
                    }
                }
            }
            Yii::app()->session['flashmessage'] = $clang->gT("Default value settings were successfully saved.");
            LimeExpressionManager::SetDirtyFlag();

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
            }
        }


        if ($action == "updateansweroptions" && hasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            Yii::app()->loadHelper('database');
            $anslangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;

            $alllanguages = $anslangs;
            array_unshift($alllanguages,$baselang);

            $resrow = Questions::model()->findByAttributes(array('qid'=>$qid));
            $questiontype = $resrow['type'];    // Checked)
            $qtypes=getQuestionTypeList('','array');
            $scalecount=$qtypes[$questiontype]['answerscales'];

            $count=0;
            $invalidCode = 0;
            $duplicateCode = 0;

            //require_once("../classes/inputfilter/class.inputfilter_clean.php");
            //$myFilter = new InputFilter('','',1,1,1);

            //First delete all answers
            Answers::model()->deleteAllByAttributes(array('qid'=>$qid));

            LimeExpressionManager::RevertUpgradeConditionsToRelevance($surveyid);

            for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
            {
                $maxcount=(int) Yii::app()->request->getPost('answercount_'.$scale_id);

                for ($sortorderid=1;$sortorderid<$maxcount;$sortorderid++)
                {
                    $code=sanitize_paranoid_string(Yii::app()->request->getPost('code_'.$sortorderid.'_'.$scale_id));
                    if (Yii::app()->request->getPost('oldcode_'.$sortorderid.'_'.$scale_id)) {
                        $oldcode=sanitize_paranoid_string(Yii::app()->request->getPost('oldcode_'.$sortorderid.'_'.$scale_id));
                        if($code !== $oldcode) {
                            Conditions::model()->updateAll(array('value'=>$code), 'cqid=:cqid AND value=:value', array(':cqid'=>$qid, ':value'=>$oldcode));
                        }
                    }

                    $assessmentvalue=(int) Yii::app()->request->getPost('assessment_'.$sortorderid.'_'.$scale_id);
                    foreach ($alllanguages as $language)
                    {
                        $answer=Yii::app()->request->getPost('answer_'.$language.'_'.$sortorderid.'_'.$scale_id);

                        if ($xssfilter)
                        {
                            $answer=$filter->purify($answer);
                        }
                        else
                        {
                            $answer=html_entity_decode($answer, ENT_QUOTES, "UTF-8");
                        }
                        // Fix bug with FCKEditor saving strange BR types
                        $answer=fixCKeditorText($answer);

                        // Now we insert the answers
                        $result=Answers::model()->insertRecords(array('code'=>$code,
                        'answer'=>$answer,
                        'qid'=>$qid,
                        'sortorder'=>$sortorderid,
                        'language'=>$language,
                        'assessment_value'=>$assessmentvalue,
                        'scale_id'=>$scale_id));
                        if (!$result) // Checked
                        {
                            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update answers","js")."\")\n //-->\n</script>\n";
                        }
                    } // foreach ($alllanguages as $language)

                    if(isset($oldcode) && $code !== $oldcode) {
                        Conditions::model()->updateAll(array('value'=>$code), 'cqid=:cqid AND value=:value', array(':cqid'=>$qid, ':value'=>$oldcode));
                    }

                }  // for ($sortorderid=0;$sortorderid<$maxcount;$sortorderid++)
            }  //  for ($scale_id=0;

            LimeExpressionManager::UpgradeConditionsToRelevance($surveyid);

            if ($invalidCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Answers with a code of 0 (zero) or blank code are not allowed, and will not be saved","js")."\")\n //-->\n</script>\n";
            if ($duplicateCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Duplicate codes found, these entries won't be updated","js")."\")\n //-->\n</script>\n";

            Yii::app()->session['flashmessage']= $clang->gT("Answer options were successfully saved.");
            LimeExpressionManager::SetDirtyFlag();
            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->getController()->redirect($this->getController()->createUrl('/admin/question/sa/answeroptions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
            }

            //$action='editansweroptions';

        }


        if ($action == "updatesubquestions" && hasSurveyPermission($surveyid, 'surveycontent','update'))
        {

            Yii::app()->loadHelper('database');
            $anslangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;
            array_unshift($anslangs,$baselang);

            $row = Questions::model()->findByAttributes(array('qid'=>$qid));
            $questiontype = $row['type'];    // Checked
            $qtypes=getQuestionTypeList('','array');
            $scalecount=$qtypes[$questiontype]['subquestions'];

            $clang = $this->getController()->lang;
            // First delete any deleted ids
            $deletedqids=explode(' ', trim(Yii::app()->request->getPost('deletedqids')));

            LimeExpressionManager::RevertUpgradeConditionsToRelevance($surveyid);

            foreach ($deletedqids as $deletedqid)
            {
                $deletedqid=(int)$deletedqid;
                if ($deletedqid>0)
                { // don't remove undefined
                    $result = Questions::model()->deleteAllByAttributes(array('qid'=>$deletedqid));
                    if (!$result)
                    {
                        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete answer","js")." \")\n //-->\n</script>\n";
                    }
                }
            }

            //Determine ids by evaluating the hidden field
            $rows=array();
            $codes=array();
            $oldcodes=array();
            foreach ($_POST as $postkey=>$postvalue)
            {
                $postkey=explode('_',$postkey);
                if ($postkey[0]=='answer')
                {
                    $rows[$postkey[3]][$postkey[1]][$postkey[2]]=$postvalue;
                }
                if ($postkey[0]=='code')
                {
                    $codes[$postkey[2]][]=$postvalue;
                }
                if ($postkey[0]=='oldcode')
                {
                    $oldcodes[$postkey[2]][]=$postvalue;
                }
            }
            $count=0;
            $invalidCode = 0;
            $duplicateCode = 0;
            $dupanswers = array();
            /*
            for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
            {

            // Find duplicate codes and add these to dupanswers array
            $foundCat=array_count_values($codes);
            foreach($foundCat as $key=>$value){
            if($value>=2){
            $dupanswers[]=$key;
            }
            }
            }
            */
            //require_once("../classes/inputfilter/class.inputfilter_clean.php");
            //$myFilter = new InputFilter('','',1,1,1);


            //$insertqids=array(); //?
            $insertqid =array();
            for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
            {
                foreach ($anslangs as $language)
                {
                    $position=0;
                    foreach ($rows[$scale_id][$language] as $subquestionkey=>$subquestionvalue)
                    {
                        if (substr($subquestionkey,0,3)!='new')
                        {
                            Questions::model()->updateByPk(array('qid'=>$subquestionkey, 'language'=>$language), array('question_order'=>$position+1, 'title'=>$codes[$scale_id][$position], 'question'=>$subquestionvalue, 'scale_id'=>$scale_id));

                            if(isset($oldcodes[$scale_id][$position]) && $codes[$scale_id][$position] !== $oldcodes[$scale_id][$position])
                            {
                                Conditions::model()->updateAll(array('cfieldname'=>'+'.$surveyid.'X'.$gid.'X'.$qid.$codes[$scale_id][$position], 'value'=>$codes[$scale_id][$position]), 'cqid=:cqid AND cfieldname=:cfieldname AND value=:value', array(':cqid'=>$qid, ':cfieldname'=>$surveyid.'X'.$gid.'X'.$qid, ':value'=>$oldcodes[$scale_id][$position]));

                            }

                        }
                        else
                        {
                            if (!isset($insertqid[$scale_id][$position]))
                            {
                                $insertqid[$scale_id][$position]=Questions::model()->insertRecords(array('sid'=>$surveyid, 'gid'=>$gid, 'question_order'=>$position+1,'title'=>$codes[$scale_id][$position],'question'=>$subquestionvalue,'parent_qid'=>$qid,'language'=>$language,'scale_id'=>$scale_id));
                            }
                            else
                            {
                                switchMSSQLIdentityInsert('questions',true);
                                Questions::model()-> insertRecords(array('qid'=>$insertqid[$scale_id][$position],'sid'=>$surveyid, 'gid'=>$gid, 'question_order'=>$position+1,'title'=>$codes[$scale_id][$position],'question'=>$subquestionvalue,'parent_qid'=>$qid,'language'=>$language,'scale_id'=>$scale_id));
                                switchMSSQLIdentityInsert('questions',true);
                            }
                        }
                        $position++;
                    }

                }
            }

            LimeExpressionManager::UpgradeConditionsToRelevance($surveyid);

            //include("surveytable_functions.php");
            //surveyFixColumns($surveyid);
            Yii::app()->session['flashmessage'] = $clang->gT("Subquestions were successfully saved.");

            //$action='editsubquestions';
            LimeExpressionManager::SetDirtyFlag();
            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->getController()->redirect($this->getController()->createUrl('/admin/question/sa/subquestions/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
            }
        }

        if (in_array($action, array('insertquestion', 'copyquestion')) && hasSurveyPermission($surveyid, 'surveycontent','create'))
        {
            $baselang = Survey::model()->findByPk($surveyid)->language;
            if (strlen(Yii::app()->request->getPost('title')) < 1)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n "
                ."alert(\"".$clang->gT("The question could not be added. You must enter at least a question code.","js")."\")\n "
                ."//-->\n</script>\n";
            }
            else
            {
                if (Yii::app()->request->getPost('questionposition',"")!="")
                {
                    $question_order= intval(Yii::app()->request->getPost('questionposition'));
                    //Need to renumber all questions on or after this
                    $cdquery = "UPDATE {{questions}} SET question_order=question_order+1 WHERE gid=:gid AND question_order >= :order";
                    $cdresult=Yii::app()->db->createCommand($cdquery)->bindValues(array(':gid'=>$gid, ':order'=>$question_order))->query();
                } else {
                    $question_order=(getMaxQuestionOrder($gid,$surveyid));
                    $question_order++;
                }
                $_POST['title'] = html_entity_decode(Yii::app()->request->getPost('title'), ENT_QUOTES, "UTF-8");
                $_POST['question_'.$baselang] = html_entity_decode(Yii::app()->request->getPost('question_'.$baselang), ENT_QUOTES, "UTF-8");
                $_POST['help_'.$baselang] = html_entity_decode(Yii::app()->request->getPost('help_'.$baselang), ENT_QUOTES, "UTF-8");


                // Fix bug with FCKEditor saving strange BR types
                if ($xssfilter)
                {
                    $_POST['title']=$filter->purify($_POST['title']);
                    $_POST['question_'.$baselang]=$filter->purify($_POST['question_'.$baselang]);
                    $_POST['help_'.$baselang]=$filter->purify($_POST['help_'.$baselang]);
                }
                else
                {
                    $_POST['title']=fixCKeditorText(Yii::app()->request->getPost('title'));
                    $_POST['question_'.$baselang]=fixCKeditorText(Yii::app()->request->getPost('question_'.$baselang));
                    $_POST['help_'.$baselang]=fixCKeditorText(Yii::app()->request->getPost('help_'.$baselang));
                }

                $data = array(
                'sid' => $surveyid,
                'gid' => $gid,
                'type' => Yii::app()->request->getPost('type'),
                'title' => Yii::app()->request->getPost('title'),
                'question' => Yii::app()->request->getPost('question_'.$baselang),
                'preg' => Yii::app()->request->getPost('preg'),
                'help' => Yii::app()->request->getPost('help_'.$baselang),
                'other' => Yii::app()->request->getPost('other'),
                'mandatory' => Yii::app()->request->getPost('mandatory'),
                'relevance' => Yii::app()->request->getPost('relevance'),
                'question_order' => $question_order,
                'language' => $baselang
                );
                 $qid=Questions::model()->insertRecords($data);
                // Add other languages
                if ($qid)
                {
                    $addlangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                    foreach ($addlangs as $alang)
                    {
                        if ($alang != "")
                        {
                            $data = array(
                            'qid' => $qid,
                            'sid' => $surveyid,
                            'gid' => $gid,
                            'type' => Yii::app()->request->getPost('type'),
                            'title' => Yii::app()->request->getPost('title'),
                            'question' => Yii::app()->request->getPost('question_'.$alang),
                            'preg' => Yii::app()->request->getPost('preg'),
                            'help' => Yii::app()->request->getPost('help_'.$alang),
                            'other' => Yii::app()->request->getPost('other'),
                            'mandatory' => Yii::app()->request->getPost('mandatory'),
                            'question_order' => $question_order,
                            'language' => $alang
                            );
                            $langqid=Questions::model()->insertRecords($data);

                            // Checked */
                            if (!$langqid)
                            {
                                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".sprintf($clang->gT("Question in language %s could not be created.","js"),$alang)."\\n\")\n //-->\n</script>\n";
                            }
                        }
                    }
                }


                if (!$qid)
                {
                    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n\")\n //-->\n</script>\n";

                } else {
                    if ($action == 'copyquestion') {
                        if (returnGlobal('copysubquestions') == "Y")
                        {
                            $aSQIDMappings = array();
                            $r1 = Questions::model()->getSubQuestions(returnGlobal('oldqid'));

                            while ($qr1 = $r1->read())
                            {
                                $qr1['parent_qid'] = $qid;
                                if (isset($aSQIDMappings[$qr1['qid']]))
                                {
                                    $qr1['qid'] = $aSQIDMappings[$qr1['qid']];
                                } else {
                                    $oldqid = $qr1['qid'];
                                    unset($qr1['qid']);
                                }
                                $qr1['gid'] = $postgid;
                                $iInsertID = Questions::model()->insertRecords($qr1);
                                if (!isset($qr1['qid']))
                                {
                                    $aSQIDMappings[$oldqid] = $iInsertID;
                                }
                            }
                        }
                        if (returnGlobal('copyanswers') == "Y")
                        {
                            $r1 = Answers::model()->getAnswers(returnGlobal('oldqid'));
                            while ($qr1 = $r1->read())
                            {
                                Answers::model()->insertRecords(array(
                                'qid' => $qid,
                                'code' => $qr1['code'],
                                'answer' => $qr1['answer'],
                                'sortorder' => $qr1['sortorder'],
                                'language' => $qr1['language'],
                                'scale_id' => $qr1['scale_id']
                                ));
                            }
                        }
                        if (returnGlobal('copyattributes') == "Y")
                        {
                            $r1 = Question_attributes::model()->getQuestionAttributes(returnGlobal('oldqid'));
                            while($qr1 = $r1->read())
                            {
                                $qr1['qid']=$qid;
                                unset($qr1['qaid']);
                                Question_attributes::model()->insertRecords($qr1);
                            }
                        }
                    } else {
                        $qattributes = questionAttributes();
                        $validAttributes = $qattributes[Yii::app()->request->getPost('type')];
                        $aLanguages=array_merge(array(Survey::model()->findByPk($surveyid)->language),Survey::model()->findByPk($surveyid)->additionalLanguages);

                        foreach ($validAttributes as $validAttribute)
                        {
                            if ($validAttribute['i18n'])
                            {
                                foreach ($aLanguages as $sLanguage)
                                {// TODO sanitise XSS
                                    $value=Yii::app()->request->getPost($validAttribute['name'].'_'.$sLanguage);
                                    $result = Question_attributes::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$qid, 'language'=>$sLanguage));
                                    if (count($result)>0)
                                    {
                                        if ($value!='')
                                        {
                                            Question_attributes::model()->updateAll(array('value'=>$value), 'attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid, ':language'=>$sLanguage));
                                        }
                                        else
                                        {
                                            Question_attributes::model()->deleteAll('attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid, ':language'=>$sLanguage));
                                        }
                                    }
                                    elseif($value!='')
                                    {
                                        $attribute = new Question_attributes;
                                        $attribute->qid = $qid;
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

                                $result = Question_attributes::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$qid));
                                if (count($result)>0)
                                {
                                    if($value!=$validAttribute['default'] && trim($value)!="")
                                    {
                                        Question_attributes::model()->updateAll(array('value'=>$value),'attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid));
                                    }
                                    else
                                    {
                                        Question_attributes::model()->deleteAll('attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid));
                                    }
                                }
                                elseif($value!=$validAttribute['default'] && trim($value)!="")
                                {
                                    $attribute = new Question_attributes;
                                    $attribute->qid = $qid;
                                    $attribute->value = $value;
                                    $attribute->attribute = $validAttribute['name'];
                                    $attribute->save();
                                }
                            }
                        }

                    }
                    Questions::model()->updateQuestionOrder($gid, $surveyid);
                    Yii::app()->session['flashmessage'] =  $clang->gT("Question was successfully added.");

                }

            }

            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
            }
        }

        if ($action == "updatequestion" && hasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($surveyid);

            $cqr=Questions::model()->findByAttributes(array('qid'=>$qid));
            $oldtype=$cqr['type'];
            $oldgid=$cqr['gid'];

            // Remove invalid question attributes on saving
            $qattributes=questionAttributes();

            $criteria = new CDbCriteria;
            $criteria->compare('qid',$qid);
            if (isset($qattributes[Yii::app()->request->getPost('type')])){
                $validAttributes=$qattributes[Yii::app()->request->getPost('type')];
                foreach ($validAttributes as  $validAttribute)
                {
                    $criteria->compare('attribute', '<>'.$validAttribute['name']);
                }
            }
            Question_attributes::model()->deleteAll($criteria);

            $aLanguages=array_merge(array(Survey::model()->findByPk($surveyid)->language),Survey::model()->findByPk($surveyid)->additionalLanguages);


            //now save all valid attributes
            $validAttributes=$qattributes[Yii::app()->request->getPost('type')];

            foreach ($validAttributes as $validAttribute)
            {
                if ($validAttribute['i18n'])
                {
                    foreach ($aLanguages as $sLanguage)
                    {// TODO sanitise XSS
                        $value=Yii::app()->request->getPost($validAttribute['name'].'_'.$sLanguage);
                        $result = Question_attributes::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$qid, 'language'=>$sLanguage));
                        if (count($result)>0)
                        {
                            if ($value!='')
                            {
                                Question_attributes::model()->updateAll(array('value'=>$value), 'attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid, ':language'=>$sLanguage));
                            }
                            else
                            {
                                Question_attributes::model()->deleteAll('attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid, ':language'=>$sLanguage));
                            }
                        }
                        elseif($value!='')
                        {
                            $attribute = new Question_attributes;
                            $attribute->qid = $qid;
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

                    $result = Question_attributes::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$qid));
                    if (count($result)>0)
                    {
                        if($value!=$validAttribute['default'] && trim($value)!="")
                        {
                            Question_attributes::model()->updateAll(array('value'=>$value),'attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid));
                        }
                        else
                        {
                            Question_attributes::model()->deleteAll('attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$qid));
                        }
                    }
                    elseif($value!=$validAttribute['default'] && trim($value)!="")
                    {
                        $attribute = new Question_attributes;
                        $attribute->qid = $qid;
                        $attribute->value = $value;
                        $attribute->attribute = $validAttribute['name'];
                        $attribute->save();
                    }
                }
            }


            $qtypes=getQuestionTypeList('','array');
            // These are the questions types that have no answers and therefore we delete the answer in that case
            $iAnswerScales = $qtypes[Yii::app()->request->getPost('type')]['answerscales'];
            $iSubquestionScales = $qtypes[Yii::app()->request->getPost('type')]['subquestions'];

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
                // TMSW Conditions->Relevance:  Do similar check via EM, but do allow such a change since will be easier to modify relevance
                //Make sure there are no conditions based on this question, since we are changing the type
                $ccresult = Conditions::model()->findAllByAttributes(array('cqid'=>$qid));
                $cccount=count($ccresult);
                foreach ($ccresult as $ccr) {$qidarray[]=$ccr['qid'];}
                if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
            }
            if (isset($cccount) && $cccount)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions before you can change the type of this question.","js")." ($qidlist)\")\n //-->\n</script>\n";
            }
            else
            {
                if (isset($gid) && $gid != "")
                {

                    //                    $array_result=checkMoveQuestionConstraintsForConditions(sanitize_int($surveyid),sanitize_int($qid), sanitize_int($gid));
                    //                    // If there is no blocking conditions that could prevent this move
                    //
                    //                    if (is_null($array_result['notAbove']) && is_null($array_result['notBelow']))
                    //                    {
                    $questlangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
                    $baselang = Survey::model()->findByPk($surveyid)->language;
                    array_push($questlangs,$baselang);
                    if ($xssfilter)
                        $_POST['title'] = $filter->purify($_POST['title']);
                    else
                        $_POST['title'] = html_entity_decode(Yii::app()->request->getPost('title'), ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $_POST['title']=fixCKeditorText(Yii::app()->request->getPost('title'));
                    foreach ($questlangs as $qlang)
                    {
                        if ($xssfilter)
                        {
                            $_POST['question_'.$qlang] = $filter->purify($_POST['question_'.$qlang]);
                            $_POST['help_'.$qlang] = $filter->purify($_POST['help_'.$qlang]);
                        }
                        else
                        {
                            $_POST['question_'.$qlang] = html_entity_decode(Yii::app()->request->getPost('question_'.$qlang), ENT_QUOTES, "UTF-8");
                            $_POST['help_'.$qlang] = html_entity_decode(Yii::app()->request->getPost('help_'.$qlang), ENT_QUOTES, "UTF-8");
                        }

                        // Fix bug with FCKEditor saving strange BR types
                        $_POST['question_'.$qlang]=fixCKeditorText(Yii::app()->request->getPost('question_'.$qlang));
                        $_POST['help_'.$qlang]=fixCKeditorText(Yii::app()->request->getPost('help_'.$qlang));

                        if (isset($qlang) && $qlang != "")
                        { // ToDo: Sanitize the POST variables !

                            $udata = array(
                            'type' => Yii::app()->request->getPost('type'),
                            'title' => Yii::app()->request->getPost('title'),
                            'question' => Yii::app()->request->getPost('question_'.$qlang),
                            'preg' => Yii::app()->request->getPost('preg'),
                            'help' => Yii::app()->request->getPost('help_'.$qlang),
                            'gid' => $gid,
                            'other' => Yii::app()->request->getPost('other'),
                            'mandatory' => Yii::app()->request->getPost('mandatory'),
                            'relevance' => Yii::app()->request->getPost('relevance'),
                            );

                            if ($oldgid!=$gid)
                            {

                                if ( getGroupOrder($surveyid,$oldgid) > getGroupOrder($surveyid,$gid) )
                                {
                                    // TMSW Conditions->Relevance:  What is needed here?

                                    // Moving question to a 'upper' group
                                    // insert question at the end of the destination group
                                    // this prevent breaking conditions if the target qid is in the dest group
                                    $insertorder = getMaxQuestionOrder($gid,$surveyid) + 1;
                                    $udata = array_merge($udata,array('question_order' => $insertorder));
                                }
                                else
                                {
                                    // Moving question to a 'lower' group
                                    // insert question at the beginning of the destination group
                                    shiftOrderQuestions($surveyid,$gid,1); // makes 1 spare room for new question at top of dest group
                                    $udata = array_merge($udata,array('question_order' => 0));
                                }
                            }
                            $condn = array('sid' => $surveyid, 'qid' => $qid, 'language' => $qlang);
                            $question = Questions::model()->findByAttributes($condn);
                            foreach ($udata as $k => $v)
                                $question->$k = $v;

                            $uqresult = $question->save();//($uqquery); // or safeDie ("Error Update Question: ".$uqquery."<br />");  // Checked)
                            if (!$uqresult)
                            {
                                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\n\")\n //-->\n</script>\n";
                            }
                        }
                    }


                    // Update the group ID on subquestions, too
                    if ($oldgid!=$gid)
                    {
                        Questions::model()->updateAll(array('gid'=>$gid), 'qid=:qid and parent_qid>0', array(':qid'=>$qid));
                        // if the group has changed then fix the sortorder of old and new group
                        Questions::model()->updateQuestionOrder($oldgid, $surveyid);
                        Questions::model()->updateQuestionOrder($gid, $surveyid);
                        // If some questions have conditions set on this question's answers
                        // then change the cfieldname accordingly
                        fixMovedQuestionConditions($qid, $oldgid, $gid);
                    }
                    if ($oldtype != Yii::app()->request->getPost('type'))
                    {
                        Questions::model()->updateAll(array('type'=>Yii::app()->request->getPost('type')), 'parent_qid=:qid', array(':qid'=>$qid));
                    }

                    Answers::model()->deleteAllByAttributes(array('qid' => $qid), 'scale_id >= :scale_id', array(':scale_id' => $iAnswerScales));

                    // Remove old subquestion scales
                    Questions::model()->deleteAllByAttributes(array('parent_qid' => $qid), 'scale_id >= :scale_id', array(':scale_id' => $iSubquestionScales));

                    Yii::app()->session['flashmessage'] = $clang->gT("Question was successfully saved.");
                    //                    }
                    //                    else
                    //                    {
                    //
                    //                        // There are conditions constraints: alert the user
                    //                        $errormsg="";
                    //                        if (!is_null($array_result['notAbove']))
                    //                        {
                    //                            $errormsg.=$clang->gT("This question relies on other question's answers and can't be moved above groupId:","js")
                    //                            . " " . $array_result['notAbove'][0][0] . " " . $clang->gT("in position","js")." ".$array_result['notAbove'][0][1]."\\n"
                    //                            . $clang->gT("See conditions:")."\\n";
                    //
                    //                            foreach ($array_result['notAbove'] as $notAboveCond)
                    //                            {
                    //                                $errormsg.="- cid:". $notAboveCond[3]."\\n";
                    //                            }
                    //
                    //                        }
                    //                        if (!is_null($array_result['notBelow']))
                    //                        {
                    //                            $errormsg.=$clang->gT("Some questions rely on this question's answers. You can't move this question below groupId:","js")
                    //                            . " " . $array_result['notBelow'][0][0] . " " . $clang->gT("in position","js")." ".$array_result['notBelow'][0][1]."\\n"
                    //                            . $clang->gT("See conditions:")."\\n";
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
                    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\")\n //-->\n</script>\n";
                }
            }
            LimeExpressionManager::UpgradeConditionsToRelevance($surveyid);

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                if(Yii::app()->request->getPost('newpage') == "return") {
                    $this->getController()->redirect($this->getController()->createUrl('admin/question/sa/editquestion/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
                } else {
                    $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
                }
            }
        }


        if (($action == "updatesurveylocalesettings") && hasSurveyPermission($surveyid,'surveylocale','update'))
        {
            $languagelist = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $languagelist[]=Survey::model()->findByPk($surveyid)->language;

            Yii::app()->loadHelper('database');

            foreach ($languagelist as $langname)
            {
                if ($langname)
                {
                    $url = Yii::app()->request->getPost('url_'.$langname);
                    if ($url == 'http://') {$url="";}

                    // Clean XSS attacks
                    if ($xssfilter)
                    {
                        $purifier = new CHtmlPurifier();
                        $purifier->options = array(
                        'HTML.Allowed' => 'p,a[href],b,i'
                        );
                        $short_title=$purifier->purify(Yii::app()->request->getPost('short_title_'.$langname));
                        $description=$purifier->purify(Yii::app()->request->getPost('description_'.$langname));
                        $welcome=$purifier->purify(Yii::app()->request->getPost('welcome_'.$langname));
                        $endtext=$purifier->purify(Yii::app()->request->getPost('endtext_'.$langname));
                        $sURLDescription=$purifier->purify(Yii::app()->request->getPost('urldescrip_'.$langname));
                        $sURL=$purifier->purify(Yii::app()->request->getPost('url_'.$langname));
                    }
                    else
                    {
                        $short_title = html_entity_decode(Yii::app()->request->getPost('short_title_'.$langname), ENT_QUOTES, "UTF-8");
                        $description = html_entity_decode(Yii::app()->request->getPost('description_'.$langname), ENT_QUOTES, "UTF-8");
                        $welcome = html_entity_decode(Yii::app()->request->getPost('welcome_'.$langname), ENT_QUOTES, "UTF-8");
                        $endtext = html_entity_decode(Yii::app()->request->getPost('endtext_'.$langname), ENT_QUOTES, "UTF-8");
                        $sURLDescription = html_entity_decode(Yii::app()->request->getPost('urldescrip_'.$langname), ENT_QUOTES, "UTF-8");
                        $sURL = html_entity_decode(Yii::app()->request->getPost('url_'.$langname), ENT_QUOTES, "UTF-8");
                    }

                    // Fix bug with FCKEditor saving strange BR types
                    $short_title = Yii::app()->request->getPost('short_title_'.$langname);
                    $description = Yii::app()->request->getPost('description_'.$langname);
                    $welcome = Yii::app()->request->getPost('welcome_'.$langname);
                    $endtext = Yii::app()->request->getPost('endtext_'.$langname);

                    $short_title=fixCKeditorText($short_title);
                    $description=fixCKeditorText($description);
                    $welcome=fixCKeditorText($welcome);
                    $endtext=fixCKeditorText($endtext);

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
                    $Surveys_languagesettings=Surveys_languagesettings::model()->findByPk(array('surveyls_survey_id'=>$postsid, 'surveyls_language'=>$langname));
                    $Surveys_languagesettings->attributes=$data;
                    $Surveys_languagesettings->save(); // save the change to database

                }
            }
            Yii::app()->session['flashmessage'] = $clang->gT("Survey text elements successfully saved.");

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid));
            }
        }

        if (($action == "updatesurveysettingsandeditlocalesettings" || $action == "updatesurveysettings") && hasSurveyPermission($surveyid,'surveysettings','update'))
        {
            Yii::app()->loadHelper('surveytranslator');
            Yii::app()->loadHelper('database');
            $formatdata=getDateFormatData(Yii::app()->session['dateformat']);

            $expires = $_POST['expires'];
            if (trim($expires)=="")
            {
                $expires=null;
            }
            else
            {
                Yii::app()->loadLibrary('Date_Time_Converter');
                $datetimeobj = new date_time_converter($expires, $formatdata['phpdate'].' H:i'); //new Date_Time_Converter($expires, $formatdata['phpdate'].' H:i');
                $expires=$datetimeobj->convert("Y-m-d H:i:s");
            }
            $startdate = $_POST['startdate'];
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

            //make sure only numbers are passed within the $_POST variable
            $tokenlength = (int) $_POST['tokenlength'];

            //token length has to be at least 5, otherwise set it to default (15)
            if($tokenlength < 5)
            {
                $tokenlength = 15;
            }

            cleanLanguagesFromSurvey($surveyid,Yii::app()->request->getPost('languageids'));

            fixLanguageConsistency($surveyid,Yii::app()->request->getPost('languageids'));
            $template = Yii::app()->request->getPost('template');

            if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1 && Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] != 1 && !hasTemplateManageRights(Yii::app()->session['loginID'], $template)) $template = "default";

            $aURLParams=json_decode(Yii::app()->request->getPost('allurlparams'),true);
            Survey_url_parameters::model()->deleteAllByAttributes(array('sid'=>$surveyid));

            foreach($aURLParams as $aURLParam)
            {
                $aURLParam['parameter']=trim($aURLParam['parameter']);
                if ($aURLParam['parameter']=='' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/',$aURLParam['parameter']) || $aURLParam['parameter']=='sid' || $aURLParam['parameter']=='newtest' || $aURLParam['parameter']=='token' || $aURLParam['parameter']=='lang')
                {
                    continue;  // this parameter name seems to be invalid - just ignore it
                }
                unset($aURLParam['act']);
                unset($aURLParam['title']);
                unset($aURLParam['id']);
                if ($aURLParam['targetqid']=='') $aURLParam['targetqid']=NULL;
                if ($aURLParam['targetsqid']=='') $aURLParam['targetsqid']=NULL;
                $aURLParam['sid']=$surveyid;

                $param = new Survey_url_parameters;
                foreach ($aURLParam as $k => $v)
                    $param->$k = $v;
                $param->save();
            }
            $updatearray= array('admin'=> Yii::app()->request->getPost('admin'),
            'expires'=>$expires,
            'adminemail'=> Yii::app()->request->getPost('adminemail'),
            'startdate'=>$startdate,
            'bounce_email'=> Yii::app()->request->getPost('bounce_email'),
            'anonymized'=> Yii::app()->request->getPost('anonymized'),
            'faxto'=> Yii::app()->request->getPost('faxto'),
            'format'=> Yii::app()->request->getPost('format'),
            'savetimings'=> Yii::app()->request->getPost('savetimings'),
            'template'=>$template,
            'assessments'=> Yii::app()->request->getPost('assessments'),
            'language'=> Yii::app()->request->getPost('language'),
            'additional_languages'=> Yii::app()->request->getPost('languageids'),
            'datestamp'=> Yii::app()->request->getPost('datestamp'),
            'ipaddr'=> Yii::app()->request->getPost('ipaddr'),
            'refurl'=> Yii::app()->request->getPost('refurl'),
            'publicgraphs'=> Yii::app()->request->getPost('publicgraphs'),
            'usecookie'=> Yii::app()->request->getPost('usecookie'),
            'allowregister'=> Yii::app()->request->getPost('allowregister'),
            'allowsave'=> Yii::app()->request->getPost('allowsave'),
            'navigationdelay'=> Yii::app()->request->getPost('navigationdelay'),
            'printanswers'=> Yii::app()->request->getPost('printanswers'),
            'publicstatistics'=> Yii::app()->request->getPost('publicstatistics'),
            'autoredirect'=> Yii::app()->request->getPost('autoredirect'),
            'showxquestions'=> Yii::app()->request->getPost('showxquestions'),
            'showgroupinfo'=> Yii::app()->request->getPost('showgroupinfo'),
            'showqnumcode'=> Yii::app()->request->getPost('showqnumcode'),
            'shownoanswer'=> Yii::app()->request->getPost('shownoanswer'),
            'showwelcome'=> Yii::app()->request->getPost('showwelcome'),
            'allowprev'=> Yii::app()->request->getPost('allowprev'),
            'allowjumps'=> Yii::app()->request->getPost('allowjumps'),
            'nokeyboard'=> Yii::app()->request->getPost('nokeyboard'),
            'showprogress'=> Yii::app()->request->getPost('showprogress'),
            'listpublic'=> Yii::app()->request->getPost('public'),
            'htmlemail'=> Yii::app()->request->getPost('htmlemail'),
            'sendconfirmation'=>  Yii::app()->request->getPost('sendconfirmation'),
            'tokenanswerspersistence'=> Yii::app()->request->getPost('tokenanswerspersistence'),
            'alloweditaftercompletion'=> Yii::app()->request->getPost('alloweditaftercompletion'),
            'usecaptcha'=> Yii::app()->request->getPost('usecaptcha'),
            'emailresponseto'=>trim(Yii::app()->request->getPost('emailresponseto')),
            'emailnotificationto'=>trim(Yii::app()->request->getPost('emailnotificationto')),
            'googleanalyticsapikey'=>trim(Yii::app()->request->getPost('googleanalyticsapikey')),
            'googleanalyticsstyle'=>trim(Yii::app()->request->getPost('googleanalyticsstyle')),
            'tokenlength'=>$tokenlength
            );
            // use model
            $Survey=Survey::model()->findByPk($surveyid);
            foreach ($updatearray as $k => $v)
                $Survey->$k = $v;
            $Survey->save();
#            Survey::model()->updateByPk($surveyid, $updatearray);
            $sqlstring = "surveyls_survey_id=:sid AND surveyls_language <> :base ";
            $params = array(':sid'=>$surveyid, ':base'=>Survey::model()->findByPk($surveyid)->language);

            $i=100000;
            foreach (Survey::model()->findByPk($surveyid)->additionalLanguages as $langname)
            {
                if ($langname)
                {
                    $sqlstring .= "AND surveyls_language <> :{$i} ";
                    $params[':'.$i]=$langname;
                }
                $i++;
            }

            Surveys_languagesettings::model()->deleteAll($sqlstring, $params);
            $usresult=true;

            foreach (Survey::model()->findByPk($surveyid)->additionalLanguages as $langname)
            {
                if ($langname)
                {
                    $oLanguageSettings = Surveys_languagesettings::model()->find('surveyls_survey_id=:surveyid AND surveyls_language=:langname', array(':surveyid'=>$surveyid,':langname'=>$langname));
                    if(!$oLanguageSettings)
                    {
                        $oLanguageSettings= new Surveys_languagesettings;
                        $languagedetails=getLanguageDetails($langname);
                        $insertdata = array(
                            'surveyls_survey_id' => $surveyid,
                            'surveyls_language' => $langname,
                            'surveyls_title' => '',
                            'surveyls_dateformat' => $languagedetails['dateformat']
                        );
                        foreach ($insertdata as $k => $v)
                            $oLanguageSettings->$k = $v;
                        $usresult=$oLanguageSettings->save();
                    }
                }
            }

            if ($usresult)
            {
                Yii::app()->session['flashmessage'] = $clang->gT("Survey settings were successfully saved.");
            }
            else
            {
                Yii::app()->session['flashmessage'] = $clang->gT("Error:").'<br>'.$clang->gT("Survey could not be updated.");
            }

            if (Yii::app()->request->getPost('action') == "updatesurveysettingsandeditlocalesettings")
            {
                $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/editlocalsettings/surveyid/'.$surveyid));
            }
            else
            {
                $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid));
            }

        }

        if (!$action)
        {
            $this->getController()->redirect("/admin","refresh");
        }


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
            Defaultvalues::model()->deleteByPk(array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language));
        }
        else
        {
            $arDefaultValue = Defaultvalues::model()->findByPk(array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language));

            if (is_null($arDefaultValue))
            {
                $data=array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language, 'defaultvalue'=>$defaultvalue);
                Defaultvalues::model()->insertRecords($data);
            }
            else
            {
                Defaultvalues::model()->updateByPk(array('sqid'=>$sqid, 'qid'=>$qid, 'specialtype'=>$specialtype, 'scale_id'=>$scale_id, 'language'=>$language), array('defaultvalue'=>$defaultvalue));
            }
        }
    }
}
