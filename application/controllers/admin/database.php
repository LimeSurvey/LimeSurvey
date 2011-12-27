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
* $Id: database.php 11349 2011-11-09 21:49:00Z tpartner $
*
*/
/**
* Database
*
* @package LimeSurvey
* @author
* @copyright 2011
* @version $Id: database.php 11349 2011-11-09 21:49:00Z tpartner $
* @access public
*/
class database extends Survey_Common_Action
{
	private $yii;
	private $controller;
	
	public function run($sa = null)
	{
	
		$this->yii = Yii::app();
		$this->controller = $this->getController();
		
		$this->route('index', array('sa'));
	}

    /**
    * Database::index()
    *
    * @param mixed $action
    * @return
    */
    function index($action=null)
    {

      	if(!empty($_POST['action'])) $action=$_POST['action'];
     	$clang = $this->controller->lang;
        $postsid=returnglobal('sid');
        $postgid=returnglobal('gid');
        $postqid=returnglobal('qid');
        $postqaid=returnglobal('qaid');
        $databaseoutput = '';
        $surveyid = returnglobal('sid');
        $gid = returnglobal('gid');
        $qid = returnglobal('qid');
        // if $action is not passed, check post data.

        if ($action == "updatedefaultvalues" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {

            $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_unshift($questlangs,$baselang);

            // same_default value on/off for question
            $uqquery = "UPDATE {{questions}}";
            if (isset($_POST['samedefault']))
            {
                $uqquery .= " SET same_default = '1' ";
            }
            else
            {
                $uqquery .= " SET same_default = '0' ";
            }
            $uqquery .= "WHERE sid='".$surveyid."' AND qid='".$qid."'";
            $uqresult = Yii::app()->db->createCommand($uqquery)->query() or show_error("Error Update Question: ".$uqquery."<br />");
            if (!$uqresult)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\n\")\n //-->\n</script>\n";
            }
            $query = "SELECT type FROM {{questions}} WHERE qid=$qid";
            $res = Yii::app()->db->createCommand($query)->query;
            $resrow = $res->read();
            $questiontype = $resrow['type'];

            $qtproperties=getqtypelist('','array');
            if ($qtproperties[$questiontype]['answerscales']>0 && $qtproperties[$questiontype]['subquestions']==0)
            {
                for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['answerscales'];$scale_id++)
                {
                    foreach ($questlangs as $language)
                    {
                        if (isset($_POST['defaultanswerscale_'.$scale_id.'_'.$language]))
                        {
                        $this->_Updatedefaultvalues($qid,0,$scale_id,'',$language,$_POST['defaultanswerscale_'.$scale_id.'_'.$language],true);
						}
                        if (isset($_POST['other_'.$scale_id.'_'.$language]))
                        {                            
                        $this->_Updatedefaultvalues($qid,0,$scale_id,'other',$language,$_POST['other_'.$scale_id.'_'.$language],true);
						}
                    }
                }
            }
            if ($qtproperties[$questiontype]['subquestions']>0)
            {

                foreach ($questlangs as $language)
                {
                    $sqquery = "SELECT * FROM {{questions}} WHERE sid=$surveyid AND gid=$gid AND parent_qid=$qid and language='".$language."' and scale_id=0 order by question_order";                
                    $sqresult = Yii::app()->db->createCommand($sqquery)->query();

                    for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['subquestions'];$scale_id++)
                    {
                        
			foreach ($sqresult->readAll() as $aSubquestionrow)
                        {
                            if (isset($_POST['defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid']]))
                            {
                                $this->_Updatedefaultvalues($qid,$aSubquestionrow['qid'],$scale_id,'',$language,$_POST['defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid']],true);
                            }
                            /*                       if (isset($_POST['other_'.$scale_id.'_'.$language]))
                            {
                            Updatedefaultvalues($postqid,$qid,$scale_id,'other',$language,$_POST['other_'.$scale_id.'_'.$language],true);
                            } */

                        }
                    }
                }
            }
            $this->session->set_userdata('flashmessage', $clang->gT("Default value settings were successfully saved."));

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                 $this->controller->redirect($this->getController()->createUrl('/admin/survey/view/'.$surveyid.'/'.$gid.'/'.$qid));  
            }
        }

        $databaseoutput['clang']=$clang;
        
        if ($action == "updateansweroptions" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {
           
	    Yii::app()->loadHelper('database');
            $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);

            $alllanguages = $anslangs;
            array_unshift($alllanguages,$baselang);

            $query = "select type from {{questions}} where qid=$qid";
            $res= Yii::app()->db->createCommand($query)->query();
            $resrow = $res->read();
            $questiontype = $resrow['type']; //$connect->GetOne($query);    // Checked)
            $qtypes=getqtypelist('','array');
            $scalecount=$qtypes[$questiontype]['answerscales'];

            $count=0;
            $invalidCode = 0;
            $duplicateCode = 0;

            //require_once("../classes/inputfilter/class.inputfilter_clean.php");
            //$myFilter = new InputFilter('','',1,1,1);

            //First delete all answers
            $query = "delete from {{answers}} where qid=".$qid;
            $result = Yii::app()->db->createCommand($query)->query(); // Checked

            for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
            {
                $maxcount=(int) $_POST['answercount_'.$scale_id];

                for ($sortorderid=1;$sortorderid<$maxcount;$sortorderid++)
                {
                    $code=sanitize_paranoid_string($_POST['code_'.$sortorderid.'_'.$scale_id]);
                    if (isset($_POST['oldcode_'.$sortorderid.'_'.$scale_id])) {
                        $oldcode=sanitize_paranoid_string($_POST['oldcode_'.$sortorderid.'_'.$scale_id]);
                        if($code !== $oldcode) {
                            $query='UPDATE {{conditions}} SET value='.db_quoteall($code).' WHERE cqid='.db_quoteall($qid).' AND value='.db_quoteall($oldcode);//escape string is correct?
                            Yii::app()->db->createCommand($query)->query();
                        }
                    }

                    $assessmentvalue=(int) $_POST['assessment_'.$sortorderid.'_'.$scale_id];
                    foreach ($alllanguages as $language)
                    {*/
                        $answer=$_POST['answer_'.$language.'_'.$sortorderid.'_'.$scale_id];

                        if (Yii::app()->getConfig('filterxsshtml'))
                        {
                            //Sanitize input, strip XSS
                            $answer=$this->security->xss_clean($answer);
                        }
                        else
                        {
                            $answer=html_entity_decode($answer, ENT_QUOTES, "UTF-8");
                        }
                        // Fix bug with FCKEditor saving strange BR types
                        $answer=fix_FCKeditor_text($answer);

                        // Now we insert the answers
                        $query = "INSERT INTO {{answers}} (code,answer,qid,sortorder,language,assessment_value, scale_id)
                        VALUES ('".$code."', '".
                        $answer."', ".
                        $qid.", ".
                        $sortorderid.", '".
                        $language."', ".
                        $assessmentvalue.",
                        $scale_id)";
                        if (!$result = Yii::app()->db->createCommand($query)->query()) // Checked
                        {
                            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update answers","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                        }
                    } // foreach ($alllanguages as $language)

                    if($code !== $oldcode) {
                        $query='UPDATE {{conditions}} SET value=\''.$code.'\' WHERE cqid='.$qid.' AND value=\''.$oldcode.'\'';
                        Yii::app()->db->createCommand($query)->query();
                    }

                }  // for ($sortorderid=0;$sortorderid<$maxcount;$sortorderid++)
            }  //  for ($scale_id=0;

            if ($invalidCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Answers with a code of 0 (zero) or blank code are not allowed, and will not be saved","js")."\")\n //-->\n</script>\n";
            if ($duplicateCode == 1) $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Duplicate codes found, these entries won't be updated","js")."\")\n //-->\n</script>\n";

            $sortorderid--;
            $this->session->set_userdata('flashmessage', $clang->gT("Answer options were successfully saved."));

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                 $this->controller->redirect($this->getController()->createUrl('/admin/question/answeroptions/'.$surveyid.'/'.$gid.'/'.$qid));
            }

            //$action='editansweroptions';

        }


        if ($action == "updatesubquestions" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {

            Yii::app()->loadHelper('database');
            $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_unshift($anslangs,$baselang);

            $query = "select type from {{questions}} where qid=$qid";
            $res=Yii::app()->db->createCommand($query)->query();
            $row = $res->read();
            $questiontype = $row['type']; //$connect->GetOne($query);    // Checked
            $qtypes=getqtypelist('','array');
            $scalecount=$qtypes[$questiontype]['subquestions'];

            $clang = $this->getController()->lang;
            // First delete any deleted ids
            $deletedqids=explode(' ', trim($_POST['deletedqids']));

            foreach ($deletedqids as $deletedqid)
            {
                $deletedqid=(int)$deletedqid;
                if ($deletedqid>0)
                { // don't remove undefined
                    $query = "DELETE FROM {{questions}} WHERE qid='{$deletedqid}'";  // Checked
                    if (!$result = Yii::app()->db->createCommand($query)->query())
                    {
                        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to delete answer","js")." - ".$query." \")\n //-->\n</script>\n";
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


            $insertqids=array();
            for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
            {
                foreach ($anslangs as $language)
                {
                    $position=0;
                    foreach ($rows[$scale_id][$language] as $subquestionkey=>$subquestionvalue)
                    {
                        if (substr($subquestionkey,0,3)!='new')
                        {
                            $query='Update {{questions}} set question_order='.($position+1).', title=\''.$codes[$scale_id][$position].'\', question=\''.$subquestionvalue.'\', scale_id='.$scale_id.' where qid=\''.$subquestionkey.'\' AND language=\''.$language.'\'';
                            Yii::app()->db->createCommand($query)->query();

                            if(isset($oldcodes[$scale_id][$position]) && $codes[$scale_id][$position] !== $oldcodes[$scale_id][$position]) {
                                $query='UPDATE {{conditions}} SET cfieldname="+'.$surveyid.'X'.$gid.'X'.$qid.$codes[$scale_id][$position].'" WHERE cqid='.$qid.' AND cfieldname="+'.$surveyid.'X'.$gid.'X'.$qid.$oldcodes[$scale_id][$position].'"';
                                Yii::app()->db->createCommand($query)->query();
                                $query='UPDATE {{conditions}} SET value="'.$codes[$scale_id][$position].'" WHERE cqid='.$qid.' AND cfieldname="'.$surveyid.'X'.$gid.'X'.$qid.'" AND value="'.$oldcodes[$scale_id][$position].'"';
                                Yii::app()->db->createCommand($query)->query();
                            }

                        }
                        else
                        {
                            if (!isset($insertqid[$position]))
                            {
                                $query='INSERT into {{questions}} (sid, gid, question_order, title, question, parent_qid, language, scale_id) values ('.$surveyid.','.$gid.','.($position+1).',\''.$codes[$scale_id][$position].'\',\''.$subquestionvalue.'\','.$qid.',\''.($language).'\','.$scale_id.')';
                                Yii::app()->db->createCommand($query)->query();
                                $insertqid[$position]=$this->db->insert_id(); //$connect->Insert_Id(db_table_name_nq('questions'),"qid");
                            }
                            else
                            {
                                db_switchIDInsert('questions',true);
                                $query='INSERT into {{questions}} (qid, sid, gid, question_order, title, question, parent_qid, language, scale_id) values ('.$insertqid[$position].','.$surveyid.','.$gid.','.($position+1).',\''.$codes[$scale_id][$position].'\',\''.$subquestionvalue.'\','.$qid.',\''.$language.'\','.$scale_id.')';
                                Yii::app()->db->createCommand($query)->query();
                                db_switchIDInsert('questions',true);
                            }
                        }
                        $position++;
                    }

                }
            }
            //include("surveytable_functions.php");
            //surveyFixColumns($surveyid);
            $this->session->set_userdata('flashmessage', $clang->gT("Subquestions were successfully saved."));

            //$action='editsubquestions';

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                 $this->controller->redirect($this->getController()->createUrl('/admin/question/subquestions/'.$surveyid.'/'.$gid.'/'.$qid));
            }
        }

        if (in_array($action, array('insertquestion', 'copyquestion')) && bHasSurveyPermission($surveyid, 'surveycontent','create'))
        {
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            if (strlen($_POST['title']) < 1)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n "
                ."alert(\"".$clang->gT("The question could not be added. You must enter at least enter a question code.","js")."\")\n "
                ."//-->\n</script>\n";
            }
            else
            {
                if (!isset($_POST['lid']) || $_POST['lid'] == '') {$_POST['lid']="0";}
                if (!isset($_POST['lid1']) || $_POST['lid1'] == '') {$_POST['lid1']="0";}
                if (!empty($_POST['questionposition']))
                {
                    //Bug Fix: remove +1 ->  $question_order=(sanitize_int($_POST['questionposition'])+1);
                    $question_order=(sanitize_int($_POST['questionposition']));
                    //Need to renumber all questions on or after this
                    $cdquery = "UPDATE {{questions}} SET question_order=question_order+1 WHERE gid=".$gid." AND question_order >= ".$question_order;
                    
		    $cdresult=Yii::app()->db->createCommand($cdquery)->query();
		    } else {
                    $question_order=(getMaxquestionorder($gid,$surveyid));
                    $question_order++;
                }

                $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");
                $_POST['question_'.$baselang] = html_entity_decode($_POST['question_'.$baselang], ENT_QUOTES, "UTF-8");
                $_POST['help_'.$baselang] = html_entity_decode($_POST['help_'.$baselang], ENT_QUOTES, "UTF-8");

            	$purifier = new CHtmlPurifier();

                // Fix bug with FCKEditor saving strange BR types
            	if (Yii::app()->getConfig('filterxsshtml'))
            	{
            		$_POST['title']=$purifier->purify($_POST['title']);
            		$_POST['question_'.$baselang]=$purifier->purify($_POST['question_'.$baselang]);
            		$_POST['help_'.$baselang]=$purifier->purify($_POST['help_'.$baselang]);
            	}
        		else
        		{
        			$_POST['title']=fix_FCKeditor_text($_POST['title']);
        			$_POST['question_'.$baselang]=fix_FCKeditor_text($_POST['question_'.$baselang]);
        			$_POST['help_'.$baselang]=fix_FCKeditor_text($_POST['help_'.$baselang]);
        		}
                //$_POST  = array_map('db_quote', $_POST);

                $data = array();
                $data = array(
	                'sid' => $surveyid,
	                'gid' => $gid,
	                'type' => $_POST['type'],
	                'title' => $_POST['title'],
	                'question' => $_POST['question_'.$baselang],
	                'preg' => $_POST['preg'],
	                'help' => $_POST['help_'.$baselang],
	                'other' => $_POST['other'],
	                'mandatory' => $_POST['mandatory'],
	                'question_order' => $question_order,
	                'language' => $baselang
                );

                $question = new Questions;
            	foreach ($data as $k => $v)
            		$question->$k = $v;
            	$result = $question->save();

                /**
                $query = "INSERT INTO ".db_table_name('questions')." (sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                ." VALUES ('{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
                ." '{$_POST['question_'.$baselang]}', '{$_POST['preg']}', '{$_POST['help_'.$baselang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$baselang}')";
                */
                //$result = $connect->Execute($query);  // Checked
                // Get the last inserted questionid for other languages
                $qid=Yii::app()->db->getLastInsertID(); //$connect->Insert_ID(db_table_name_nq('questions'),"qid");

                // Add other languages
                if ($result)
                {
                    $addlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    foreach ($addlangs as $alang)
                    {
                        if ($alang != "")
                        {
                            $data = array(
	                            'qid' => $qid,
	                            'sid' => $surveyid,
	                            'gid' => $gid,
	                            'type' => $_POST['type'],
	                            'title' => $_POST['title'],
	                            'question' => $_POST['question_'.$alang],
	                            'preg' => $_POST['preg'],
	                            'help' => $_POST['help_'.$alang],
	                            'other' => $_POST['other'],
	                            'mandatory' => $_POST['mandatory'],
	                            'question_order' => $question_order,
	                            'language' => $alang
                            );
                            $ques = new Questions;
                        	foreach ($data as $k => $v)
                        		$ques->$k = $v;
                        	$result2 = $ques->save();

                            /**
                            $query = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                            ." VALUES ('$qid','{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
                            ." '{$_POST['question_'.$alang]}', '{$_POST['preg']}', '{$_POST['help_'.$alang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$alang}')";
                            $result2 = $connect->Execute($query);  // Checked */
                            if (!$result2)
                            {
                                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".sprintf($clang->gT("Question in language %s could not be created.","js"),$alang)."\\n\")\n //-->\n</script>\n";

                            }
                        }
                    }
                }


                if (!$result)
                {
                    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n\")\n //-->\n</script>\n";

                } else {
					if ($action == 'copyquestion') {
						if (returnglobal('copysubquestions') == "Y")
						{
							$aSQIDMappings = array();
							$r1 = Questions::getSubQuestions(returnglobal('oldqid'));

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
								$ir1 = Questions::insertRecords($qr1);
								if (!isset($qr1['qid']))
								{
									$aSQIDMappings[$oldqid] = Yii::app()->db->getLastInsertID('qid');
								}
							}
						}
						if (returnglobal('copyanswers') == "Y")
						{
							$r1 = Answers::getAnswers(returnglobal('oldqid'));
							while ($qr1 = $r1->read())
							{
								Answers::insertRecords(array(
									'qid' => $qid,
									'code' => $qr1['code'],
									'answer' => $qr1['answer'],
									'sortorder' => $qr1['sortorder'],
									'language' => $qr1['language'],
									'scale_id' => $qr1['scale_id']
								));
							}
						}
						if (returnglobal('copyattributes') == "Y")
						{
							$r1 = Question_attributes::getQuestionAttributes(returnglobal('oldqid'));
							while($qr1 = $r1->read())
							{
								Question_attributes::insertRecords(array(
									'qid' => $qid,
									'attribute' => $qr1['attribute'],
									'value' => $qr1['value']
								));
							}
						}
					} else {
						$qattributes = questionAttributes();
						$validAttributes = $qattributes[$_POST['type']];
						foreach ($validAttributes as $validAttribute)
						{
							if (isset($_POST[$validAttribute['name']]))
							{
								$data = array(
									'qid' => $qid,
									'value' => $_POST[$validAttribute['name']],
									'attribute' => $validAttribute['name']
								);

								Question_attributes::insertRecords($data);

							}
						}
					}

					fixsortorderQuestions($gid, $surveyid);
					Yii::app()->session['flashmessage'] =  $clang->gT("Question was successfully added.");

				}

            }

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->controller->redirect($this->controller->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
            }
        }

        if ($action == "updatequestion" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            Yii::app()->loadHelper('expressions/em_manager');
            $cqquery = "SELECT type, gid FROM {{questions}} WHERE qid={$qid}";
            $cqresult=Yii::app()->db->createCommand($cqquery)->query(); // or safe_die ("Couldn't get question type to check for change<br />".$cqquery."<br />".$connect->ErrorMsg()); // Checked
            $cqr=$cqresult->read();
            $oldtype=$cqr['type'];
            $oldgid=$cqr['gid'];

            // Remove invalid question attributes on saving
            $qattributes=questionAttributes();
            $attsql="delete from {{question_attributes}} where qid='{$qid}' and ";
            if (isset($qattributes[$_POST['type']])){
                $validAttributes=$qattributes[$_POST['type']];
                foreach ($validAttributes as  $validAttribute)
                {
                    //$attsql.='attribute<>'.db_quoteall($validAttribute['name'])." and ";
                    $attsql.='attribute<>\''.$validAttribute['name']."' and ";
                }
            }
            $attsql.='1=1';
            Yii::app()->db->createCommand($attsql)->execute(); // or safe_die ("Couldn't delete obsolete question attributes<br />".$attsql."<br />".$connect->ErrorMsg()); // Checked
            $aLanguages=array_merge(array(GetBaseLanguageFromSurveyID($surveyid)),GetAdditionalLanguagesFromSurveyID($surveyid));


            //now save all valid attributes
            $validAttributes=$qattributes[$_POST['type']];
            // if there are conditions, create a relevance equation, over-writing any default relevance value
            $cond2rel = LimeExpressionManager::ConvertConditionsToRelevance($surveyid,$qid);
            if (!is_null($cond2rel)) {
                $_POST['relevance'] = $cond2rel;
            }

            foreach ($validAttributes as $validAttribute)
            {
                if ($validAttribute['i18n'])
                {
                    foreach ($aLanguages as $sLanguage)
                    {
                        if (isset($_POST[$validAttribute['name'].'_'.$sLanguage]))
                        {
                            $value=sanatize_paranoid_string($_POST[$validAttribute['name'].'_'.$sLanguage]);
                            $query = "select qaid from {{question_attributes}}
                            WHERE attribute='".$validAttribute['name']."' AND qid={$qid} AND language='{$sLanguage}'";
                            $result = Yii::app()->db->createCommand($query)->query(); // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                            if ($result->getRowCount()>0)
                            {
                                $query = "UPDATE {{question_attributes}}
                                SET value=".$value." WHERE attribute='".$validAttribute['name']."' AND qid={$qid} AND language='{$sLanguage}'";
                                $result = Yii::app()->db->createCommand($query)->execute() ; // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                            }
                            else
                            {
                                $query = "INSERT into {{question_attributes}}
                                (qid, value, attribute, language) values ({$qid},{$value},'{$validAttribute['name']}','{$sLanguage}')";
                                $result = Yii::app()->db->createCommand($query)->execute(); // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                            }
                        }
                    }
                }
                else
                {
                    if (isset($_POST[$validAttribute['name']]))
                    {
                        $query = "select qaid from {{question_attributes}}
                        WHERE attribute='".$validAttribute['name']."' AND qid=".$qid;
                        $result = Yii::app()->db->createCommand($query)->query(); // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                        $value = sanitize_string_paranoid($_POST[$validAttribute['name']]);
                        if ($result->getRowCount()>0)
                        {
                            $query = "UPDATE {{question_attributes}}
                            SET value=".$value.",language=NULL WHERE attribute='".$validAttribute['name']."' AND qid=".$qid;
                            $result = Yii::app()->db->createCommand($query)->execute() ; // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                        }
                        else
                        {
                            $query = "INSERT into {{question_attributes}}
                            (qid, value, attribute) values ($qid,$value,'{$validAttribute['name']}')";
                            $result = Yii::app()->db->createCommand($query)->execute(); // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                        }
                    }
                }
            }


            $qtypes=getqtypelist('','array');
            // These are the questions types that have no answers and therefore we delete the answer in that case
            $iAnswerScales = $qtypes[$_POST['type']]['answerscales'];
            $iSubquestionScales = $qtypes[$_POST['type']]['subquestions'];

            // These are the questions types that have the other option therefore we set everything else to 'No Other'
            if (($_POST['type']!= "L") && ($_POST['type']!= "!") && ($_POST['type']!= "P") && ($_POST['type']!="M"))
            {
                $_POST['other']='N';
            }

            // These are the questions types that have no validation - so zap it accordingly

            if ($_POST['type']== "!" || $_POST['type']== "L" || $_POST['type']== "M" || $_POST['type']== "P" ||
            $_POST['type']== "F" || $_POST['type']== "H" || $_POST['type']== ":" || $_POST['type']== ";" ||
            $_POST['type']== "X" || $_POST['type']== "")
            {
                $_POST['preg']='';
            }

            // These are the questions types that have no mandatory property - so zap it accordingly
            if ($_POST['type']== "X" || $_POST['type']== "|")
            {
                $_POST['mandatory']='N';
            }


            if ($oldtype != $_POST['type'])
            {
                // TMSW Conditions->Relevance:  Do similar check via EM, but do allow such a change since will be easier to modify relevance
                //Make sure there are no conditions based on this question, since we are changing the type
                $ccquery = "SELECT * FROM {{conditions}} WHERE cqid={$qid}";
                $ccresult = Yii::app()->db->createCommand($ccquery)->query(); // or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg()); // Checked
                $cccount=count($ccresult);
                foreach ($ccresult->readAll() as $ccr) {$qidarray[]=$ccr['qid'];}
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

                    // TMSW Conditions->Relevance:  not needed?

                    $array_result=checkMovequestionConstraintsForConditions(sanitize_int($surveyid),sanitize_int($qid), sanitize_int($gid));
                    // If there is no blocking conditions that could prevent this move

                    if (is_null($array_result['notAbove']) && is_null($array_result['notBelow']))
                    {

                        $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                        $baselang = GetBaseLanguageFromSurveyID($surveyid);
                        array_push($questlangs,$baselang);
                    	$p = new CHtmlPurifier();
                    	if (Yii::app()->getConfig('filterxsshtml'))
	                    	$_POST['title'] = $p->purify($_POST['title']);
                    	else
							$_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");

                        // Fix bug with FCKEditor saving strange BR types
                        $_POST['title']=fix_FCKeditor_text($_POST['title']);
                        foreach ($questlangs as $qlang)
                        {
                        	if (Yii::app()->getConfig('filterxsshtml'))
                        	{
                        		$_POST['question_'.$qlang] = $p->purify($_POST['question_'.$qlang]);
                        		$_POST['help_'.$qlang] = $p->purify($_POST['help_'.$qlang]);
                        	}
                        	else
                        	{
                            	$_POST['question_'.$qlang] = html_entity_decode($_POST['question_'.$qlang], ENT_QUOTES, "UTF-8");
                            	$_POST['help_'.$qlang] = html_entity_decode($_POST['help_'.$qlang], ENT_QUOTES, "UTF-8");
                        	}

                            // Fix bug with FCKEditor saving strange BR types
                            $_POST['question_'.$qlang]=fix_FCKeditor_text($_POST['question_'.$qlang]);
                            $_POST['help_'.$qlang]=fix_FCKeditor_text($_POST['help_'.$qlang]);

                            if (isset($qlang) && $qlang != "")
                            { // ToDo: Sanitize the POST variables !

                                $udata = array(
	                                'type' => $_POST['type'],
	                                'title' => $_POST['title'],
	                                'question' => $_POST['question_'.$qlang],
	                                'preg' => $_POST['preg'],
	                                'help' => $_POST['help_'.$qlang],
	                                'gid' => $gid,
	                                'other' => $_POST['other'],
	                                'mandatory' => $_POST['mandatory'],
	                                'relevance' => $_POST['relevance'],
                                );

                                if ($oldgid!=$gid)
                                {

                                    if ( getGroupOrder($surveyid,$oldgid) > getGroupOrder($surveyid,$gid) )
                                    {
                                        // TMSW Conditions->Relevance:  What is needed here?

                                        // Moving question to a 'upper' group
                                        // insert question at the end of the destination group
                                        // this prevent breaking conditions if the target qid is in the dest group
                                        $insertorder = getMaxquestionorder($gid) + 1;
                                        $udata = array_merge($udata,array('question_order' => $insertorder));
                                    }
                                    else
                                    {
                                        // Moving question to a 'lower' group
                                        // insert question at the beginning of the destination group
                                        shiftorderQuestions($surveyid,$gid,1); // makes 1 spare room for new question at top of dest group
                                        $udata = array_merge($udata,array('question_order' => 0));
                                    }
                                }
                                $condn = array('sid' => $surveyid, 'qid' => $qid, 'language' => $qlang);
                            	$question = Questions::model()->findByAttributes($condn);
                            	foreach ($udata as $k => $v)
                            		$question->$k = $v;

                                $uqresult = $question->save();//($uqquery); // or safe_die ("Error Update Question: ".$uqquery."<br />".$connect->ErrorMsg());  // Checked)
                                if (!$uqresult)
                                {
                                    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\n\")\n //-->\n</script>\n";
                                }
                            }
                        }


                        // Update the group ID on subquestions, too
                        if ($oldgid!=$gid)
                        {
                            $sQuery="UPDATE {{questions}} set gid={$gid} where gid={$oldgid} and parent_qid>0";
                            $oResult = Yii::app()->db->createCommand($sQuery)->execute(); // or safe_die ("Error updating question group ID: ".$uqquery."<br />".$connect->ErrorMsg());  // Checked
                            // if the group has changed then fix the sortorder of old and new group
                            fixsortorderQuestions($oldgid, $surveyid);
                            fixsortorderQuestions($gid, $surveyid);
                            // If some questions have conditions set on this question's answers
                            // then change the cfieldname accordingly
                            fixmovedquestionConditions($qid, $oldgid, $gid);
                        }
                        if ($oldtype != $_POST['type'])
                        {
                            $sQuery="UPDATE {{questions}} set type=".sanitize_paranoid_string($_POST['type'])." where parent_qid={$qid}";
                            $oResult = Yii::app()->db->createCommand($sQuery)->execute(); // or safe_die ("Error updating question group ID: ".$uqquery."<br />".$connect->ErrorMsg());  // Checked

                        }
                        $query = "DELETE FROM {{answers}} WHERE qid= {$qid} and scale_id>={$iAnswerScales}";
                        $result = Yii::app()->db->createCommand($query)->execute(); // or safe_die("Error: ".$connect->ErrorMsg()); // Checked

                        // Remove old subquestion scales
                        $query = "DELETE FROM {{questions}} WHERE parent_qid={$qid} and scale_id>={$iSubquestionScales}";
                        $result = Yii::app()->db->createCommand($query)->execute() ; //or safe_die("Error: ".$connect->ErrorMsg()); // Checked

                    }
                    else
                    {
                        // TMSW Conditions->Relevance:  not needed since such a move is no longer an error?

                        // There are conditions constraints: alert the user
                        $errormsg="";
                        if (!is_null($array_result['notAbove']))
                        {
                            $errormsg.=$clang->gT("This question relies on other question's answers and can't be moved above groupId:","js")
                            . " " . $array_result['notAbove'][0][0] . " " . $clang->gT("in position","js")." ".$array_result['notAbove'][0][1]."\\n"
                            . $clang->gT("See conditions:")."\\n";

                            foreach ($array_result['notAbove'] as $notAboveCond)
                            {
                                $errormsg.="- cid:". $notAboveCond[3]."\\n";
                            }

                        }
                        if (!is_null($array_result['notBelow']))
                        {
                            $errormsg.=$clang->gT("Some questions rely on this question's answers. You can't move this question below groupId:","js")
                            . " " . $array_result['notBelow'][0][0] . " " . $clang->gT("in position","js")." ".$array_result['notBelow'][0][1]."\\n"
                            . $clang->gT("See conditions:")."\\n";

                            foreach ($array_result['notBelow'] as $notBelowCond)
                            {
                                $errormsg.="- cid:". $notBelowCond[3]."\\n";
                            }
                        }

                        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
                        $gid= $oldgid; // group move impossible ==> keep display on oldgid
                    }
                }
                else
                {
                    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\")\n //-->\n</script>\n";
                }
            }

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->controller->redirect($this->controller->createUrl('admin/survey/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid));
            }
        }


        if (($action == "updatesurveylocalesettings") && bHasSurveyPermission($surveyid,'surveylocale','update'))
        {
            $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
            $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
			
			Yii::app()->loadHelper('database');
			
            foreach ($languagelist as $langname)
            {
                if ($langname)
                {
                    $url = CHttpRequest::getPost('url_'.$langname);
                    if ($url == 'http://') {$url="";}

                    // Clean XSS attacks
                    if (Yii::app()->getConfig('filterxsshtml'))
                    {
                    	$purifier = new CHtmlPurifier();
						$purifier->options = array(
						    'HTML.Allowed' => 'p,a[href],b,i'
						);
                        $short_title=$purifier->purify(CHttpRequest::getPost('short_title_'.$langname));
                        $description=$purifier->purify(CHttpRequest::getPost('description_'.$langname));
                        $welcome=$purifier->purify(CHttpRequest::getPost('welcome_'.$langname));
                        $endtext=$purifier->purify(CHttpRequest::getPost('endtext_'.$langname));
                        $sURLDescription=$purifier->purify(CHttpRequest::getPost('urldescrip_'.$langname));
                        $sURL=$purifier->purify(CHttpRequest::getPost('url_'.$langname));
                    }
                    else
                    {
                        $short_title = html_entity_decode(CHttpRequest::getPost('short_title_'.$langname), ENT_QUOTES, "UTF-8");
                        $description = html_entity_decode(CHttpRequest::getPost('description_'.$langname), ENT_QUOTES, "UTF-8");
                        $welcome = html_entity_decode(CHttpRequest::getPost('welcome_'.$langname), ENT_QUOTES, "UTF-8");
                        $endtext = html_entity_decode(CHttpRequest::getPost('endtext_'.$langname), ENT_QUOTES, "UTF-8");
                        $sURLDescription = html_entity_decode(CHttpRequest::getPost('urldescrip_'.$langname), ENT_QUOTES, "UTF-8");
                        $sURL = html_entity_decode(CHttpRequest::getPost('url_'.$langname), ENT_QUOTES, "UTF-8");
					}

                    // Fix bug with FCKEditor saving strange BR types
                    $short_title = CHttpRequest::getPost('short_title_'.$langname);
                    $description = CHttpRequest::getPost('description_'.$langname);
                    $welcome = CHttpRequest::getPost('welcome_'.$langname);
                    $endtext = CHttpRequest::getPost('endtext_'.$langname);

                    $short_title=fix_FCKeditor_text($short_title);
                    $description=fix_FCKeditor_text($description);
                    $welcome=fix_FCKeditor_text($welcome);
                    $endtext=fix_FCKeditor_text($endtext);

                    $data = array(
	                    'surveyls_title' => $short_title,
	                    'surveyls_description' => $description,
	                    'surveyls_welcometext' => $welcome,
	                    'surveyls_endtext' => $endtext,
	                    'surveyls_url' => $sURL,
	                    'surveyls_urldescription' => $sURLDescription,
	                    'surveyls_dateformat' => CHttpRequest::getPost('dateformat_'.$langname),
	                    'surveyls_numberformat' => CHttpRequest::getPost('numberformat_'.$langname)
                    );
                    //In 'surveyls_survey_id' => $surveyid, it was initially $postsid. returnglobal not working properly!
                    $condition = array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $langname);

                    $usquery = "UPDATE {{surveys_languagesettings}} \n"
                    . "SET surveyls_title='".$short_title."', surveyls_description='".$description."',\n"
                    . "surveyls_welcometext='".$welcome."',\n"
                    . "surveyls_endtext='".$endtext."',\n"
                    . "surveyls_url='".$url."',\n"
                    . "surveyls_urldescription='".CHttpRequest::getPost('urldescrip_'.$langname)."',\n"
                    . "surveyls_dateformat='".CHttpRequest::getPost('dateformat_'.$langname)."',\n"
                    . "surveyls_numberformat='".CHttpRequest::getPost('numberformat_'.$langname)."'\n"
                    . "WHERE surveyls_survey_id=".$postsid." and surveyls_language='".$langname."'";
                    
                    $usresult = Yii::app()->db->createCommand($usquery)->query();// or safe_die("Error updating local settings");   // Checked
                }
            }
           	Yii::app()->session['flashmessage'] = $clang->gT("Survey text elements successfully saved.");
			
            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                $this->controller->redirect($this->getController()->createUrl('admin/survey/view/'.$surveyid));
            }
        }

        if (($action == "updatesurveysettingsandeditlocalesettings" || $action == "updatesurveysettings") && bHasSurveyPermission($surveyid,'surveysettings','update'))
        {
            $this->yii->loadHelper('surveytranslator');
            $this->yii->loadHelper('database');
            $formatdata=getDateFormatData($this->yii->session['dateformat']);

            $expires = $_POST['expires'];
            if (trim($expires)=="")
            {
                $expires=null;
            }
            else
            {
                $this->yii->loadLibrary('Date_Time_Converter');
                $datetimeobj = new date_time_converter(array($expires, $formatdata['phpdate'].' H:i')); //new Date_Time_Converter($expires, $formatdata['phpdate'].' H:i');
                $expires=$datetimeobj->convert("Y-m-d H:i:s");
            }
            $startdate = $_POST['startdate'];
            if (trim($startdate)=="")
            {
                $startdate=null;
            }
            else
            {
                $this->yii->loadLibrary('Date_Time_Converter');
                $datetimeobj = new date_time_converter(array($startdate,$formatdata['phpdate'].' H:i')); //new Date_Time_Converter($startdate,$formatdata['phpdate'].' H:i');
                $startdate=$datetimeobj->convert("Y-m-d H:i:s");
            }

            //make sure only numbers are passed within the $_POST variable
            $tokenlength = (int) $_POST['tokenlength'];
            //$_POST['tokenlength'] = (int) $_POST['tokenlength'];

            //token length has to be at least 5, otherwise set it to default (15)
            if($tokenlength < 5)
            {
                $tokenlength = 15;
            }


            CleanLanguagesFromSurvey($surveyid,$_POST['languageids']);

            FixLanguageConsistency($surveyid,$_POST['languageids']);
            $template = $_POST['template'];

            if($this->yii->session['USER_RIGHT_SUPERADMIN'] != 1 && $this->yii->session['USER_RIGHT_MANAGE_TEMPLATE'] != 1 && !hasTemplateManageRights($this->yii->session['loginID'], $template)) $template = "default";

           

            $aURLParams=json_decode($_POST['allurlparams'],true);
	    Yii::app()->db->createCommand("DELETE FROM `{{survey_url_parameters}}` WHERE `sid` = '$surveyid'")->query();

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
                if ($aURLParam['targetqid']=='') $aURLParam['targetqid']='NULL';
                if ($aURLParam['targetsqid']=='') $aURLParam['targetsqid']='NULL';
                $aURLParam['sid']=$surveyid;
				
				//db_execute_assoc("INSERT INTO `{{survey_url_parameters}}` (`sid`, `parameter`, `targetqid`, `targetsqid`) VALUES ('$aURLParam[sid]', '$aURLParam[parameter]', $aURLParam[targetqid], $aURLParam[targetsqid])");
				Yii::app()->db->createCommand()->insert('{{survey_url_parameters}}', $aURLParam);
            }
            $updatearray= array('admin'=> $_POST['admin'],
            'expires'=>$expires,
            'adminemail'=> $_POST['adminemail'],
            'startdate'=>$startdate,
            'bounce_email'=> $_POST['bounce_email'],
            'anonymized'=> $_POST['anonymized'],
            'faxto'=> $_POST['faxto'],
            'format'=> $_POST['format'],
            'savetimings'=> $_POST['savetimings'],
            'template'=>$template,
            'assessments'=> $_POST['assessments'],
            'language'=> $_POST['language'],
            'additional_languages'=> $_POST['languageids'],
            'datestamp'=> $_POST['datestamp'],
            'ipaddr'=> $_POST['ipaddr'],
            'refurl'=> $_POST['refurl'],
            'publicgraphs'=> $_POST['publicgraphs'],
            'usecookie'=> $_POST['usecookie'],
            'allowregister'=> $_POST['allowregister'],
            'allowsave'=> $_POST['allowsave'],
            'navigationdelay'=> $_POST['navigationdelay'],
            'printanswers'=> $_POST['printanswers'],
            'publicstatistics'=> $_POST['publicstatistics'],
            'autoredirect'=> $_POST['autoredirect'],
            'showXquestions'=> $_POST['showXquestions'],
            'showgroupinfo'=> $_POST['showgroupinfo'],
            'showqnumcode'=> $_POST['showqnumcode'],
            'shownoanswer'=> $_POST['shownoanswer'],
            'showwelcome'=> $_POST['showwelcome'],
            'allowprev'=> $_POST['allowprev'],
            'allowjumps'=> $_POST['allowjumps'],
            'nokeyboard'=> $_POST['nokeyboard'],
            'showprogress'=> $_POST['showprogress'],
            'listpublic'=> $_POST['public'],
            'htmlemail'=> $_POST['htmlemail'],
            'sendconfirmation'=> 'N',
            'tokenanswerspersistence'=> $_POST['tokenanswerspersistence'],
            'alloweditaftercompletion'=> $_POST['alloweditaftercompletion'],
            'usecaptcha'=> $_POST['usecaptcha'],
            'emailresponseto'=>trim($_POST['emailresponseto']),
            'emailnotificationto'=>trim($_POST['emailnotificationto']),
            'tokenlength'=>$tokenlength
            );


            /**$usquery=$connect->GetUpdateSQL($rs, $updatearray, false, get_magic_quotes_gpc());
            if ($usquery) {
            $usresult = $connect->Execute($usquery) or safe_die("Error updating<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());  // Checked
            }
            */
            $condition = 'sid = \''.$surveyid.'\'';
            Survey::model()->updateSurvey($updatearray,$condition);
            $sqlstring ='';

            foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
            {
                if ($langname)
                {
                    $sqlstring .= "AND surveyls_language <> '".$langname."' ";
                }
            }

            // Add base language too
            $sqlstring .= "AND surveyls_language <> '".GetBaseLanguageFromSurveyID($surveyid)."' ";
            $usquery = "DELETE FROM {{surveys_languagesettings}} WHERE surveyls_survey_id={$surveyid} ".$sqlstring;

            $usresult = Yii::app()->db->createCommand($usquery)->query() or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>"); // Checked

            foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
            {
                if ($langname)
                {
                    $usquery = "select * from {{surveys_languagesettings}} where surveyls_survey_id={$surveyid} and surveyls_language='".$langname."'";
                    $usresult = Yii::app()->db->createCommand($usquery)->query() or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>"); // Checked
                    if ($usresult->getRowCount()==0)
                    {
                        $bplang = $this->controller->lang;
                        $aDefaultTexts=aTemplateDefaultTexts($bplang,'unescaped');
                        if (getEmailFormat($surveyid) == "html")
                        {
                            $ishtml=true;
                            $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].$aDefaultTexts['admin_detailed_notification'];
                        }
                        else
                        {
                            $ishtml=false;
                        }
                        $languagedetails=getLanguageDetails($langname);

                        $insertdata = array(
                        'surveyls_survey_id' => $surveyid,
                        'surveyls_language' => $langname,
                        'surveyls_title' => '',
                        'surveyls_email_invite_subj' => $aDefaultTexts['invitation_subject'],
                        'surveyls_email_invite' => $aDefaultTexts['invitation'],
                        'surveyls_email_remind_subj' => $aDefaultTexts['reminder_subject'],
                        'surveyls_email_remind' => $aDefaultTexts['reminder'],
                        'surveyls_email_confirm_subj' => $aDefaultTexts['confirmation_subject'],
                        'surveyls_email_confirm' => $aDefaultTexts['confirmation'],
                        'surveyls_email_register_subj' => $aDefaultTexts['registration_subject'],
                        'surveyls_email_register' => $aDefaultTexts['registration'],
                        'email_admin_notification_subj' => $aDefaultTexts['admin_notification_subject'],
                        'email_admin_notification' => $aDefaultTexts['admin_notification'],
                        'email_admin_responses_subj' => $aDefaultTexts['admin_detailed_notification_subject'],
                        'email_admin_responses' => $aDefaultTexts['admin_detailed_notification'],
                        'surveyls_dateformat' => $languagedetails['dateformat']
                        );
						Yii::app()->db->createCommand()->insert('{{surveys_languagesettings}}', $insertdata);
						unset($bplang);
                    }
                }
            }



            if ($usresult)
            {
                $surveyselect = getsurveylist();
                $this->yii->session['flashmessage'] = $clang->gT("Survey settings were successfully saved.");

            }
            else
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Survey could not be updated","js")."\n\")\n //-->\n</script>\n";
            }

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                //redirect(site_url('admin/survey/view/'.$surveyid));

                if ($_POST['action'] == "updatesurveysettingsandeditlocalesettings")
                {
                    $this->controller->redirect($this->yii->homeUrl.('/admin/survey/sa/editlocalsettings/surveyid/'.$surveyid));
                }
                else
                {
                    $this->controller->redirect($this->yii->homeUrl.('/admin/survey/sa/view/surveyid/'.$surveyid));
                }

            }
        }

        if (!$action)
        {
			$this->controller->redirect("/admin","refresh");
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
    function _Updatedefaultvalues($qid,$sqid,$scale_id,$specialtype,$language,$defaultvalue,$ispost)
    {
        //$this->load->helper('database');
        if ($defaultvalue=='')  // Remove the default value if it is empty
        {
            $query = "DELETE FROM {{defaultvalues}} WHERE sqid=$sqid AND qid=$qid AND specialtype='$specialtype' AND scale_id={$scale_id} AND language='{$language}'";
            Yii::app()->db->createCommand($query)->query();
            
        }
        else
        {
            $query = "SELECT qid FROM {{defaultvalues}} WHERE sqid=$sqid AND qid=$qid AND specialtype=$specialtype'' AND scale_id={$scale_id} AND language='{$language}'";
            $res = Yii::app()->db->createCommand($query)->query();
            $exists=count($res); 
          
            if ($exists == 0)
            {
                $query = "INSERT INTO {{defaultvalues}} (defaultvalue,qid,scale_id,language,specialtype,sqid) VALUES ('".$defaultvalue."',{$qid},{$scale_id},'{$language}','{$specialtype}',{$sqid})";
                Yii::app()->db->createCommand($query)->query();
            }
            else
            {
                $query = "UPDATE {{defaultvalues}} set defaultvalue='".$defaultvalue."'  WHERE sqid={$sqid} AND qid={$qid} AND specialtype='{$specialtype}' AND scale_id={$scale_id} AND language='{$language}'";
                Yii::app()->db->createCommand($query)->query();
               
            }
        }
    }





}
