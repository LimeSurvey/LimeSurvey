<?php
/*
 * LimeSurvey
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id$
 */
 
/**
 * Survey Common Controller
 *
 * This controller contains common functions for survey related views.
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 * @author		LimeSurvey
 */
 class SurveyCommonController extends AdminController {
 
 	/**
	 * Constructor
	 */
    function __construct()
	{
		parent::__construct();
	}
    
    
    /**
	 * Shows admin menu for question
	 * @param int Survey id
     * @param int Group id
     * @param int Question id
     * @param string action
	 */
     function _questionbar($surveyid,$gid,$qid,$action)
     {
        
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        
        // TODO: check that surveyid is set and that so is $baselang
        //Show Question Details
    	//Count answer-options for this question
        $qrq = "SELECT * FROM ".$this->db->dbprefix."answers WHERE qid=$qid AND language='".$baselang."' ORDER BY sortorder, answer";
        $qrr = db_execute_assoc($qrq); //Checked)
        $data['qct'] = $qct = $qrr->num_rows();
    	//Count sub-questions for this question
    	$sqrq= "SELECT * FROM ".$this->db->dbprefix."questions WHERE parent_qid=$qid AND language='".$baselang."'";
    	$sqrr= db_execute_assoc($sqrq); //Checked
    	$data['sqct'] = $sqct = $sqrr->num_rows();
    	
        $qrquery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE gid=$gid AND sid=$surveyid AND qid=$qid AND language='".$baselang."'";
        $qrresult = db_execute_assoc($qrquery); // or safe_die($qrquery."<br />".$connect->ErrorMsg()); //Checked
        $questionsummary = "<div class='menubar'>\n";
    
        // Check if other questions in the Survey are dependent upon this question
        $condarray=GetQuestDepsForConditions($surveyid,"all","all",$qid,"by-targqid","outsidegroup");
        $this->load->model('surveys_model');
        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $data['activated'] = $activated = $surveyinfo['active'];
    
        // PREVIEW THIS QUESTION BUTTON
    
        foreach ($qrresult->result_array() as $qrrow)
        {
            $qrrow = array_map('FlattenText', $qrrow);
            //$qrrow = array_map('htmlspecialchars', $qrrow);
            /**$questionsummary .= "<div class='menubar-title ui-widget-header'>\n"
            . "<strong>". $clang->gT("Question")."</strong> <span class='basic'>{$qrrow['question']} (".$clang->gT("ID").":$qid)</span>\n"
            . "</div>\n"
            . "<div class='menubar-main'>\n"
            . "<div class='menubar-left'>\n"
            . "<img src='$imageurl/blank.gif' alt='' width='55' height='20' />\n"
            . "<img src='$imageurl/seperator.gif' alt='' />\n"; */
            if(bHasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
                {
                    /*$questionsummary .= "<a href=\"#\" accesskey='q' onclick=\"window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid', '_blank')\""
                    . "title=\"".$clang->gTview("Preview This Question")."\">"
                    . "<img src='$imageurl/preview.png' alt='".$clang->gT("Preview This Question")."' name='previewquestionimg' /></a>\n"
                    . "<img src='$imageurl/seperator.gif' alt='' />\n"; */
                } else {
                    /**$questionsummary .= "<a href=\"#\" accesskey='q' id='previewquestion'"
                    . "title=\"".$clang->gTview("Preview This Question")."\">"
                    . "<img src='$imageurl/preview.png' title='' alt='".$clang->gT("Preview This Question")."' name='previewquestionimg' /></a>\n"
                    . "<img src='$imageurl/seperator.gif' alt=''  />\n"; */
        
                    //
                    $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    $baselang = GetBaseLanguageFromSurveyID($surveyid);
                    $tmp_survlangs[] = $baselang;
                    rsort($tmp_survlangs);
    
                    // Test question Language Selection Popup
                    /**$surveysummary .="<div class=\"langpopup\" id=\"previewquestionpopup\">".$clang->gT("Please select a language:")."<ul>";
                    foreach ($tmp_survlangs as $tmp_lang)
                    {
                        $surveysummary .= "<li><a target='_blank' onclick=\"$('#previewquestion').qtip('hide');\" href='{$scriptname}?action=previewquestion&amp;sid={$surveyid}&amp;qid={$qid}&amp;lang={$tmp_lang}' accesskey='d'>".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
                    }
                    $surveysummary .= "</ul></div>"; */
                }
            }
    
            // SEPARATOR
    
    //        $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='117' height='20'  />\n";
    
    
            // EDIT CURRENT QUESTION BUTTON
    
            /**if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            {
                $questionsummary .= ""
    //            ."<img src='$imageurl/seperator.gif' alt='' />\n"
                . "<a href='$scriptname?action=editquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                . " title=\"".$clang->gTview("Edit current question")."\">"
                . "<img src='$imageurl/edit.png' alt='".$clang->gT("Edit Current Question")."' name='EditQuestion' /></a>\n" ;
            }
    
    
            // DELETE CURRENT QUESTION BUTTON
    
            if ((($qct == 0 && $activated != "Y") || $activated != "Y") && bHasSurveyPermission($surveyid,'surveycontent','delete'))
            {
                if (is_null($condarray))
                {
                    $questionsummary .= "<a href='#'" .
    				"onclick=\"if (confirm('".$clang->gT("Deleting this question will also delete any answer options and subquestions it includes. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."}\">"
    				. "<img src='$imageurl/delete.png' name='DeleteWholeQuestion' alt='".$clang->gT("Delete current question")."' "
    				. "border='0' hspace='0' /></a>\n";
                }
                else
                {
                    $questionsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
    				"onclick=\"alert('".$clang->gT("It's impossible to delete this question because there is at least one question having a condition on it.","js")."')\""
    				. "title=\"".$clang->gTview("Disabled - Delete current question")."\">"
    				. "<img src='$imageurl/delete_disabled.png' name='DeleteWholeQuestion' alt='".$clang->gT("Disabled - Delete current question")."' /></a>\n";
                }
            }
            else {$questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";}
    
    
            // EXPORT CURRENT QUESTION BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','export'))
            {
                $questionsummary .= "<a href='$scriptname?action=exportstructureQuestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                . " title=\"".$clang->gTview("Export this question")."\" >"
                . "<img src='$imageurl/dumpquestion.png' alt='".$clang->gT("Export this question")."' name='ExportQuestion' /></a>\n";
            }
    
            $questionsummary .= "<img src='$imageurl/seperator.gif' alt='' />\n";
    
    
            // COPY CURRENT QUESTION BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                if ($activated != "Y")
                {
                    $questionsummary .= "<a href='$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                    . " title=\"".$clang->gTview("Copy Current Question")."\" >"
                    . "<img src='$imageurl/copy.png'  alt='".$clang->gT("Copy Current Question")."' name='CopyQuestion' /></a>\n"
                    . "<img src='$imageurl/seperator.gif' alt='' />\n";
                }
                else
                {
                    $questionsummary .= "<a href='#' title=\"".$clang->gTview("Copy Current Question")."\" "
                    . "onclick=\"alert('".$clang->gT("You can't copy a question if the survey is active.","js")."')\">"
                    . "<img src='$imageurl/copy_disabled.png' alt='".$clang->gT("Copy Current Question")."' name='CopyQuestion' /></a>\n"
                    . "<img src='$imageurl/seperator.gif' alt='' />\n";
                }
            }
            else
            {
                $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
            }
    
    
            // SET EXTENDED CONDITIONS FOR QUESTION BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            {
                $questionsummary .= "<a href='#' onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;qid=$qid&amp;gid=$gid&amp;subaction=editconditionsform', '_top')\""
                . " title=\"".$clang->gTview("Set/view conditions for this question")."\">"
                . "<img src='$imageurl/conditions.png' alt='".$clang->gT("Set conditions for this question")."'  name='SetQuestionConditions' /></a>\n"
                . "<img src='$imageurl/seperator.gif' alt='' />\n";
            }
            else
            {
                $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
            } */
    
    
            // EDIT SUBQUESTIONS FOR THIS QUESTION BUTTON
    
            $data['qtypes'] = $qtypes=getqtypelist('','array');
            
            /**if(bHasSurveyPermission($surveyid,'surveycontent','read'))
            {
                if ($qtypes[$qrrow['type']]['subquestions'] >0)
                {
                    $questionsummary .=  "<a href='".$scriptname."?action=editsubquestions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                    ."title=\"".$clang->gTview("Edit subquestions for this question")."\">"
                    ."<img src='$imageurl/subquestions.png' alt='".$clang->gT("Edit subquestions for this question")."' name='EditSubquestions' /></a>\n" ;
                }
            }
            else
            {
                $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
            }
    
    
            // EDIT ANSWER OPTIONS FOR THIS QUESTION BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['answerscales'] >0)
            {
                $questionsummary .=  "<a href='".$scriptname."?action=editansweroptions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                ."title=\"".$clang->gTview("Edit answer options for this question")."\">"
                ."<img src='$imageurl/answers.png' alt='".$clang->gT("Edit answer options for this question")."' name='EdtAnswerOptions' /></a>\n" ;
            }
            else
            {
                $questionsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
            }
    
    
            // EDIT DEFAULT ANSWERS FOR THIS QUESTION BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','read') && $qtypes[$qrrow['type']]['hasdefaultvalues'] >0)
            {
                $questionsummary .=  "<a href='".$scriptname."?action=editdefaultvalues&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'"
                ."title=\"".$clang->gTview("Edit default answers for this question")."\">"
                ."<img src='$imageurl/defaultanswers.png' alt='".$clang->gT("Edit default answers for this question")."' name='EdtAnswerOptions' /></a>\n" ;
            }
            $questionsummary .= "</div>\n"
            . "<div class='menubar-right'>\n"
            . "<input type='image' src='$imageurl/minus.gif' title='"
            . $clang->gT("Hide Details of this Question")."'  alt='". $clang->gT("Hide Details of this Question")."' name='MinimiseQuestionWindow' "
            . "onclick='document.getElementById(\"questiondetails\").style.display=\"none\";' />\n"
            . "<input type='image' src='$imageurl/plus.gif' title='"
            . $clang->gT("Show Details of this Question")."'  alt='". $clang->gT("Show Details of this Question")."' name='MaximiseQuestionWindow' "
            . "onclick='document.getElementById(\"questiondetails\").style.display=\"\";' />\n"
            . "<input type='image' src='$imageurl/close.gif' title='"
            . $clang->gT("Close this Question")."' alt='". $clang->gT("Close this Question")."' name='CloseQuestionWindow' "
            . "onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid', '_top')\" />\n"
            . "</div>\n"
            . "</div>\n"
            . "</div>\n";
            $questionsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
            */
            if ($action=='editansweroptions' || $action =="editsubquestions" || $action =="editquestion" || $action =="editdefaultvalues" || $action =="copyquestion")
            {
                $qshowstyle = "style='display: none'";
            }
            else
            {
                $qshowstyle = "";
            }
            $data['qshowstyle'] = $qshowstyle;
            $data['action'] = $action;
            $data['surveyid'] = $surveyid;
            $data['qid'] = $qid;
            $data['gid'] = $gid;
            $data['clang'] = $clang;
            $data['qrrow'] = $qrrow;
            /**
            $questionsummary .= "<table  id='questiondetails' $qshowstyle><tr><td width='20%' align='right'><strong>"
            . $clang->gT("Code:")."</strong></td>\n"
            . "<td align='left'>{$qrrow['title']}";
            if ($qrrow['type'] != "X")
            {
                if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>".$clang->gT("Mandatory Question")."</i>)";}
                else {$questionsummary .= ": (<i>".$clang->gT("Optional Question")."</i>)";}
            }
            $questionsummary .= "</td></tr>\n"
            . "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Question:")."</strong></td>\n<td align='left'>".$qrrow['question']."</td></tr>\n"
            . "<tr><td align='right' valign='top'><strong>"
            . $clang->gT("Help:")."</strong></td>\n<td align='left'>";
            if (trim($qrrow['help'])!=''){$questionsummary .= $qrrow['help'];}
            $questionsummary .= "</td></tr>\n";
            if ($qrrow['preg'])
            {
                $questionsummary .= "<tr ><td align='right' valign='top'><strong>"
                . $clang->gT("Validation:")."</strong></td>\n<td align='left'>{$qrrow['preg']}"
                . "</td></tr>\n";
            }
            $qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
            $questionsummary .= "<tr><td align='right' valign='top'><strong>"
            .$clang->gT("Type:")."</strong></td>\n<td align='left'>{$qtypes[$qrrow['type']]['description']}";
            $questionsummary .="</td></tr>\n";
            if ($qct == 0 && $qtypes[$qrrow['type']]['answerscales'] >0)
            {
                $questionsummary .= "<tr ><td></td><td align='left'>"
                . "<span class='statusentryhighlight'>"
                . $clang->gT("Warning").": <a href='{$scriptname}?sid={$surveyid}&amp;gid={$gid}&amp;qid={$qid}&amp;action=editansweroptions'>". $clang->gT("You need to add answer options to this question")." "
                . "<img src='$imageurl/answers_20.png' title='"
                . $clang->gT("Edit answer options for this question")."' name='EditThisQuestionAnswers'/></span></td></tr>\n";
            }
    
    
            // EDIT SUBQUESTIONS FOR THIS QUESTION BUTTON
    
            if($sqct == 0 && $qtypes[$qrrow['type']]['subquestions'] >0)
            {
               $questionsummary .= "<tr ><td></td><td align='left'>"
                . "<span class='statusentryhighlight'>"
                . $clang->gT("Warning").": <a href='{$scriptname}?sid={$surveyid}&amp;gid={$gid}&amp;qid={$qid}&amp;action=editsubquestions'>". $clang->gT("You need to add subquestions to this question")." "
                . "<img src='$imageurl/subquestions_20.png' title='"
                . $clang->gT("Edit subquestions for this question")."' name='EditThisQuestionAnswers' /></span></td></tr>\n";
            }
    
            if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
            {
                $questionsummary .= "<tr>"
                . "<td align='right' valign='top'><strong>"
                . $clang->gT("Option 'Other':")."</strong></td>\n"
                . "<td align='left'>";
                $questionsummary .= ($qrrow['other'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
                $questionsummary .= "</td></tr>\n";
            }
            if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X") and ($qrrow['type'] != "|"))
            {
                $questionsummary .= "<tr>"
                . "<td align='right' valign='top'><strong>"
                . $clang->gT("Mandatory:")."</strong></td>\n"
                . "<td align='left'>";
                $questionsummary .= ($qrrow['mandatory'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
                $questionsummary .= "</td></tr>\n";
            }
            if (!is_null($condarray))
            {
                $questionsummary .= "<tr>"
                . "<td align='right' valign='top'><strong>"
                . $clang->gT("Other questions having conditions on this question:")
                . "</strong></td>\n<td align='left' valign='bottom'>\n";
                foreach ($condarray[$qid] as $depqid => $depcid)
                {
                    $listcid=implode("-",$depcid);
                    $questionsummary .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."','_top')\">[QID: ".$depqid."]</a>";
                }
                $questionsummary .= "</td></tr>";
            }
            $questionsummary .= "</table>"; */
            
            $questionsummary .= $this->load->view("admin/Survey/Question/questionbar_view",$data,true);
        } 
        $finaldata['display'] = $questionsummary;
        $this->load->view('survey_view',$finaldata);
        
     }
    
    /**
	 * Shows admin menu for question groups
	 * @param int Survey id
     * @param int Group id
	 */
    function _questiongroupbar($surveyid,$gid,$qid=null,$action)
    {
        
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        
        // TODO: check that surveyid and thus baselang are always set here
        $sumquery4 = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid AND
    	gid=$gid AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult4 = db_execute_assoc($sumquery4); //Checked
        $sumcount4 = $sumresult4->num_rows();
        $grpquery ="SELECT * FROM ".$this->db->dbprefix."groups WHERE gid=$gid AND
    	language='".$baselang."' ORDER BY ".$this->db->dbprefix."groups.group_order";
        $grpresult = db_execute_assoc($grpquery); //Checked
        
        // Check if other questions/groups are dependent upon this group
        $condarray=GetGroupDepsForConditions($surveyid,"all",$gid,"by-targgid");
    
        $groupsummary = "<div class='menubar'>\n"
        . "<div class='menubar-title ui-widget-header'>\n";
        
        $this->load->model('surveys_model');
        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $data['activated'] = $activated = $surveyinfo['active'];
        
        foreach ($grpresult->result_array() as $grow)
        {
            $grow = array_map('FlattenText', $grow);
            $data = array();
            
            /**$groupsummary .= '<strong>'.$clang->gT("Question group").'</strong>&nbsp;'
            . "<span class='basic'>{$grow['group_name']} (".$clang->gT("ID").":$gid)</span>\n"
            . "</div>\n"
            . "<div class='menubar-main'>\n"
            . "<div class='menubar-left'>\n";
    
    
    //        // CREATE BLANK SPACE FOR IMAGINARY BUTTONS
    //
    //
            $groupsummary .= ""
            . "<img src='$imageurl/blank.gif' alt='' width='54' height='20'  />\n";
    
            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            {
                $groupsummary .=  "<img src='$imageurl/seperator.gif' alt=''  />\n"
                . "<a href=\"#\" onclick=\"window.open('$scriptname?action=previewgroup&amp;sid=$surveyid&amp;gid=$gid','_blank')\""
                . " title=\"".$clang->gTview("Preview current question group")."\">"
                . "<img src='$imageurl/preview.png' alt='".$clang->gT("Preview current question group")."' name='PreviewGroup' /></a>\n" ;
            }
            else{
                $groupsummary .=  "<img src='$imageurl/seperator.gif' alt=''  />\n";
            }
    
    
    
            // EDIT CURRENT QUESTION GROUP BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            {
                $groupsummary .=  "<img src='$imageurl/seperator.gif' alt=''  />\n"
                . "<a href=\"#\" onclick=\"window.open('$scriptname?action=editgroup&amp;sid=$surveyid&amp;gid=$gid','_top')\""
                . " title=\"".$clang->gTview("Edit current question group")."\">"
                . "<img src='$imageurl/edit.png' alt='".$clang->gT("Edit current question group")."' name='EditGroup' /></a>\n" ;
            }
    
    
            // DELETE CURRENT QUESTION GROUP BUTTON
    
            if (bHasSurveyPermission($surveyid,'surveycontent','delete'))
            {
                if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y"))
                {
                    if (is_null($condarray))
                    {
                        //				$groupsummary .= "<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid' onclick=\"return confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')\""
                        $groupsummary .= "<a href='#' onclick=\"if (confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid")."}\""
                        . " title=\"".$clang->gTview("Delete current question group")."\">"
                        . "<img src='$imageurl/delete.png' alt='".$clang->gT("Delete current question group")."' name='DeleteWholeGroup' title=''  /></a>\n";
                        //get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid");
                    }
                    else
                    {
                        $groupsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid' onclick=\"alert('".$clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js")."')\""
                        . " title=\"".$clang->gTview("Delete current question group")."\">"
                        . "<img src='$imageurl/delete_disabled.png' alt='".$clang->gT("Delete current question group")."' name='DeleteWholeGroup' /></a>\n";
                    }
                }
                else
                {
                    $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
                }
            }
    
    
            // EXPORT QUESTION GROUP BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','export'))
            {
    
                $groupsummary .="<a href='$scriptname?action=exportstructureGroup&amp;sid=$surveyid&amp;gid=$gid' title=\"".$clang->gTview("Export this question group")."\" >"
                . "<img src='$imageurl/dumpgroup.png' title='' alt='".$clang->gT("Export this question group")."' name='ExportGroup'  /></a>\n";
            }
    
    
            // CHANGE QUESTION ORDER BUTTON
    
            if(bHasSurveyPermission($surveyid,'surveycontent','update'))
            {
                $groupsummary .= "<img src='$imageurl/seperator.gif' alt='' />\n";
                if($activated!="Y" && getQuestionSum($surveyid, $gid)>1)
                {
    //                $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
    //                $groupsummary .= "<img src='$imageurl/seperator.gif' alt='' />\n";
                    $groupsummary .= "<a href='$scriptname?action=orderquestions&amp;sid=$surveyid&amp;gid=$gid' title=\"".$clang->gTview("Change Question Order")."\" >"
                    . "<img src='$imageurl/reorder.png' alt='".$clang->gT("Change Question Order")."' name='updatequestionorder' /></a>\n" ;
                }
                else
                {
                    $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='40' />\n";
                }
            }
    
            $groupsummary.= "</div>\n"
            . "<div class='menubar-right'>\n"
            . "<span class=\"boxcaption\">".$clang->gT("Questions").":</span><select class=\"listboxquestions\" name='qid' "
            . "onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">"
            . getQuestions($surveyid,$gid,$qid)
            . "</select>\n";
    
            
            */
                    // QUICK NAVIGATION TO PREVIOUS AND NEXT QUESTION
            // TODO: Fix functionality to previos and next question  buttons (Andrie)
            
            
            
            $data['qid'] = $qid;
            //$data['qid'] = $qid = $this->config->item('qid');
            $data['QidPrev'] = $QidPrev = getQidPrevious($surveyid, $gid, $qid);
            
            /**$groupsummary .= "<span class='arrow-wrapper'>";
            if ($QidPrev != "")
            {
              $groupsummary .= ""
                . "<a href='{$scriptname}?sid=$surveyid&amp;gid=$gid&amp;qid=$QidPrev'>"
                . "<img src='{$imageurl}/previous_20.png' title='' alt='".$clang->gT("Previous question")."' "
                ."name='questiongroupprevious'/></a>";
            }
            else
            {
              $groupsummary .= ""
                . "<img src='{$imageurl}/previous_disabled_20.png' title='' alt='".$clang->gT("No previous question")."' "
                ."name='noquestionprevious' />";
            }
    
            */
            
            $data['QidNext'] = $QidNext = getQidNext($surveyid, $gid, $qid);
            
            /**
            if ($QidNext != "")
            {
              $groupsummary .= ""
                . "<a href='{$scriptname}?sid=$surveyid&amp;gid=$gid&amp;qid=$QidNext'>"
                . "<img src='{$imageurl}/next_20.png' title='' alt='".$clang->gT("Next question")."' "
                ."name='questiongroupnext' ".$clang->gT("Next question")."/> </a>";
            }
            else
            {
              $groupsummary .= ""
                . "<img src='{$imageurl}/next_disabled_20.png' title='' alt='".$clang->gT("No next question")."' "
                ."name='noquestionnext' />";
            }
            $groupsummary .= "</span>";
    
    
    
            // ADD NEW QUESTION TO GROUP BUTTON
    
            if ($activated == "Y")
            {
                $groupsummary .= "<a href='#'"
                ."<img src='$imageurl/add_disabled.png' title='' alt='".$clang->gT("Disabled").' - '.$clang->gT("This survey is currently active.")."' " .
                " name='AddNewQuestion' onclick=\"window.open('', '_top')\" /></a>\n";
            }
            elseif(bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                $groupsummary .= "<a href='$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid'"
                ." title=\"".$clang->gTview("Add New Question to Group")."\" >"
                ."<img src='$imageurl/add.png' title='' alt='".$clang->gT("Add New Question to Group")."' " .
                " name='AddNewQuestion' onclick=\"window.open('', '_top')\" /></a>\n";
            }
    
    
            // Separator
    
            $groupsummary .= "<img src='$imageurl/seperator.gif' alt=''  />";
    
            $groupsummary.= "<img src='$imageurl/blank.gif' width='18' alt='' />"
            . "<input id='MinimizeGroupWindow' type='image' src='$imageurl/minus.gif' title='"
            . $clang->gT("Hide Details of this Group")."' alt='". $clang->gT("Hide Details of this Group")."' name='MinimizeGroupWindow' />\n";
            $groupsummary .= "<input type='image' id='MaximizeGroupWindow' src='$imageurl/plus.gif' title='"
            . $clang->gT("Show Details of this Group")."' alt='". $clang->gT("Show Details of this Group")."' name='MaximizeGroupWindow' />\n";
            if (!$qid)
            {
                $groupsummary .= "<input type='image' src='$imageurl/close.gif' title='"
                . $clang->gT("Close this Group")."' alt='". $clang->gT("Close this Group")."'  name='CloseSurveyWindow' "
                . "onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n";
            }
            else
            {
                $groupsummary .= "<img src='$imageurl/blank.gif' alt='' width='18' />\n";
            }
            $groupsummary .="</div></div>\n"
            . "</div>\n"; */
            //  $groupsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
            
            if ($action=='editgroup'|| $action=='addquestion' || $action == 'viewquestion') 
            {
                $gshowstyle="style='display: none'";
            }
            else
            {
                $gshowstyle="";
            }
            $data['gshowstyle'] = $gshowstyle; 
            
            /**
            $groupsummary .= "<table id='groupdetails' $gshowstyle ><tr ><td width='20%' align='right'><strong>"
            . $clang->gT("Title").":</strong></td>\n"
            . "<td align='left'>"
            . "{$grow['group_name']} ({$grow['gid']})</td></tr>\n"
            . "<tr><td valign='top' align='right'><strong>"
            . $clang->gT("Description:")."</strong></td>\n<td align='left'>";
            if (trim($grow['description'])!='') {$groupsummary .=$grow['description'];}
            $groupsummary .= "</td></tr>\n";
    
            if (!is_null($condarray))
            {
                $groupsummary .= "<tr><td align='right'><strong>"
                . $clang->gT("Questions with conditions to this group").":</strong></td>\n"
                . "<td valign='bottom' align='left'>";
                foreach ($condarray[$gid] as $depgid => $deprow)
                {
                    foreach ($deprow['conditions'] as $depqid => $depcid)
                    {
                        //$groupsummary .= "[QID: ".$depqid."]";
                        $listcid=implode("-",$depcid);
                        $groupsummary .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."','_top')\">[QID: ".$depqid."]</a>";
                    }
                }
                $groupsummary .= "</td></tr>";
            } */
            
            $data['surveyid'] = $surveyid;
            $data['gid'] = $gid;
            $data['grow'] = $grow;
            $data['clang'] = $clang;
            $data['condarray'] = $condarray;
            $data['sumcount4'] = $sumcount4;
            $groupsummary .= $this->load->view('admin/Survey/QuestionGroups/questiongroupbar_view',$data,true);
        }
        $groupsummary .= "\n</table>\n";
        
        $finaldata['display'] = $groupsummary;
        $this->load->view('survey_view',$finaldata);
        
    } 

    /**
	 * Shows admin menu for surveys
	 * @param int Survey id
	 */
    function _surveybar($surveyid,$gid=null)
    {
    	//$this->load->helper('surveytranslator');
    	$clang = $this->limesurvey_lang;
		//echo $this->config->item('gid');
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $condition = array('sid' => $surveyid, 'language' => $baselang);
        $this->load->model('surveys_model');
        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = $surveyinfo['active'];

        $js_admin_includes = $this->config->item("js_admin_includes");
        $js_admin_includes[]=$this->config->item('generalscripts').'jquery/jquery.coookie.js';
        $js_admin_includes[]=$this->config->item('generalscripts').'jquery/superfish.js';
        $js_admin_includes[]=$this->config->item('generalscripts').'jquery/hoverIntent.js';
        $js_admin_includes[]=$this->config->item('adminscripts').'surveytoolbar.js';
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
		$this->config->set_item("css_admin_includes", $css_admin_includes);
        $this->config->set_item("js_admin_includes", $js_admin_includes); 
        
		//Parse data to send to view
		$data['clang']=$clang;
		$data['surveyinfo']=$surveyinfo;
		$data['surveyid']=$surveyid;
		
		// ACTIVATE SURVEY BUTTON
		$data['activated'] = ($activated=="Y") ? true : false;
		$data['imageurl'] = $this->config->item('imageurl');
		
        $condition = array('sid' => $surveyid, 'parent_qid' => 0, 'language' => $baselang);
        $this->load->model('questions_model');      
        //$sumquery3 =  "SELECT * FROM ".db_table_name('questions')." WHERE sid={$surveyid} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = $this->questions_model->getAllRecords($condition); //$connect->Execute($sumquery3); //Checked
        $sumcount3 = $sumresult3->num_rows();
		
		$data['canactivate'] = $sumcount3>0 && bHasSurveyPermission($surveyid,'surveyactivation','update');
		$data['candeactivate'] = bHasSurveyPermission($surveyid,'surveyactivation','update');
		$data['expired'] = $surveyinfo['expires']!='' && ($surveyinfo['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $this->config->item('timeadjust')));
		$data['notstarted'] = ($surveyinfo['startdate']!='') && ($surveyinfo['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $this->config->item('timeadjust')));
		
        // Start of suckerfish menu
        // TEST BUTTON
  		if ($activated == "N")
        {
            $data['icontext']=$clang->gT("Test This Survey");
            $data['icontext2']=$clang->gTview("Test This Survey");
        } else
        {
            $data['icontext']=$clang->gT("Execute This Survey");
            $data['icontext2']=$clang->gTview("Execute This Survey");
        }
		
        $data['baselang'] = GetBaseLanguageFromSurveyID($surveyid);
 		$data['onelanguage'] = (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0);
		
		$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$data['additionallanguages'] = $tmp_survlangs;
        $tmp_survlangs[] = $data['baselang'];
		rsort($tmp_survlangs);
		$data['languagelist'] = $tmp_survlangs;
		
		$data['hasadditionallanguages']=(count($data['additionallanguages']) > 0);

        // EDIT SURVEY TEXT ELEMENTS BUTTON
        $data['surveylocale']=bHasSurveyPermission($surveyid,'surveylocale','read');
        // EDIT SURVEY SETTINGS BUTTON
        $data['surveysettings']=bHasSurveyPermission($surveyid,'surveysettings','read');
        // Survey permission item
        $data['surveysecurity']=($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $surveyinfo['owner_id'] == $this->session->userdata('loginID'));
         // CHANGE QUESTION GROUP ORDER BUTTON
        $data['surveycontent']=bHasSurveyPermission($surveyid,'surveycontent','read');
		$data['groupsum']=(getGroupSum($surveyid,$surveyinfo['language'])>1);
        // SET SURVEY QUOTAS BUTTON
        $data['quotas'] = bHasSurveyPermission($surveyid,'quotas','read');
        // Assessment menu item
        $data['assessments'] = bHasSurveyPermission($surveyid,'assessments','read');
        // EDIT SURVEY TEXT ELEMENTS BUTTON
        // End if survey properties

        // Tools menu item     
        // Delete survey item
        $data['surveydelete'] = bHasSurveyPermission($surveyid,'survey','delete');
        // Translate survey item
        $data['surveytranslate'] = bHasSurveyPermission($surveyid,'translations','read');
        // RESET SURVEY LOGIC BUTTON
        //$sumquery6 = "SELECT count(*) FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')." as q WHERE c.qid = q.qid AND q.sid=$surveyid"; //Getting a count of conditions for this survey
        $this->load->model('conditions_model');
        $query = $this->conditions_model->getCountOfConditions($surveyid);
        $sumcount6 = $query->row_array(); //$connect->GetOne($sumquery6); //Checked
        $data['surveycontent'] = bHasSurveyPermission($surveyid,'surveycontent','update');
		$data['conditionscount'] = ($sumcount6 > 0);
        // Eport menu item
        $data['surveyexport']=bHasSurveyPermission($surveyid,'surveycontent','export');
        // PRINTABLE VERSION OF SURVEY BUTTON
        // SHOW PRINTABLE AND SCANNABLE VERSION OF SURVEY BUTTON

        //browse responses menu item
        $data['respstatsread']=bHasSurveyPermission($surveyid,'responses','read') || bHasSurveyPermission($surveyid,'statistics','read');
        // Data entry screen menu item
        $data['responsescreate']=bHasSurveyPermission($surveyid,'responses','create');
        $data['responsesread'] = bHasSurveyPermission($surveyid,'responses','read');
        // TOKEN MANAGEMENT BUTTON
		$data['tokenmanagement'] = bHasSurveyPermission($surveyid,'surveysettings','update') || bHasSurveyPermission($surveyid,'tokens','read');
        
        $data['gid'] = $gid ;// = $this->input->post('gid');
        
        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $data['permission']= true;
        }
        else
        {
            $data['gid'] = $gid =null;
            $qid=null;
        } 
        
        if (getgrouplistlang($gid, $baselang,$surveyid))
        {
            $data['groups']= getgrouplistlang($gid, $baselang,$surveyid);
        }
        else
        {
            $data['groups']= "<option>".$clang->gT("None")."</option>";
        }
        
        $data['GidPrev'] = $GidPrev = getGidPrevious($surveyid, $gid);
        
        $data['GidNext'] = $GidNext = getGidNext($surveyid, $gid);
        $data['activated'] = $activated;
        
        $this->load->view("admin/survey/surveybar",$data);
		
        
        /**
         ////////////////////////////////////////////////////////////////////////
        // QUESTION GROUP TOOLBAR
        ////////////////////////////////////////////////////////////////////////

        $surveysummary.= "<div class='menubar-right'>\n";
        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $surveysummary .= "<span class=\"boxcaption\">".$clang->gT("Question groups").":</span>"
            . "<select name='groupselect' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";

            if (getgrouplistlang($gid, $baselang))
            {
                $surveysummary .= getgrouplistlang($gid, $baselang);
            }
            else
            {
                $surveysummary .= "<option>".$clang->gT("None")."</option>\n";
            }
            $surveysummary .= "</select>\n";
        }
        else
        {
            $gid=null;
            $qid=null;
        }

        // QUICK NAVIGATION TO PREVIOUS AND NEXT QUESTION GROUP
        // TODO: Fix functionality to previous and next question group buttons (Andrie)
        $GidPrev = getGidPrevious($surveyid, $gid);
        $surveysummary .= "<span class='arrow-wrapper'>";
        if ($GidPrev != "")
        {
          $link = site_url("admin/index/index/$surveyid/$GidPrev");
          $surveysummary .= ""
            . "<a href='{$link}'>"
            . "<img src='".$this->config->item('imageurl')."/previous_20.png' title='' alt='".$clang->gT("Previous question group")."' "
            ."name='questiongroupprevious' ".$clang->gT("Previous question group")."/> </a>";
        }
        else
        {
          $surveysummary .= ""
            . "<img src='".$this->config->item('imageurl')."/previous_disabled_20.png' title='' alt='".$clang->gT("No previous question group")."' "
            ."name='noquestiongroupprevious' />";
        }

        $GidNext = getGidNext($surveyid, $gid);
        if ($GidNext != "")
        {
          $link = site_url("admin/index/index/$surveyid/$GidNext");
          $surveysummary .= ""
            . "<a href='{$link}'>"
            . "<img src='".$this->config->item('imageurl')."/next_20.png' title='' alt='".$clang->gT("Next question group")."' "
            ."name='questiongroupnext' /> </a>";
        }
        else
        {
          $surveysummary .= ""
            . "<img src='".$this->config->item('imageurl')."/next_disabled_20.png' title='' alt='".$clang->gT("No next question group")."' "
            ."name='noquestiongroupnext' />";
        }
		$surveysummary .= "</span>";


        // ADD NEW GROUP TO SURVEY BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','create'))
        {
            if ($activated == "Y")
            {
                $surveysummary .= "<a href='#'>"
                ."<img src='".$this->config->item('imageurl')."/add_disabled.png' title='' alt='".$clang->gT("Disabled").' - '.$clang->gT("This survey is currently active.")."' " .
                " name='AddNewGroup' /></a>\n";
            }
            else
            {
                $link = site_url("admin/addgroup/index/$surveyid");
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$link', '_top')\""
                . " title=\"".$clang->gTview("Add new group to survey")."\">"
                . "<img src='".$this->config->item('imageurl')."/add.png' alt='".$clang->gT("Add new group to survey")."' name='AddNewGroup' /></a>\n";
            }
        }
        $surveysummary .= "<img src='".$this->config->item('imageurl')."/seperator.gif' alt='' />\n"
        . "<img src='".$this->config->item('imageurl')."/blank.gif' width='15' alt='' />"
        . "<input type='image' src='".$this->config->item('imageurl')."/minus.gif' title='". $clang->gT("Hide details of this Survey")."' "
        . "alt='". $clang->gT("Hide details of this Survey")."' name='MinimiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"none\";' />\n";

        $surveysummary .= "<input type='image' src='".$this->config->item('imageurl')."/plus.gif' title='". $clang->gT("Show details of this survey")."' "
        . "alt='". $clang->gT("Show details of this survey")."' name='MaximiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"\";' />\n";

        if (!$gid)
        {
            $link = site_url("admin/index/index");
            $surveysummary .= "<input type='image' src='".$this->config->item('imageurl')."/close.gif' title='". $clang->gT("Close this survey")."' "
            . "alt='".$clang->gT("Close this survey")."' name='CloseSurveyWindow' "
            . "onclick=\"window.open('$link', '_top')\" />\n";
        }
        else
        {
            $surveysummary .= "<img src='".$this->config->item('imageurl')."/blank.gif' width='18' alt='' />\n";
        }

        $surveysummary .= "</div>\n"
        . "</div>\n"
        . "</div>\n";
        */
        
        
    }
    
	/**
	 * Show survey summary
	 * @param int Survey id
     * @param string Action to be performed
	 */
    function _surveysummary($surveyid,$action=null)
    {
        $clang = $this->limesurvey_lang;
		
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
        $condition = array('sid' => $surveyid, 'language' => $baselang);
        $this->load->model('surveys_model');
        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = $surveyinfo['active'];
		
		$condition = array('sid' => $surveyid, 'parent_qid' => 0, 'language' => $baselang);
        $this->load->model('questions_model');      
        //$sumquery3 =  "SELECT * FROM ".db_table_name('questions')." WHERE sid={$surveyid} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = $this->questions_model->getAllRecords($condition); //$connect->Execute($sumquery3); //Checked
        $sumcount3 = $sumresult3->num_rows();
        
		$condition = array('sid' => $surveyid, 'language' => $baselang);
		$this->load->model('groups_model');
		//$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid={$surveyid} AND language='".$baselang."'"; //Getting a count of groups for this survey
		$sumresult2 = $this->groups_model->getAllRecords($condition); //$connect->Execute($sumquery2); //Checked
		$sumcount2 = $sumresult2->num_rows();
        
        //SURVEY SUMMARY

        $aAdditionalLanguages = GetAdditionalLanguagesFromSurveyID($surveyid);
        $surveysummary2 = "";
        if ($surveyinfo['anonymized'] != "N") {$surveysummary2 .= $clang->gT("Answers to this survey are anonymized.")."<br />\n";}
        else {$surveysummary2 .= $clang->gT("This survey is NOT anonymous.")."<br />\n";}
        if ($surveyinfo['format'] == "S") {$surveysummary2 .= $clang->gT("It is presented question by question.")."<br />\n";}
        elseif ($surveyinfo['format'] == "G") {$surveysummary2 .= $clang->gT("It is presented group by group.")."<br />\n";}
        else {$surveysummary2 .= $clang->gT("It is presented on one single page.")."<br />\n";}
        if ($surveyinfo['allowjumps'] == "Y")
        {
          if ($surveyinfo['format'] == 'A') {$surveysummary2 .= $clang->gT("No question index will be shown with this format.")."<br />\n";}
          else {$surveysummary2 .= $clang->gT("A question index will be shown; participants will be able to jump between viewed questions.")."<br />\n";}
        }
        if ($surveyinfo['datestamp'] == "Y") {$surveysummary2 .= $clang->gT("Responses will be date stamped.")."<br />\n";}
        if ($surveyinfo['ipaddr'] == "Y") {$surveysummary2 .= $clang->gT("IP Addresses will be logged")."<br />\n";}
        if ($surveyinfo['refurl'] == "Y") {$surveysummary2 .= $clang->gT("Referrer URL will be saved.")."<br />\n";}
        if ($surveyinfo['usecookie'] == "Y") {$surveysummary2 .= $clang->gT("It uses cookies for access control.")."<br />\n";}
        if ($surveyinfo['allowregister'] == "Y") {$surveysummary2 .= $clang->gT("If tokens are used, the public may register for this survey")."<br />\n";}
        if ($surveyinfo['allowsave'] == "Y" && $surveyinfo['tokenanswerspersistence'] == 'N') {$surveysummary2 .= $clang->gT("Participants can save partially finished surveys")."<br />\n";}
        if ($surveyinfo['emailnotificationto'] != '') 
        {
            $surveysummary2 .= $clang->gT("Basic email notification is sent to:")." {$surveyinfo['emailnotificationto']}<br />\n";
        }
        if ($surveyinfo['emailresponseto'] != '') 
        {
            $surveysummary2 .= $clang->gT("Detailed email notification with response data is sent to:")." {$surveyinfo['emailresponseto']}<br />\n";
        }

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $surveysummary2 .= $clang->gT("Regenerate question codes:")
            . " [<a href='#' "
            . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post(base_url()."?action=renumberquestions&amp;sid=$surveyid&amp;style=straight")."}\" "
            . ">".$clang->gT("Straight")."</a>] "
            . " [<a href='#' "
            . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post(base_url()."?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup")."}\" "
            . ">".$clang->gT("By Group")."</a>]";
            $surveysummary2 .= "</td></tr>\n";
        }

		//SURVEY URL PART
        /** front end functionality required
        if ( $modrewrite ) {
            $tmp_url = $GLOBALS['publicurl'] . '/' . $surveyinfo['sid'];
            $surveysummary .= "<td align='left'> <a href='$tmp_url/lang-".$surveyinfo['language']."' target='_blank'>$tmp_url/lang-".$surveyinfo['language']."</a>";
            foreach ($aAdditionalLanguages as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url/lang-$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
            }
        } else {
            $tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $surveyinfo['sid'];
            $surveysummary .= "<td align='left'> <a href='$tmp_url&amp;lang=".$surveyinfo['language']."' target='_blank'>$tmp_url&amp;lang=".$surveyinfo['language']."</a>";
            foreach ($aAdditionalLanguages as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url&amp;lang=$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
            }
        }
        */
        
        $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
        if (trim($surveyinfo['startdate'])!= '')
        {
            $constructoritems = array($surveyinfo['startdate'] , "Y-m-d H:i:s");
            $this->load->library('Date_Time_Converter',$constructoritems);
            $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($surveyinfo['startdate'] , "Y-m-d H:i:s");
            $data['startdate']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }
        else
        {
            $data['startdate']="-";
        }

        if (trim($surveyinfo['expires'])!= '')
        {
            $constructoritems = array($surveyinfo['expires'] , "Y-m-d H:i:s");
            $this->load->library('Date_Time_Converter',$constructoritems);
            $datetimeobj = $this->date_time_converter; 
            //$datetimeobj = new Date_Time_Converter($surveyinfo['expires'] , "Y-m-d H:i:s");
            $data['expdate']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }
        else
        {
            $data['expdate']="-";
        }

        if (!$surveyinfo['language']) {$data['language']=getLanguageNameFromCode($currentadminlang,false);} else {$data['language']=getLanguageNameFromCode($surveyinfo['language'],false);}

        // get the rowspan of the Additionnal languages row
        // is at least 1 even if no additionnal language is present
        $additionnalLanguagesCount = count($aAdditionalLanguages);
        $first=true;
		$data['additionnalLanguages']="";
        if ($additionnalLanguagesCount == 0)
        {
                    $data['additionnalLanguages'] .= "<td align='left'>-</td>\n";
        }
        else
        {
            foreach ($aAdditionalLanguages as $langname)
            {
                if ($langname)
                {
                    if (!$first) {$data['additionnalLanguages'].= "<tr><td>&nbsp;</td>";}
                    $first=false;
                    $data['additionnalLanguages'] .= "<td align='left'>".getLanguageNameFromCode($langname,false)."</td></tr>\n";
                }
            }
        }
        if ($first) $data['additionnalLanguages'] .= "</tr>";

        if ($surveyinfo['surveyls_urldescription']==""){$surveyinfo['surveyls_urldescription']=htmlspecialchars($surveyinfo['surveyls_url']);}
                                          
        if ($surveyinfo['surveyls_url']!="") 
        {
            $data['endurl'] = " <a target='_blank' href=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\" title=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\">{$surveyinfo['surveyls_urldescription']}</a>";
        }
        else
        {
            $data['endurl'] ="-";
        }

		$data['sumcount3']=$sumcount3;
		$data['sumcount2']=$sumcount2;

        if ($activated == "N")
        {
            $data['activatedlang'] = $clang->gT("No");
        }
        else
        {
            $data['activatedlang'] = $clang->gT("Yes");
        }

		$data['activated']=$activated;
        if ($activated == "Y")
        {
            $data['surveydb']=$this->db->dbprefix."survey_".$surveyid;
        }
 		$data['warnings']="";
        if ($activated == "N" && $sumcount3 == 0)
        {
            $data['warnings']= $clang->gT("Survey cannot be activated yet.")."<br />\n";
            if ($sumcount2 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                $data['warnings'] .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add question groups")."]</span><br />";
            }
            if ($sumcount3 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
               $data['warnings'] .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add questions")."]</span><br />";
            }
        }
        $data['hints']=$surveysummary2;

        //return (array('column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
        /*
        $tableusage = get_dbtableusage($surveyid);
        if ($tableusage != false){

            if ($tableusage['dbtype']=='mysql'){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                $size_usage =  round($tableusage['size'][0]/$tableusage['size'][1] * 100,2);


                $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Column Usage")}: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='{$column_usage}'></div> </td></tr>";
                $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Size Usage")}: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='{$size_usage}'></div></td></tr>";
            }
            elseif (($arrCols['dbtype'] == 'mssqlnative')||($arrCols['dbtype'] == 'postgres')||($arrCols['dbtype'] == 'odbtp')||($arrCols['dbtype'] == 'mssql_n')){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Column Usage")}: </strong></td><td><strong>{$column_usage}%</strong><div class='progressbar' style='width:20%; height:15px;' name='{$column_usage}'></div> </td></tr>";
            }
            
        }
        */
        //$gid || $qid || 
       
        
        if ($action=="deactivate"|| $action=="activate" || $action=="surveysecurity"
        || $action=="surveyrights" || $action=="addsurveysecurity" || $action=="addusergroupsurveysecurity"
        || $action=="setsurveysecurity" ||  $action=="setusergroupsurveysecurity" || $action=="delsurveysecurity"
        || $action=="editsurveysettings"|| $action=="editsurveylocalesettings" || $action=="updatesurveysettingsandeditlocalesettings" || $action=="addgroup" || $action=="importgroup"
        || $action=="ordergroups" || $action=="deletesurvey" || $action=="resetsurveylogic"
        || $action=="importsurveyresources" || $action=="translate"  || $action=="emailtemplates" 
        || $action=="exportstructure" || $action=="quotas" || $action=="copysurvey" || $action=="viewgroup" || $action == "viewquestion") {$showstyle="style='display: none'";}
        if (!isset($showstyle)) {$showstyle="";} 
        /**if ($gid) {$showstyle="style='display: none'";}
        if (!isset($showstyle)) {$showstyle="";} */
        $data['showstyle'] = $showstyle;
		$this->load->view("admin/survey/surveysummary",$data);
    }
}
