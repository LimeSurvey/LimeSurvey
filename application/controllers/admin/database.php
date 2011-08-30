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
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class Database extends Admin_Controller {


    /**
     * Database::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
		parent::__construct();
	}

    /**
     * Database::index()
     *
     * @param mixed $action
     * @return
     */
    function index($action=null)
    {

        //global $clang;
        $clang = $this->limesurvey_lang;
        $postsid=returnglobal('sid');
        $postgid=returnglobal('gid');
        $postqid=returnglobal('qid');
        $postqaid=returnglobal('qaid');
        $databaseoutput = '';
        $surveyid = $this->input->post("sid");
        $gid = $this->input->post("gid");
        $qid = $this->input->post("qid");
        // if $action is not passed, check post data.
        if (!$action)
        {
            $action = $this->input->post("action");
        }

        if ($action == "updatedefaultvalues" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {

            $this->load->helper('database');
            $_POST = $this->input->post();
            $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_unshift($questlangs,$baselang);

            // same_default value on/off for question
            $uqquery = "UPDATE ".$this->db->dbprefix."questions";
            if (isset($_POST['samedefault']))
            {
                $uqquery .= " SET same_default = '1' ";
            }
            else
            {
                $uqquery .= " SET same_default = '0' ";
            }
            $uqquery .= "WHERE sid='".$surveyid."' AND qid='".$qid."'";
            $uqresult = db_execute_assoc($uqquery) or show_error("Error Update Question: ".$uqquery."<br />");
            if (!$uqresult)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\n\")\n //-->\n</script>\n";
            }
            $query = "SELECT type FROM ".$this->db->dbprefix."questions WHERE qid=$qid";
            $res = db_execute_assoc($query);
            $resrow = $res->row_array();
            $questiontype = $resrow['type'];
            //$questiontype=$connect->GetOne("SELECT type FROM ".$this->db->dbprefix."questions WHERE qid=$qid");
            $qtproperties=getqtypelist('','array');
            if ($qtproperties[$questiontype]['answerscales']>0 && $qtproperties[$questiontype]['subquestions']==0)
            {
                for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['answerscales'];$scale_id++)
                {
                    foreach ($questlangs as $language)
                    {
                       if (isset($_POST['defaultanswerscale_'.$scale_id.'_'.$language]))
                       {
                           self::_Updatedefaultvalues($qid,0,$scale_id,'',$language,$_POST['defaultanswerscale_'.$scale_id.'_'.$language],true);
                       }
                       if (isset($_POST['other_'.$scale_id.'_'.$language]))
                       {
                           self::_Updatedefaultvalues($qid,0,$scale_id,'other',$language,$_POST['other_'.$scale_id.'_'.$language],true);
                       }
                    }
                }
            }
            if ($qtproperties[$questiontype]['subquestions']>0)
            {

                foreach ($questlangs as $language)
                {
                    $sqquery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND parent_qid=$qid and language='".$language."' and scale_id=0 order by question_order";
                    $sqresult = db_execute_assoc($sqquery);
                    //$sqrows = $sqresult->GetRows();

                    for ($scale_id=0;$scale_id<$qtproperties[$questiontype]['subquestions'];$scale_id++)
                    {
                       foreach ($sqresult->result_array() as $aSubquestionrow)
                       {
                           if (isset($_POST['defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid']]))
                           {
                               self::_Updatedefaultvalues($qid,$aSubquestionrow['qid'],$scale_id,'',$language,$_POST['defaultanswerscale_'.$scale_id.'_'.$language.'_'.$aSubquestionrow['qid']],true);
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
                redirect(site_url('admin/survey/view/'.$surveyid.'/'.$gid.'/'.$qid));
            }
        }


        if ($action == "updateansweroptions" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            $this->load->helper('database');
            $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);

            $alllanguages = $anslangs;
            array_unshift($alllanguages,$baselang);


            $query = "select type from ".$this->db->dbprefix."questions where qid=$qid";
            $res= db_execute_assoc($query);
            $resrow = $res->row_array();
            $questiontype = $resrow['type']; //$connect->GetOne($query);    // Checked)
            $qtypes=getqtypelist('','array');
            $scalecount=$qtypes[$questiontype]['answerscales'];

            $count=0;
            $invalidCode = 0;
            $duplicateCode = 0;

            //require_once("../classes/inputfilter/class.inputfilter_clean.php");
            //$myFilter = new InputFilter('','',1,1,1);
            $_POST = $this->input->post();
            //First delete all answers
            $query = "delete from ".$this->db->dbprefix."answers where qid=".$qid;
            $result = db_execute_assoc($query); // Checked

            for ($scale_id=0;$scale_id<$scalecount;$scale_id++)
            {
                $maxcount=(int) $_POST['answercount_'.$scale_id];

                for ($sortorderid=1;$sortorderid<$maxcount;$sortorderid++)
                {
                    $code=sanitize_paranoid_string($_POST['code_'.$sortorderid.'_'.$scale_id]);
                    if (isset($_POST['oldcode_'.$sortorderid.'_'.$scale_id])) {
                        $oldcode=sanitize_paranoid_string($_POST['oldcode_'.$sortorderid.'_'.$scale_id]);
                        if($code !== $oldcode) {
                            $query='UPDATE '.$this->db->dbprefix.'conditions SET value='.db_quoteall($code).' WHERE cqid='.$this->db->escape($qid).' AND value='.$this->db->escape_str($oldcode);
                            db_execute_assoc($query);
                        }
                    }

                    $assessmentvalue=(int) $_POST['assessment_'.$sortorderid.'_'.$scale_id];
                    foreach ($alllanguages as $language)
                    {
                        $answer=$_POST['answer_'.$language.'_'.$sortorderid.'_'.$scale_id];

                        /**if ($filterxsshtml)
                        {
                            //Sanitize input, strip XSS
                            $answer=$myFilter->process($answer);
                        }
                        else
                        { */
                            $answer=html_entity_decode($answer, ENT_QUOTES, "UTF-8");
                        //}
                        // Fix bug with FCKEditor saving strange BR types
                        $answer=fix_FCKeditor_text($answer);

                        // Now we insert the answers
                        $query = "INSERT INTO ".$this->db->dbprefix."answers (code,answer,qid,sortorder,language,assessment_value, scale_id)
                                  VALUES ('".$code."', '".
                        $answer."', ".
                        $qid.", ".
                        $sortorderid.", '".
                        $language."', ".
                        $assessmentvalue.",
                        $scale_id)";
                        if (!$result = db_execute_assoc($query)) // Checked
                        {
                            $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Failed to update answers","js")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                        }
                    } // foreach ($alllanguages as $language)

                    if($code !== $oldcode) {
                        $query='UPDATE '.$this->db->dbprefix.'conditions SET value=\''.$code.'\' WHERE cqid='.$qid.' AND value=\''.$oldcode.'\'';
                        db_execute_assoc($query);
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
                redirect(site_url('admin/question/answeroptions/'.$surveyid.'/'.$gid.'/'.$qid));
            }

            //$action='editansweroptions';

        }


        if ($action == "updatesubquestions" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            $this->load->helper('database');
            $anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_unshift($anslangs,$baselang);

            $query = "select type from ".$this->db->dbprefix."questions where qid=$qid";
            $res=db_execute_assoc($query);
            $row = $res->row_array();
            $questiontype = $row['type']; //$connect->GetOne($query);    // Checked
            $qtypes=getqtypelist('','array');
            $scalecount=$qtypes[$questiontype]['subquestions'];
            $_POST = $this->input->post();
            $clang = $this->limesurvey_lang;
            // First delete any deleted ids
            $deletedqids=explode(' ', trim($_POST['deletedqids']));

            foreach ($deletedqids as $deletedqid)
            {
                $deletedqid=(int)$deletedqid;
                if ($deletedqid>0)
                { // don't remove undefined
                $query = "DELETE FROM ".$this->db->dbprefix."questions WHERE qid='{$deletedqid}'";  // Checked
                if (!$result = db_execute_assoc($query))
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
                            $query='Update '.$this->db->dbprefix.'questions set question_order='.($position+1).', title=\''.$codes[$scale_id][$position].'\', question=\''.$subquestionvalue.'\', scale_id='.$scale_id.' where qid=\''.$subquestionkey.'\' AND language=\''.$language.'\'';
                            db_execute_assoc($query);

                            if(isset($oldcodes[$scale_id][$position]) && $codes[$scale_id][$position] !== $oldcodes[$scale_id][$position]) {
                                $query='UPDATE '.$this->db->dbprefix.'conditions SET cfieldname="+'.$surveyid.'X'.$gid.'X'.$qid.$codes[$scale_id][$position].'" WHERE cqid='.$qid.' AND cfieldname="+'.$surveyid.'X'.$gid.'X'.$qid.$oldcodes[$scale_id][$position].'"';
                                db_execute_assoc($query);
                                $query='UPDATE '.$this->db->dbprefix.'conditions SET value="'.$codes[$scale_id][$position].'" WHERE cqid='.$qid.' AND cfieldname="'.$surveyid.'X'.$gid.'X'.$qid.'" AND value="'.$oldcodes[$scale_id][$position].'"';
                                db_execute_assoc($query);
                            }

                        }
                        else
                        {
                            if (!isset($insertqid[$position]))
                            {
                                $query='INSERT into '.$this->db->dbprefix.'questions (sid, gid, question_order, title, question, parent_qid, language, scale_id) values ('.$surveyid.','.$gid.','.($position+1).',\''.$codes[$scale_id][$position].'\',\''.$subquestionvalue.'\','.$qid.',\''.($language).'\','.$scale_id.')';
                                db_execute_assoc($query);
                                $insertqid[$position]=$this->db->insert_id(); //$connect->Insert_Id(db_table_name_nq('questions'),"qid");
                            }
                            else
                            {
                                db_switchIDInsert('questions',true);
                                $query='INSERT into '.$this->db->dbprefix.'questions (qid, sid, gid, question_order, title, question, parent_qid, language, scale_id) values ('.$insertqid[$position].','.$surveyid.','.$gid.','.($position+1).',\''.$codes[$scale_id][$position].'\',\''.$subquestionvalue.'\','.$qid.',\''.$language.'\','.$scale_id.')';
                                db_execute_assoc($query);
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
                redirect(site_url('admin/question/subquestions/'.$surveyid.'/'.$gid.'/'.$qid));
            }
        }

        if ($action == "insertquestion" && bHasSurveyPermission($surveyid, 'surveycontent','create'))
        {
            $_POST = $this->input->post();
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            if (strlen($_POST['title']) < 1)
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n "
                ."alert(\"".$clang->gT("The question could not be added. You must enter at least enter a question code.","js")."\")\n "
                ."//-->\n</script>\n";
            }
            else
            {
                $this->load->helper('database');

                if (!isset($_POST['lid']) || $_POST['lid'] == '') {$_POST['lid']="0";}
                if (!isset($_POST['lid1']) || $_POST['lid1'] == '') {$_POST['lid1']="0";}
                if(!empty($_POST['questionposition']) || $_POST['questionposition'] == '0')
                {
                    //Bug Fix: remove +1 ->  $question_order=(sanitize_int($_POST['questionposition'])+1);
                    $question_order=(sanitize_int($_POST['questionposition']));
                    //Need to renumber all questions on or after this
                    $cdquery = "UPDATE ".$this->db->dbprefix."questions SET question_order=question_order+1 WHERE gid=".$gid." AND question_order >= ".$question_order;
                    $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());  // Checked)
                } else {
                    $question_order=(getMaxquestionorder($gid,$surveyid));
                    $question_order++;
                }

                /**if ($filterxsshtml)
                {
                    require_once("../classes/inputfilter/class.inputfilter_clean.php");
                    $myFilter = new InputFilter('','',1,1,1);
                    $_POST['title']=$myFilter->process($_POST['title']);
                    $_POST['question_'.$baselang]=$myFilter->process($_POST['question_'.$baselang]);
                    $_POST['help_'.$baselang]=$myFilter->process($_POST['help_'.$baselang]);
                }
                else
                {
                    $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");
                    $_POST['question_'.$baselang] = html_entity_decode($_POST['question_'.$baselang], ENT_QUOTES, "UTF-8");
                    $_POST['help_'.$baselang] = html_entity_decode($_POST['help_'.$baselang], ENT_QUOTES, "UTF-8");
                } */

                // Fix bug with FCKEditor saving strange BR types
                $_POST['title']=fix_FCKeditor_text($_POST['title']);
                $_POST['question_'.$baselang]=fix_FCKeditor_text($_POST['question_'.$baselang]);
                $_POST['help_'.$baselang]=fix_FCKeditor_text($_POST['help_'.$baselang]);

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

                $this->load->model("questions_model");
                $result = $this->questions_model->insertRecords($data);
                /**
                $query = "INSERT INTO ".db_table_name('questions')." (sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                ." VALUES ('{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
                ." '{$_POST['question_'.$baselang]}', '{$_POST['preg']}', '{$_POST['help_'.$baselang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$baselang}')";
                */
                //$result = $connect->Execute($query);  // Checked
                // Get the last inserted questionid for other languages
                $qid=$this->db->insert_id(); //$connect->Insert_ID(db_table_name_nq('questions'),"qid");

                // Add other languages
                if ($result)
                {
                    $addlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    foreach ($addlangs as $alang)
                    {
                        if ($alang != "")
                        {
                            db_switchIDInsert('questions',true);

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

                            $this->load->model("questions_model");
                            $result2 = $this->questions_model->insertRecords($data);
                            /**
                            $query = "INSERT INTO ".db_table_name('questions')." (qid, sid, gid, type, title, question, preg, help, other, mandatory, question_order, language)"
                            ." VALUES ('$qid','{$postsid}', '{$postgid}', '{$_POST['type']}', '{$_POST['title']}',"
                            ." '{$_POST['question_'.$alang]}', '{$_POST['preg']}', '{$_POST['help_'.$alang]}', '{$_POST['other']}', '{$_POST['mandatory']}', $question_order,'{$alang}')";
                            $result2 = $connect->Execute($query);  // Checked */
                            if (!$result2)
                            {
                                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".sprintf($clang->gT("Question in language %s could not be created.","js"),$alang)."\\n\")\n //-->\n</script>\n";

                            }
                            db_switchIDInsert('questions',false);
                    }
                    }
                }


                if (!$result)
                {
                    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be created.","js")."\\n\")\n //-->\n</script>\n";

                }

                $qattributes=questionAttributes();
                $validAttributes=$qattributes[$_POST['type']];
                foreach ($validAttributes as $validAttribute)
                {
                    if (isset($_POST[$validAttribute['name']]))
                    {
                        $data = array();
                        $data = array(
                                'qid' => $qid,
                                'value' => $_POST[$validAttribute['name']],
                                'attribute' => $validAttribute['name']

                        );


                        $this->load->model("question_attributes_model");
                        $result = $this->question_attributes_model->insertRecords($data);
                        /**$query = "INSERT into ".db_table_name('question_attributes')."
                                  (qid, value, attribute) values ($qid,'".db_quote($_POST[$validAttribute['name']])."','{$validAttribute['name']}')";
                        $result = $connect->Execute($query) or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg()); // Checked */

                    }
                }

                fixsortorderQuestions($gid, $surveyid);
                $this->session->set_userdata('flashmessage', $clang->gT("Question was successfully added."));

                //include("surveytable_functions.php");
                //surveyFixColumns($surveyid);
            }

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid.'/'.$gid.'/'.$qid));
            }
        }

        if ($action == "updatequestion" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            $_POST = $this->input->post();
            $this->load->helper('database');


            $cqquery = "SELECT type, gid FROM ".$this->db->dbprefix."questions WHERE qid={$qid}";
            $cqresult=db_execute_assoc($cqquery); // or safe_die ("Couldn't get question type to check for change<br />".$cqquery."<br />".$connect->ErrorMsg()); // Checked
            $cqr=$cqresult->row_array();
            $oldtype=$cqr['type'];
            $oldgid=$cqr['gid'];

            // Remove invalid question attributes on saving
            $qattributes=questionAttributes();
            $attsql="delete from ".$this->db->dbprefix."question_attributes where qid='{$qid}' and ";
            if (isset($qattributes[$_POST['type']])){
                $validAttributes=$qattributes[$_POST['type']];
                foreach ($validAttributes as  $validAttribute)
                {
                    //$attsql.='attribute<>'.db_quoteall($validAttribute['name'])." and ";
                    $attsql.='attribute<>\''.$validAttribute['name']."' and ";
                }
            }
            $attsql.='1=1';
            db_execute_assoc($attsql); // or safe_die ("Couldn't delete obsolete question attributes<br />".$attsql."<br />".$connect->ErrorMsg()); // Checked


            //now save all valid attributes
            $validAttributes=$qattributes[$_POST['type']];
            foreach ($validAttributes as $validAttribute)
            {
                if (isset($_POST[$validAttribute['name']]))
                {
                    $query = "select qaid from ".$this->db->dbprefix."question_attributes
                              WHERE attribute='".$validAttribute['name']."' AND qid=".$qid;
                    $result = db_execute_assoc($query); // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                    if ($result->Recordcount()>0)
                    {
                        $query = "UPDATE ".$this->db->dbprefix."question_attributes
                                  SET value='".$_POST[$validAttribute['name']]."' WHERE attribute='".$validAttribute['name']."' AND qid=".$qid;
                        $result = db_execute_assoc($query) ; // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
                    }
                    else
                    {
                        $query = "INSERT into ".$this->db->dbprefix."question_attributes
                                  (qid, value, attribute) values ($qid,'".$_POST[$validAttribute['name']]."','{$validAttribute['name']}')";
                        $result = db_execute_assoc($query); // or safe_die("Error updating attribute value<br />".$query."<br />".$connect->ErrorMsg());  // Checked
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
                //Make sure there are no conditions based on this question, since we are changing the type
                $ccquery = "SELECT * FROM ".$this->db->dbprefix."conditions WHERE cqid={$qid}";
                $ccresult = db_execute_assoc($ccquery); // or safe_die ("Couldn't get list of cqids for this question<br />".$ccquery."<br />".$connect->ErrorMsg()); // Checked
                $cccount=$ccresult->num_rows();
                foreach ($ccresult->result_array() as $ccr) {$qidarray[]=$ccr['qid'];}
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


                    $array_result=checkMovequestionConstraintsForConditions(sanitize_int($surveyid),sanitize_int($qid), sanitize_int($gid));
                    // If there is no blocking conditions that could prevent this move

                    if (is_null($array_result['notAbove']) && is_null($array_result['notBelow']))
                    {

                        $questlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                        $baselang = GetBaseLanguageFromSurveyID($surveyid);
                        array_push($questlangs,$baselang);
                        /**
                        if ($filterxsshtml)
                        {
                            require_once("../classes/inputfilter/class.inputfilter_clean.php");
                            $myFilter = new InputFilter('','',1,1,1);
                            $_POST['title']=$myFilter->process($_POST['title']);
                        }
                        else
                        {
                            $_POST['title'] = html_entity_decode($_POST['title'], ENT_QUOTES, "UTF-8");
                        }
                        */
                        // Fix bug with FCKEditor saving strange BR types
                        $_POST['title']=fix_FCKeditor_text($_POST['title']);

                        foreach ($questlangs as $qlang)
                        {
                            /**if ($filterxsshtml)
                            {
                                $_POST['question_'.$qlang]=$myFilter->process($_POST['question_'.$qlang]);
                                $_POST['help_'.$qlang]=$myFilter->process($_POST['help_'.$qlang]);
                            }
                            else
                            {
                                $_POST['question_'.$qlang] = html_entity_decode($_POST['question_'.$qlang], ENT_QUOTES, "UTF-8");
                                $_POST['help_'.$qlang] = html_entity_decode($_POST['help_'.$qlang], ENT_QUOTES, "UTF-8");
                            }
                            */
                            // Fix bug with FCKEditor saving strange BR types
                            $_POST['question_'.$qlang]=fix_FCKeditor_text($_POST['question_'.$qlang]);
                            $_POST['help_'.$qlang]=fix_FCKeditor_text($_POST['help_'.$qlang]);

                            if (isset($qlang) && $qlang != "")
                            { // ToDo: Sanitize the POST variables !



                                $uqquery = "UPDATE ".$this->db->dbprefix."questions SET type='".$_POST['type']."', title='".$_POST['title']."', "
                                . "question='".$_POST['question_'.$qlang]."', preg='".$_POST['preg']."', help='".$_POST['help_'.$qlang]."', "
                                . "gid='".$gid."', other='".$_POST['other']."', "
                                . "mandatory='".$_POST['mandatory']."'";

                                if ($oldgid!=$gid)
                                {

                                    if ( getGroupOrder($surveyid,$oldgid) > getGroupOrder($surveyid,$gid) )
                                    {
                                        // Moving question to a 'upper' group
                                        // insert question at the end of the destination group
                                        // this prevent breaking conditions if the target qid is in the dest group
                                        $insertorder = getMaxquestionorder($gid) + 1;
                                        $uqquery .=', question_order='.$insertorder.' ';
                                    }
                                    else
                                    {
                                        // Moving question to a 'lower' group
                                        // insert question at the beginning of the destination group
                                        shiftorderQuestions($surveyid,$gid,1); // makes 1 spare room for new question at top of dest group
                                        $uqquery .=', question_order=0 ';
                                    }
                                }
                                $uqquery.= "WHERE sid='".$surveyid."' AND qid='".$qid."' AND language='{$qlang}'";
                                $uqresult = db_execute_assoc($uqquery); // or safe_die ("Error Update Question: ".$uqquery."<br />".$connect->ErrorMsg());  // Checked
                                if (!$uqresult)
                                {
                                    $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Question could not be updated","js")."\n".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
                                }


                            }
                        }


                        // Update the group ID on subquestions, too
                        if ($oldgid!=$gid)
                        {
                            $sQuery="UPDATE ".$this->db->dbprefix."questions set gid={$gid} where gid={$oldgid} and parent_qid>0";
                            $oResult = db_execute_assoc($sQuery); // or safe_die ("Error updating question group ID: ".$uqquery."<br />".$connect->ErrorMsg());  // Checked
                        }
                        // if the group has changed then fix the sortorder of old and new group
                        if ($oldgid!=$gid)
                        {
                            fixsortorderQuestions($oldgid, $surveyid);
                            fixsortorderQuestions($gid, $surveyid);
                            // If some questions have conditions set on this question's answers
                            // then change the cfieldname accordingly
                            fixmovedquestionConditions($qid, $oldgid, $gid);
                        }

                        $query = "DELETE FROM ".$this->db->dbprefix."answers WHERE qid= {$qid} and scale_id>={$iAnswerScales}";
                            $result = db_execute_assoc($query); // or safe_die("Error: ".$connect->ErrorMsg()); // Checked

                        // Remove old subquestion scales
                        $query = "DELETE FROM ".$this->db->dbprefix."questions WHERE parent_qid={$qid} and scale_id>={$iSubquestionScales}";
                            $result = db_execute_assoc($query) ; //or safe_die("Error: ".$connect->ErrorMsg()); // Checked
                        $this->session->set_userdata('flashmessage',$clang->gT("Question was successfully saved."));


                    }
                    else
                    {

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
                redirect(site_url('admin/survey/view/'.$surveyid.'/'.$gid.'/'.$qid));
            }
        }

        if ($action == "insertquestiongroup" && bHasSurveyPermission($surveyid, 'surveycontent','create'))
        {

            $this->load->helper('surveytranslator');
            $this->load->helper('database');
            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $grplangs[] = $baselang;
            $errorstring = '';
            foreach ($grplangs as $grouplang)
            {
                if (!$this->input->post('group_name_'.$grouplang)) { $errorstring.= GetLanguageNameFromCode($grouplang,false)."\\n";}
            }
            if ($errorstring!='')
            {
                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be added.\\n\\nIt is missing the group name for the following languages","js").":\\n".$errorstring."\")\n //-->\n</script>\n";
            }

            else
            {
                $first=true;
                /**require_once("../classes/inputfilter/class.inputfilter_clean.php");
                $myFilter = new InputFilter('','',1,1,1);
                */
                foreach ($grplangs as $grouplang)
                {
                    //Clean XSS
                    /**if ($filterxsshtml)
                    {
                        $_POST['group_name_'.$grouplang]=$myFilter->process($_POST['group_name_'.$grouplang]);
                        $_POST['description_'.$grouplang]=$myFilter->process($_POST['description_'.$grouplang]);
                    }
                    else
                    {
                        $_POST['group_name_'.$grouplang] = html_entity_decode($_POST['group_name_'.$grouplang], ENT_QUOTES, "UTF-8");
                        $_POST['description_'.$grouplang] = html_entity_decode($_POST['description_'.$grouplang], ENT_QUOTES, "UTF-8");
                    } */

                    $group_name = $this->input->post('group_name_'.$grouplang);
                    $group_description = $this->input->post('description_'.$grouplang);

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name=fix_FCKeditor_text($group_name);
                    $group_description=fix_FCKeditor_text($group_description);


                    if ($first)
                    {
                        $data = array (
                            'sid' => $surveyid,
                            'group_name' => $group_name,
                            'description' => $group_description,
                            'group_order' => getMaxgrouporder($surveyid),
                            'language' => $grouplang

                        );
                        $this->load->model('groups_model');

                        //$query = "INSERT INTO ".db_table_name('groups')." (sid, group_name, description,group_order,language) VALUES ('".db_quote($postsid)."', '".db_quote($group_name)."', '".db_quote($group_description)."',".getMaxgrouporder(returnglobal('sid')).",'{$grouplang}')";
                        $result = $this->groups_model->insertRecords($data); //$connect->Execute($query); // Checked)
                        $groupid=$this->db->insert_id(); //$connect->Insert_Id(db_table_name_nq('groups'),"gid");
                        $first=false;

                    }
                    else{
                        db_switchIDInsert('groups',true);
                        $data = array (
                            'gid' => $groupid,
                            'sid' => $surveyid,
                            'group_name' => $group_name,
                            'description' => $group_description,
                            'group_order' => getMaxgrouporder($surveyid),
                            'language' => $grouplang

                        );
                        //$query = "INSERT INTO ".db_table_name('groups')." (gid, sid, group_name, description,group_order,language) VALUES ('{$groupid}','".db_quote($postsid)."', '".db_quote($group_name)."', '".db_quote($group_description)."',".getMaxgrouporder(returnglobal('sid')).",'{$grouplang}')";
                        $result = $this->groups_model->insertRecords($data); //$connect->Execute($query) or safe_die("Error<br />".$query."<br />".$connect->ErrorMsg());   // Checked
                        db_switchIDInsert('groups',false);
                    }
                    if (!$result)
                    {
                        $databaseoutput .= $clang->gT("Error: The database reported an error while executing INSERT query in addgroup action in database.php:")."<br />\n";

                        $databaseoutput .= "</body>\n</html>";
                        //exit;
                    }
                }
                // This line sets the newly inserted group as the new group
                if (isset($groupid)){$gid=$groupid;}
                $this->session->set_userdata('flashmessage', $clang->gT("New question group was saved."));

            }
            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid.'/'.$gid));
            }

        }


        if ($action == "updategroup" && bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            $this->load->helper('surveytranslator');
            $this->load->helper('database');

            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_push($grplangs,$baselang);
            //require_once("../classes/inputfilter/class.inputfilter_clean.php");
            //$myFilter = new InputFilter('','',1,1,1);
            foreach ($grplangs as $grplang)
            {
                if (isset($grplang) && $grplang != "")
                {
                    /**if ($filterxsshtml)
                    {
                        $_POST['group_name_'.$grplang]=$myFilter->process($_POST['group_name_'.$grplang]);
                        $_POST['description_'.$grplang]=$myFilter->process($_POST['description_'.$grplang]);
                    }
                    else
                    {
                        $_POST['group_name_'.$grplang] = html_entity_decode($_POST['group_name_'.$grplang], ENT_QUOTES, "UTF-8");
                        $_POST['description_'.$grplang] = html_entity_decode($_POST['description_'.$grplang], ENT_QUOTES, "UTF-8");
                    } */

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name = $this->input->post('group_name_'.$grplang);
                    $group_description = $this->input->post('description_'.$grplang);

                    $group_name=fix_FCKeditor_text($group_name);
                    $group_description=fix_FCKeditor_text($group_description);

                    // don't use array_map db_quote on POST
                    // since this is iterated for each language
                    //$_POST  = array_map('db_quote', $_POST);
                    $data = array (
                            'group_name' => $group_name,
                            'description' => $group_description
                        );
                    $condition = array (
                        'gid' => $gid,
                        'sid' => $surveyid,
                        'language' => $grplang
                    );
                    $this->load->model('groups_model');
                    //$ugquery = "UPDATE ".db_table_name('groups')." SET group_name='".db_quote($group_name)."', description='".db_quote($group_description)."' WHERE sid=".db_quote($surveyid)." AND gid=".db_quote($gid)." AND language='{$grplang}'";
                    $ugresult = $this->groups_model->update($data,$condition); //$connect->Execute($ugquery);  // Checked
                    if ($ugresult)
                    {
                        $groupsummary = getgrouplist($gid,$surveyid);
                    }
                    else
                    {
                        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be updated","js")."\")\n //-->\n</script>\n";

                    }
                }
            }
            $this->session->set_userdata('flashmessage', $clang->gT("Question group successfully saved."));

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid.'/'.$gid));
            }
        }

        if ($action == "insertsurvey" && $this->session->userdata('USER_RIGHT_CREATE_SURVEY'))
        {


            $this->load->helper("surveytranslator");
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            // $this->input->post['language']

            $supportedLanguages = getLanguageData();

            $numberformatid = $supportedLanguages[$this->input->post('language')]['radixpoint'];

            $url = $this->input->post('url');
            if ($url == 'http://') {$url="";}
            $surveyls_title = $this->input->post('surveyls_title');
            if (!$surveyls_title)
            {

                $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Survey could not be created because it did not have a title","js")."\")\n //-->\n</script>\n";
            } else
            {
                $this->load->helper('database');
                // Get random ids until one is found that is not used
                do
                {
                    $surveyid = sRandomChars(5,'123456789');
                    $isquery = "SELECT sid FROM ".$this->db->dbprefix('surveys')." WHERE sid=$surveyid";
                    $isresult = db_execute_assoc($isquery); // Checked
                }
                while ($isresult->num_rows()>0);


                $description = $this->input->post('description');
                $welcome = $this->input->post('welcome');
                $urldescp = $this->input->post('urldescrip');

                $template = $this->input->post('template');
                if (!$template) {$template='default';}
                if($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1 && $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') != 1 && !hasTemplateManageRights($this->session->userdata('loginID'), $this->input->post('template'))) $template = "default";

                // insert base language into surveys_language_settings
                if ($this->config->item('filterxsshtml'))
                {
                    /**
                    require_once("../classes/inputfilter/class.inputfilter_clean.php");
                    $myFilter = new InputFilter('','',1,1,1);

                    $surveyls_title=$myFilter->process($surveyls_title);
                    $description=$myFilter->process($description);
                    $welcome=$myFilter->process($welcome);
                    $this->input->post['urldescrip']=$myFilter->process($this->input->post['urldescrip']); */
                }
                else
                {
                    $surveyls_title = html_entity_decode($surveyls_title, ENT_QUOTES, "UTF-8");
                    $description = html_entity_decode($description, ENT_QUOTES, "UTF-8");
                    $welcome = html_entity_decode($welcome, ENT_QUOTES, "UTF-8");
                    $urldescp = html_entity_decode($urldescp, ENT_QUOTES, "UTF-8");
                }

                //make sure only numbers are passed within the $this->input->post variable
                $dateformat = (int) $this->input->post('dateformat');
                $tokenlength = (int) $this->input->post('tokenlength');

                $expires = $this->input->post('expires');
                if (trim($expires)=='')
                {
                    $expires=null;
                }
                else
                {
                    $this->load->library('Date_Time_Converter',array($expires , "d.m.Y H:i"));
                    $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($expires , "d.m.Y H:i");
                    $browsedatafield=$datetimeobj->convert("Y-m-d H:i:s");
                    $expires=$browsedatafield;
                }
                $startdate = $this->input->post('startdate');
                if (trim($startdate)=='')
                {
                    $startdate=null;
                }
                else
                {
                    $this->load->library('Date_Time_Converter',array($startdate , "d.m.Y H:i"));
                    $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($startdate , "d.m.Y H:i");
                    $browsedatafield=$datetimeobj->convert("Y-m-d H:i:s");
                    $startdate=$browsedatafield;
                }


                $insertarray=array( 'sid'=>$surveyid,
                                    'owner_id'=>$this->session->userdata('loginID'),
                                    'admin'=>$this->input->post('admin'),
                                    'active'=>'N',
                                    'expires'=>$expires,
                                    'startdate'=>$startdate,
                                    'adminemail'=>$this->input->post('adminemail'),
                                    'bounce_email'=>$this->input->post('bounce_email'),
                                    'anonymized'=>$this->input->post('anonymized'),
                                    'faxto'=>$this->input->post('faxto'),
                                    'format'=>$this->input->post('format'),
                                    'savetimings'=>$this->input->post('savetimings'),
                                    'template'=>$template,
                                    'language'=>$this->input->post('language'),
                                    'datestamp'=>$this->input->post('datestamp'),
                                    'ipaddr'=>$this->input->post('ipaddr'),
                                    'refurl'=>$this->input->post('refurl'),
                                    'usecookie'=>$this->input->post('usecookie'),
                                    'emailnotificationto'=>$this->input->post('emailnotificationto'),
                                    'allowregister'=>$this->input->post('allowregister'),
                                    'allowsave'=>$this->input->post('allowsave'),
                                    'navigationdelay'=>$this->input->post('navigationdelay'),
                                    'autoredirect'=>$this->input->post('autoredirect'),
                                    'showXquestions'=>$this->input->post('showXquestions'),
                                    'showgroupinfo'=>$this->input->post('showgroupinfo'),
                                    'showqnumcode'=>$this->input->post('showqnumcode'),
                                    'shownoanswer'=>$this->input->post('shownoanswer'),
                                    'showwelcome'=>$this->input->post('showwelcome'),
                                    'allowprev'=>$this->input->post('allowprev'),
                                    'allowjumps'=>$this->input->post('allowjumps'),
                                    'nokeyboard'=>$this->input->post('nokeyboard'),
                                    'showprogress'=>$this->input->post('showprogress'),
                                    'printanswers'=>$this->input->post('printanswers'),
                //                            'usetokens'=>$this->input->post['usetokens'],
                                    'datecreated'=>date("Y-m-d"),
                                    'listpublic'=>$this->input->post('public'),
                                    'htmlemail'=>$this->input->post('htmlemail'),
                                    'tokenanswerspersistence'=>$this->input->post('tokenanswerspersistence'),
                                    'alloweditaftercompletion'=>$this->input->post('alloweditaftercompletion'),
                                    'usecaptcha'=>$this->input->post('usecaptcha'),
                                    'publicstatistics'=>$this->input->post('publicstatistics'),
                                    'publicgraphs'=>$this->input->post('publicgraphs'),
                                    'assessments'=>$this->input->post('assessments'),
                                    'emailresponseto'=>$this->input->post('emailresponseto'),
                                    'tokenlength'=>$tokenlength
                );

                /** $dbtablename=$this->db->dbprefix('surveys');
                $isquery = $connect->GetInsertSQL($dbtablename, $insertarray);
                $isresult = $connect->Execute($isquery) or safe_die ($isrquery."<br />".$connect->ErrorMsg()); // Checked */
                $this->load->model('surveys_model');
                $this->surveys_model->insertNewSurvey($insertarray);



                // Fix bug with FCKEditor saving strange BR types
                $surveyls_title=fix_FCKeditor_text($surveyls_title);
                $description=fix_FCKeditor_text($description);
                $welcome=fix_FCKeditor_text($welcome);

                $this->load->library('Limesurvey_lang',array($this->input->post('language')));
                $bplang = $this->limesurvey_lang; //new limesurvey_lang($this->input->post['language']);

                $aDefaultTexts=self::_aTemplateDefaultTexts($bplang,'unescaped');
                $is_html_email = false;
                if ($this->input->post('htmlemail') && $this->input->post('htmlemail') == "Y")
                {
                    $is_html_email = true;
                    $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditional_nl2br($aDefaultTexts['admin_detailed_notification'],$is_html_email,'unescaped');
                }

                $insertarray=array( 'surveyls_survey_id'=>$surveyid,
                                    'surveyls_language'=>$this->input->post('language'),
                                    'surveyls_title'=>$surveyls_title,
                                    'surveyls_description'=>$description,
                                    'surveyls_welcometext'=>$welcome,
                                    'surveyls_urldescription'=>$this->input->post('urldescrip'),
                                    'surveyls_endtext'=>$this->input->post('endtext'),
                                    'surveyls_url'=>$url,
                                    'surveyls_email_invite_subj'=>$aDefaultTexts['invitation_subject'],
                                    'surveyls_email_invite'=>conditional_nl2br($aDefaultTexts['invitation'],$is_html_email,'unescaped'),
                                    'surveyls_email_remind_subj'=>$aDefaultTexts['reminder_subject'],
                                    'surveyls_email_remind'=>conditional_nl2br($aDefaultTexts['reminder'],$is_html_email,'unescaped'),
                                    'surveyls_email_confirm_subj'=>$aDefaultTexts['confirmation_subject'],
                                    'surveyls_email_confirm'=>conditional_nl2br($aDefaultTexts['confirmation'],$is_html_email,'unescaped'),
                                    'surveyls_email_register_subj'=>$aDefaultTexts['registration_subject'],
                                    'surveyls_email_register'=>conditional_nl2br($aDefaultTexts['registration'],$is_html_email,'unescaped'),
                                    'email_admin_notification_subj'=>$aDefaultTexts['admin_notification_subject'],
                                    'email_admin_notification'=>conditional_nl2br($aDefaultTexts['admin_notification'],$is_html_email,'unescaped'),
                                    'email_admin_responses_subj'=>$aDefaultTexts['admin_detailed_notification_subject'],
                                    'email_admin_responses'=>$aDefaultTexts['admin_detailed_notification'],
                                    'surveyls_dateformat'=>$dateformat,
                                    'surveyls_numberformat'=>$numberformatid
                                  );
                /**$dbtablename=db_table_name_nq('surveys_languagesettings');
                $isquery = $connect->GetInsertSQL($dbtablename, $insertarray);
                $isresult = $connect->Execute($isquery) or safe_die ($isquery."<br />".$connect->ErrorMsg()); // Checked */
                $this->load->model('surveys_languagesettings_model');
                $this->surveys_languagesettings_model->insertNewSurvey($insertarray);
                unset($bplang);

                $this->session->set_userdata('flashmessage',$clang->gT("Survey was successfully added."));


                // Update survey permissions
                self::_GiveAllSurveyPermissions($this->session->userdata('loginID'),$surveyid);

                $surveyselect = getsurveylist();

                // Create initial Survey table
                //include("surveytable_functions.php");
                //$creationResult = surveyCreateTable($surveyid);
                // Survey table could not be created
                //if ($creationResult !== true)
                //{
                //    safe_die ("Initial survey table could not be created, please report this as a bug."."<br />".$creationResult);
                //}
            }
            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid));
            }
        }


        if (($action == "updatesurveylocalesettings") && bHasSurveyPermission($surveyid,'surveylocale','update'))
        {
            $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
            $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
            /**require_once("../classes/inputfilter/class.inputfilter_clean.php");
            $myFilter = new InputFilter('','',1,1,1);
            */
            foreach ($languagelist as $langname)
            {
                if ($langname)
                {
                    $url = $this->input->post('url_'.$langname);
                    if ($url == 'http://') {$url="";}

                    // Clean XSS attacks
                    /**if ($filterxsshtml) //not required. As we are using input class, XSS filetring is done automatically!
                    {
                        $_POST['short_title_'.$langname]=$myFilter->process($_POST['short_title_'.$langname]);
                        $_POST['description_'.$langname]=$myFilter->process($_POST['description_'.$langname]);
                        $_POST['welcome_'.$langname]=$myFilter->process($_POST['welcome_'.$langname]);
                        $_POST['endtext_'.$langname]=$myFilter->process($_POST['endtext_'.$langname]);
                        $_POST['urldescrip_'.$langname]=$myFilter->process($_POST['urldescrip_'.$langname]);
                        $_POST['url_'.$langname]=$myFilter->process($_POST['url_'.$langname]);
                    }
                    else
                    {
                        $_POST['short_title_'.$langname] = html_entity_decode($_POST['short_title_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['description_'.$langname] = html_entity_decode($_POST['description_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['welcome_'.$langname] = html_entity_decode($_POST['welcome_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['endtext_'.$langname] = html_entity_decode($_POST['endtext_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['urldescrip_'.$langname] = html_entity_decode($_POST['urldescrip_'.$langname], ENT_QUOTES, "UTF-8");
                        $_POST['url_'.$langname] = html_entity_decode($_POST['url_'.$langname], ENT_QUOTES, "UTF-8");
                    } */

                    // Fix bug with FCKEditor saving strange BR types
                    $short_title = $this->input->post('short_title_'.$langname);
                    $description = $this->input->post('description_'.$langname);
                    $welcome = $this->input->post('welcome_'.$langname);
                    $endtext = $this->input->post('endtext_'.$langname);

                    $short_title=fix_FCKeditor_text($short_title);
                    $description=fix_FCKeditor_text($description);
                    $welcome=fix_FCKeditor_text($welcome);
                    $endtext=fix_FCKeditor_text($endtext);

                    $data = array(
                    'surveyls_title' => $short_title,
                    'surveyls_description' => $description,
                    'surveyls_welcometext' => $welcome,
                    'surveyls_endtext' => $endtext,
                    'surveyls_url' => $url,
                    'surveyls_urldescription' => $this->input->post('urldescrip_'.$langname),
                    'surveyls_dateformat' => $this->input->post('dateformat_'.$langname),
                    'surveyls_numberformat' => $this->input->post('numberformat_'.$langname)
                    );
                    //In 'surveyls_survey_id' => $surveyid, it was initially $postsid. returnglobal not working properly!
                    $condition = array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $langname);
                    /**
                    $usquery = "UPDATE ".db_table_name('surveys_languagesettings')." \n"
                    . "SET surveyls_title='".db_quote($short_title)."', surveyls_description='".db_quote($description)."',\n"
                    . "surveyls_welcometext='".db_quote($welcome)."',\n"
                    . "surveyls_endtext='".db_quote($endtext)."',\n"
                    . "surveyls_url='".db_quote($url)."',\n"
                    . "surveyls_urldescription='".db_quote($_POST['urldescrip_'.$langname])."',\n"
                    . "surveyls_dateformat='".db_quote($_POST['dateformat_'.$langname])."',\n"
                    . "surveyls_numberformat='".db_quote($_POST['numberformat_'.$langname])."'\n"
                    . "WHERE surveyls_survey_id=".$postsid." and surveyls_language='".$langname."'"; */
                    $this->load->model('surveys_languagesettings_model');

                    $usresult = $this->surveys_languagesettings_model->update($data,$condition);// or safe_die("Error updating local settings");   // Checked
                }
            }
            $this->session->set_userdata('flashmessage',$clang->gT("Survey text elements successfully saved."));
            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid));
            }
        }

        if (($action == "updatesurveysettingsandeditlocalesettings" || $action == "updatesurveysettings") && bHasSurveyPermission($surveyid,'surveysettings','update'))
        {
            $this->load->helper('surveytranslator');
            $this->load->helper('database');
            $formatdata=getDateFormatData($this->session->userdata('dateformat'));

            $expires = $this->input->post('expires');
            if (trim($expires)=="")
            {
                $expires=null;
            }
            else
            {
                $this->load->library('Date_Time_Converter',array($expires, $formatdata['phpdate'].' H:i'));
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($expires, $formatdata['phpdate'].' H:i');
                $expires=$datetimeobj->convert("Y-m-d H:i:s");
            }
            $startdate = $this->input->post('startdate');
            if (trim($startdate)=="")
            {
                $startdate=null;
            }
            else
            {
                $this->load->library('Date_Time_Converter',array($startdate,$formatdata['phpdate'].' H:i'));
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($startdate,$formatdata['phpdate'].' H:i');
                $startdate=$datetimeobj->convert("Y-m-d H:i:s");
            }

            //make sure only numbers are passed within the $_POST variable
            $tokenlength = (int) $this->input->post('tokenlength');
            //$_POST['tokenlength'] = (int) $_POST['tokenlength'];

            //token length has to be at least 5, otherwise set it to default (15)
            if($tokenlength < 5)
            {
                $tokenlength = 15;
            }


            CleanLanguagesFromSurvey($surveyid,$this->input->post('languageids'));

            FixLanguageConsistency($surveyid,$this->input->post('languageids'));
            $template = $this->input->post('template');

            if($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1 && $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') != 1 && !hasTemplateManageRights($this->session->userdata('loginID'), $template)) $template = "default";

            //$sql = "SELECT * FROM ".$this->db->dbprefix."surveys WHERE sid={$postsid}";  // We are using $dbrepfix here instead of db_table_name on purpose because GetUpdateSQL doesn't work correclty on Postfres with a quoted table name
            //$rs = db_execute_assoc($sql); // Checked
            $updatearray= array('admin'=> $this->input->post('admin'),
                                'expires'=>$expires,
                                'adminemail'=> $this->input->post('adminemail'),
                                'startdate'=>$startdate,
                                'bounce_email'=> $this->input->post('bounce_email'),
                                'anonymized'=> $this->input->post('anonymized'),
                                'faxto'=> $this->input->post('faxto'),
                                'format'=> $this->input->post('format'),
                                'savetimings'=> $this->input->post('savetimings'),
                                'template'=>$template,
                                'assessments'=> $this->input->post('assessments'),
                                'language'=> $this->input->post('language'),
                                'additional_languages'=> $this->input->post('languageids'),
                                'datestamp'=> $this->input->post('datestamp'),
                                'ipaddr'=> $this->input->post('ipaddr'),
                                'refurl'=> $this->input->post('refurl'),
                                'publicgraphs'=> $this->input->post('publicgraphs'),
                                'usecookie'=> $this->input->post('usecookie'),
                                'allowregister'=> $this->input->post('allowregister'),
                                'allowsave'=> $this->input->post('allowsave'),
                                'navigationdelay'=> $this->input->post('navigationdelay'),
                                'printanswers'=> $this->input->post('printanswers'),
                                'publicstatistics'=> $this->input->post('publicstatistics'),
                                'autoredirect'=> $this->input->post('autoredirect'),
                                'showXquestions'=> $this->input->post('showXquestions'),
                                'showgroupinfo'=> $this->input->post('showgroupinfo'),
                                'showqnumcode'=> $this->input->post('showqnumcode'),
                                'shownoanswer'=> $this->input->post('shownoanswer'),
                                'showwelcome'=> $this->input->post('showwelcome'),
                                'allowprev'=> $this->input->post('allowprev'),
                                'allowjumps'=> $this->input->post('allowjumps'),
                                'nokeyboard'=> $this->input->post('nokeyboard'),
                                'showprogress'=> $this->input->post('showprogress'),
                                'listpublic'=> $this->input->post('public'),
                                'htmlemail'=> $this->input->post('htmlemail'),
                                'tokenanswerspersistence'=> $this->input->post('tokenanswerspersistence'),
                                'alloweditaftercompletion'=> $this->input->post('alloweditaftercompletion'),
                                'usecaptcha'=> $this->input->post('usecaptcha'),
                                'emailresponseto'=>trim($this->input->post('emailresponseto')),
                                'emailnotificationto'=>trim($this->input->post('emailnotificationto')),
                                'tokenlength'=>$tokenlength
            );


            /**$usquery=$connect->GetUpdateSQL($rs, $updatearray, false, get_magic_quotes_gpc());
            if ($usquery) {
                $usresult = $connect->Execute($usquery) or safe_die("Error updating<br />".$usquery."<br /><br /><strong>".$connect->ErrorMsg());  // Checked
            }
            */
            $condition = array('sid' =>  $surveyid);
            $this->load->model('surveys_model');
            $this->surveys_model->updateSurvey($updatearray,$condition);
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

            $usquery = "DELETE FROM ".$this->db->dbprefix."surveys_languagesettings WHERE surveyls_survey_id={$surveyid} ".$sqlstring;

            $usresult = db_execute_assoc($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>"); // Checked

            foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
            {
                if ($langname)
                {
                    $usquery = "select * from ".$this->db->dbprefix."surveys_languagesettings where surveyls_survey_id={$surveyid} and surveyls_language='".$langname."'";
                    $usresult = db_execute_assoc($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br /><strong>"); // Checked
                    if ($usresult->num_rows()==0)
                    {
                        $this->load->library('Limesurvey_lang',array($langname));
                        $bplang = $this->limesurvey_lang;//new limesurvey_lang($langname);
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
                        /**$usquery = "INSERT INTO ".db_table_name('surveys_languagesettings')
                        ." (surveyls_survey_id, surveyls_language, surveyls_title, "
                        ." surveyls_email_invite_subj, surveyls_email_invite, "
                        ." surveyls_email_remind_subj, surveyls_email_remind, "
                        ." surveyls_email_confirm_subj, surveyls_email_confirm, "
                        ." surveyls_email_register_subj, surveyls_email_register, "
                        ." email_admin_notification_subj, email_admin_notification, "
                        ." email_admin_responses_subj, email_admin_responses, "
                        ." surveyls_dateformat) "
                        ." VALUES ({$postsid}, '".$langname."', '',"
                        .db_quoteall($aDefaultTexts['invitation_subject']).","
                        .db_quoteall($aDefaultTexts['invitation']).","
                        .db_quoteall($aDefaultTexts['reminder_subject']).","
                        .db_quoteall($aDefaultTexts['reminder']).","
                        .db_quoteall($aDefaultTexts['confirmation_subject']).","
                        .db_quoteall($aDefaultTexts['confirmation']).","
                        .db_quoteall($aDefaultTexts['registration_subject']).","
                        .db_quoteall($aDefaultTexts['registration']).","
                        .db_quoteall($aDefaultTexts['admin_notification_subject']).","
                        .db_quoteall($aDefaultTexts['admin_notification']).","
                        .db_quoteall($aDefaultTexts['admin_detailed_notification_subject']).","
                        .db_quoteall($aDefaultTexts['admin_detailed_notification']).","
                        .$languagedetails['dateformat'].")"; */
                        $this->load->model('surveys_languagesettings_model');

                        $usresult = $this->surveys_languagesettings_model->insertNewSurvey($insertdata);
                        unset($bplang);
                        //$usresult = $connect->Execute($usquery) or safe_die("Error deleting obsolete surveysettings<br />".$usquery."<br /><br />".$connect->ErrorMsg()); // Checked
                    }
                }
            }



            if ($usresult)
            {
                $surveyselect = getsurveylist();
                $this->session->set_userdata('flashmessage', $clang->gT("Survey settings were successfully saved."));

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

                if ($this->input->post('action') == "updatesurveysettingsandeditlocalesettings")
                {
                   redirect(site_url('admin/survey/editlocalsettings/'.$surveyid));
                }
                else
                {
                    redirect(site_url('admin/survey/view/'.$surveyid));
                }

            }
        }

        if (!$action)
        {
            redirect("/admin","refresh");
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
       //global $connect;
       $this->load->helper('database');
       if ($defaultvalue=='')  // Remove the default value if it is empty
       {
          $query = "DELETE FROM ".$this->db->dbprefix."defaultvalues WHERE sqid=$sqid AND qid=$qid AND specialtype='$specialtype' AND scale_id={$scale_id} AND language='{$language}'";
          db_execute_assoc($query);
          //$connect->execute("DELETE FROM ".db_table_name('defaultvalues')." WHERE sqid=$sqid AND qid=$qid AND specialtype='$specialtype' AND scale_id={$scale_id} AND language='{$language}'");
       }
       else
       {
           $query = "SELECT qid FROM ".$this->db->dbprefix."defaultvalues WHERE sqid=$sqid AND qid=$qid AND specialtype=$specialtype'' AND scale_id={$scale_id} AND language='{$language}'";
           $res = db_execute_assoc($query);
           $exists=$res->num_rows(); //$connect->GetOne("SELECT qid FROM ".$this->db->dbprefix."defaultvalues WHERE sqid=$sqid AND qid=$qid AND specialtype=$specialtype'' AND scale_id={$scale_id} AND language='{$language}'");)
           //if ($exists===false || $exists===null)
           if ($exists == 0)
           {
               $query = 'INSERT INTO '.$this->db->dbprefix."defaultvalues (defaultvalue,qid,scale_id,language,specialtype,sqid) VALUES ('".$defaultvalue."',{$qid},{$scale_id},'{$language}','{$specialtype}',{$sqid})";
               db_execute_assoc($query);
               //$connect->execute('INSERT INTO '.$this->db->dbprefix."defaultvalues (defaultvalue,qid,scale_id,language,specialtype,sqid) VALUES (".db_quoteall($defaultvalue,$ispost).",{$qid},{$scale_id},'{$language}','{$specialtype}',{$sqid})");
           }
           else
           {
               $query = 'UPDATE '.$this->db->dbprefix."defaultvalues set defaultvalue='".$defaultvalue."'  WHERE sqid={$sqid} AND qid={$qid} AND specialtype='{$specialtype}' AND scale_id={$scale_id} AND language='{$language}'";
               db_execute_assoc($query);
               //$connect->execute('UPDATE '.$this->db->dbprefix."defaultvalues set defaultvalue='".$defaultvalue."'  WHERE sqid={$sqid} AND qid={$qid} AND specialtype='{$specialtype}' AND scale_id={$scale_id} AND language='{$language}'");
           }
       }
    }

    /** Database::_aTemplateDefaultTexts()
    * Returns the default email template texts as array
    *
    * @param mixed $oLanguage Required language translationb object
    * @param string $mode Escape mode for the translation function
    * @return array
    */
    function _aTemplateDefaultTexts($oLanguage, $mode='html'){
        return array(
          'admin_detailed_notification_subject'=>$oLanguage->gT("Response submission for survey {SURVEYNAME} with results",$mode),
          'admin_detailed_notification'=>$oLanguage->gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}\n\n\nThe following answers were given by the participant:\n{ANSWERTABLE}",$mode),
          'admin_detailed_notification_css'=>'<style type="text/css">
                                                    .printouttable {
                                                      margin:1em auto;
                                                    }
                                                    .printouttable th {
                                                      text-align: center;
                                                    }
                                                    .printouttable td {
                                                      border-color: #ddf #ddf #ddf #ddf;
                                                      border-style: solid;
                                                      border-width: 1px;
                                                      padding:0.1em 1em 0.1em 0.5em;
                                                    }

                                                    .printouttable td:first-child {
                                                      font-weight: 700;
                                                      text-align: right;
                                                      padding-right: 5px;
                                                      padding-left: 5px;

                                                    }
                                                    .printouttable .printanswersquestion td{
                                                      background-color:#F7F8FF;
                                                    }

                                                    .printouttable .printanswersquestionhead td{
                                                      text-align: left;
                                                      background-color:#ddf;
                                                    }

                                                    .printouttable .printanswersgroup td{
                                                      text-align: center;
                                                      font-weight:bold;
                                                      padding-top:1em;
                                                    }
                                                    </style>',
          'admin_notification_subject'=>$oLanguage->gT("Response submission for survey {SURVEYNAME}",$mode),
          'admin_notification'=>$oLanguage->gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}",$mode),
          'confirmation_subject'=>$oLanguage->gT("Confirmation of your participation in our survey"),
          'confirmation'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nthis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",$mode),
          'invitation_subject'=>$oLanguage->gT("Invitation to participate in a survey",$mode),
          'invitation'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nyou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".$oLanguage->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode),
          'reminder_subject'=>$oLanguage->gT("Reminder to participate in a survey",$mode),
          'reminder'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".$oLanguage->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode),
          'registration_subject'=>$oLanguage->gT("Survey registration confirmation",$mode),
          'registration'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",$mode)
        );
    }

    /** Database::_GiveAllSurveyPermissions()
    * Gives all available survey permissions for a certain survey to a user
    *
    * @param mixed $iUserID  The User ID
    * @param mixed $iSurveyID The Survey ID
    */
    function _GiveAllSurveyPermissions($iUserID, $iSurveyID)
    {
         //$clang = $this->Limesurvey_lang;
         $aPermissions=aGetBaseSurveyPermissions();

         $aPermissionsToSet=array();
         foreach ($aPermissions as $sPermissionName=>$aPermissionDetails)
         {
             foreach ($aPermissionDetails as $sPermissionDetailKey=>$sPermissionDetailValue)
             {
               if (in_array($sPermissionDetailKey,array('create','read','update','delete','import','export')) && $sPermissionDetailValue==true)
               {
                   $aPermissionsToSet[$sPermissionName][$sPermissionDetailKey]=1;
               }

             }
         }

         self::_SetSurveyPermissions($iUserID, $iSurveyID, $aPermissionsToSet);
    }

    /** Database::_SetSurveyPermissions()
    * Set the survey permissions for a user. Beware that all survey permissions for the particual survey are removed before the new ones are written.
    *
    * @param int $iUserID The User ID
    * @param int $iSurveyID The Survey ID
    * @param array $aPermissions  Array with permissions in format <permissionname>=>array('create'=>0/1,'read'=>0/1,'update'=>0/1,'delete'=>0/1)
    */
    function _SetSurveyPermissions($iUserID, $iSurveyID, $aPermissions)
    {
        //global $connect, $surveyid;
        $iUserID=sanitize_int($iUserID);
        $condition = array('sid' => $iSurveyID, 'uid' => $iUserID);
        $this->load->model('survey_permissions_model');
        $this->survey_permissions_model->deleteSomeRecords($condition);
        //$sQuery = "delete from ".db_table_name('survey_permissions')." WHERE sid = {$iSurveyID} AND uid = {$iUserID}";
        //$connect->Execute($sQuery);
        $bResult=true;

        foreach($aPermissions as $sPermissionname=>$aPermissions)
        {
            if (!isset($aPermissions['create'])) {$aPermissions['create']=0;}
            if (!isset($aPermissions['read'])) {$aPermissions['read']=0;}
            if (!isset($aPermissions['update'])) {$aPermissions['update']=0;}
            if (!isset($aPermissions['delete'])) {$aPermissions['delete']=0;}
            if (!isset($aPermissions['import'])) {$aPermissions['import']=0;}
            if (!isset($aPermissions['export'])) {$aPermissions['export']=0;}
            if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
            {
                //$sQuery = "INSERT INTO ".db_table_name('survey_permissions')." (sid, uid, permission, create_p, read_p, update_p, delete_p, import_p, export_p)
               //           VALUES ({$iSurveyID},{$iUserID},'{$sPermissionname}',{$aPermissions['create']},{$aPermissions['read']},{$aPermissions['update']},{$aPermissions['delete']},{$aPermissions['import']},{$aPermissions['export']})";

                $data = array();
                $data = array(
                        'sid' => $iSurveyID,
                        'uid' => $iUserID,
                        'permission' => $sPermissionname,
                        'create_p' => $aPermissions['create'],
                        'read_p' => $aPermissions['read'],
                        'update_p' => $aPermissions['update'],
                        'delete_p' => $aPermissions['delete'],
                        'import_p' => $aPermissions['import'],
                        'export_p' => $aPermissions['export']
                        );
                $this->load->model('survey_permissions_model');
                $this->survey_permissions_model->insertSomeRecords($data);
                //$bResult=$connect->Execute($sQuery);
            }
        }
        //return $bResult;
    }



}
