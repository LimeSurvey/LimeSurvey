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
 * Browse Controller
 *
 * This controller performs browse actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class browse extends Survey_Common_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	public function _remap($method, $params = array())
	{
		array_unshift($params, $method);
	    return call_user_func_array(array($this, "action"), $params);
	}

	function action($surveyid = null, $subaction = null, $var1 = null, $var2 = null, $var3 = null, $var4 = null)
	{
		$_POST = $this->input->post();
		$clang = $this->limesurvey_lang;
		$this->load->helper("database");
		$this->load->helper("surveytranslator");
		$data = array();

		if (!isset($limit)) {$limit=returnglobal('limit');}
		if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
		if (!isset($id)) {$id=returnglobal('id');}
		if (!isset($order)) {$order=returnglobal('order');}
		if (!isset($browselang)) {$browselang=returnglobal('browselang');}

		// Some test in response table
		if (!isset($surveyid) && !isset($subaction)) //NO SID OR ACTION PROVIDED
		{
		    show_error("\t<div class='messagebox ui-corner-all'><div class='header ui-widget-header'>"
		            . $clang->gT("Browse Responses")."</div><div class='warningheader'>"
		            .$clang->gT("Error")."\t</div>\n"
		            . $clang->gT("You have not selected a survey to browse.")."<br />\n"
		            ."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
		            ."</div>");
		    return;
		}

		$data['surveyid'] = $surveyid;
		$data['subaction'] = $subaction;

		//CHECK IF SURVEY IS ACTIVATED AND EXISTS
		$actquery = "SELECT * FROM ".$this->db->dbprefix('surveys')." as a inner join ".$this->db->dbprefix('surveys_languagesettings')." as b on (b.surveyls_survey_id=a.sid and b.surveyls_language=a.language) WHERE a.sid=".$this->db->escape($surveyid);

		$actresult = db_execute_assoc($actquery);
		$actcount = $actresult->num_rows();
		if ($actcount > 0)
		{
			foreach ($actresult->result_array() as $actrow)
		    {
		        $surveytable = $this->db->dbprefix("survey_".$actrow['sid']);
		        $surveytimingstable = $this->db->dbprefix("survey_".$actrow['sid']."_timings");
		        $tokentable = $this->db->dbprefix."tokens_".$actrow['sid'];
		        /*
		         * DO NEVER EVER PUT VARIABLES AND FUNCTIONS WHICH GIVE BACK DIFFERENT QUOTES
		         * IN DOUBLE QUOTED(' and " and \" is used) JAVASCRIPT/HTML CODE!!! (except for: you know what you are doing)
		         *
		         * Used for deleting a record, fix quote bugs..
		         */
		        $surveytableNq = $this->db->dbprefix("survey_".$surveyid);

		        $surveyname = "{$actrow['surveyls_title']}";
		        if ($actrow['active'] == "N") //SURVEY IS NOT ACTIVE YET
		        {
		            show_error("\t<div class='messagebox ui-corner-all'><div class='header ui-widget-header'>"
		                . $clang->gT("Browse Responses")."</div><div class='warningheader'>"
		                .$clang->gT("Error")."\t</div>\n"
		                . $clang->gT("This survey has not been activated. There are no results to browse.")."<br />\n"
		                ."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" /><br />\n"
		                ."</div>");
		            return;
		        }
		    }
		}
		else //SURVEY MATCHING $surveyid DOESN'T EXIST
		{
		    show_error("\t<div class='messagebox ui-corner-all'><div class='header ui-widget-header'>"
		        . $clang->gT("Browse Responses")."</div><div class='warningheader'>"
		        .$clang->gT("Error")."\t</div>\n"
		        . $clang->gT("There is no matching survey.")."<br />\n"
		        ."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
		        ."</div>");
		    return;
		}

		//OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

		$surveyinfo=getSurveyInfo($surveyid);
		//require_once(dirname(__FILE__).'/sessioncontrol.php');

		// Set language for questions and labels to base language of this survey

		if (isset($browselang) && $browselang!='')
		{
		    $_SESSION['browselang']=$browselang;
		    $language=$_SESSION['browselang'];
		}
		elseif (isset($_SESSION['browselang']))
		{
		    $language=$_SESSION['browselang'];
		    $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
		    $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
		    if (!in_array($language,$languagelist))
		    {
		        $language = GetBaseLanguageFromSurveyID($surveyid);
		    }
		}
		else
		{
		    $language = GetBaseLanguageFromSurveyID($surveyid);
		}

		self::_getAdminHeader();
		$surveyoptions = "";
		self::_browsemenubar($surveyid, $clang->gT("Browse Responses"));
		$browseoutput = "";

		self::_js_admin_includes($this->config->item("adminscripts").'browse.js');



		$qulanguage = GetBaseLanguageFromSurveyID($surveyid);


		// Looking at a SINGLE entry

		if ($subaction == "id")
		{
			$id=$var1;
		    //SHOW HEADER
		    if (!isset($_POST['sql']) || !$_POST['sql']) {$browseoutput .= $surveyoptions;} // Don't show options if coming from tokens script
		    //FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE

		    $fncount = 0;


		    $fieldmap=createFieldMap($surveyid,'full',false,false,$language);

		    //add token to top of list if survey is not private
		    if ($surveyinfo['anonymized'] == "N" && tableExists('tokens_'.$surveyid))
		    {
		        $fnames[] = array("token", "Token", $clang->gT("Token ID"), 0);
		        $fnames[] = array("firstname", "First name", $clang->gT("First name"), 0);
		        $fnames[] = array("lastname", "Last name", $clang->gT("Last name"), 0);
		        $fnames[] = array("email", "Email", $clang->gT("Email"), 0);
		    }
		    $fnames[] = array("submitdate", $clang->gT("Submission date"), $clang->gT("Completed"), "0", 'D');
		    $fnames[] = array("completed", $clang->gT("Completed"), "0");

		    foreach ($fieldmap as $field)
		    {
		        if ($field['fieldname']=='lastpage' || $field['fieldname'] == 'submitdate')
		            continue;
		        if ($field['type']=='interview_time')
		            continue;
		        if ($field['type']=='page_time')
		            continue;
		        if ($field['type']=='answer_time')
		            continue;

		        $question=$field['question'];
		        if ($field['type'] != "|")
		        {
		            if (isset($field['subquestion']) && $field['subquestion']!='')
		                $question .=' ('.$field['subquestion'].')';
		            if (isset($field['subquestion1']) && isset($field['subquestion2']))
		                $question .=' ('.$field['subquestion1'].':'.$field['subquestion2'].')';
		            if (isset($field['scale_id']))
		                $question .='['.$field['scale'].']';
		            $fnames[]=array($field['fieldname'],$question);
		        }
		        else
		        {
		            if ($field['aid']!=='filecount')
		            {
		                $qidattributes=getQuestionAttributeValues($field['qid']);

		                for ($i = 0; $i < $qidattributes['max_files']; $i++)
		                {
		                    if ($qidattributes['show_title'] == 1)
		                        $fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (Title)",     "type"=>"|", "metadata"=>"title",   "index"=>$i);

		                    if ($qidattributes['show_comment'] == 1)
		                        $fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (Comment)",   "type"=>"|", "metadata"=>"comment", "index"=>$i);

		                    $fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (File name)", "type"=>"|", "metadata"=>"name",    "index"=>$i);
		                    $fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (File size)", "type"=>"|", "metadata"=>"size",    "index"=>$i);
		                    //$fnames[] = array($field['fieldname'], "File ".($i+1)." - ".$field['question']." (extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
		                }
		            }
		            else
		                $fnames[] = array($field['fieldname'], "File count");
		        }
		}

		    $nfncount = count($fnames)-1;
		    //SHOW INDIVIDUAL RECORD
		    $idquery = "SELECT * FROM $surveytable ";
		    if ($surveyinfo['anonymized'] == "N" && db_tables_exist($tokentable))
		        $idquery .= "LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token ";
		    if (incompleteAnsFilterstate() == "inc")
		        $idquery .= " WHERE (submitdate = ".$connect->DBDate('1980-01-01'). " OR submitdate IS NULL) AND ";
		    elseif (incompleteAnsFilterstate() == "filter")
		        $idquery .= " WHERE submitdate >= ".$connect->DBDate('1980-01-01'). " AND ";
		    else
		        $idquery .= " WHERE ";
		    if ($id < 1) { $id = 1; }
		    if (isset($_POST['sql']) && $_POST['sql'])
		    {
		        if (get_magic_quotes_gpc()) {$idquery .= stripslashes($_POST['sql']);}
		        else {$idquery .= "{$_POST['sql']}";}
		    }
		    else {$idquery .= "$surveytable.id = $id";}
		    $idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />\n$idquery<br />\n".$connect->ErrorMsg());
		    foreach ($idresult->result_array() as $idrow)
		    {
		        $id=$idrow['id'];
		        $rlanguage=$idrow['startlanguage'];
		    }
		    $next=$id+1;
		    $last=$id-1;

			$data['id'] = $id;
			if (isset($rlanguage)) {$data['rlanguage'] = $rlanguage;}
			$data['next'] = $next;
			$data['last'] = $last;
			$this->load->view("admin/browse/browseidheader_view", $data);

		    $idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />$idquery<br />".$connect->ErrorMsg());
		    foreach ($idresult->result_array() as $idrow)
		    {
		        $highlight=false;
		        for ($i = 0; $i < $nfncount+1; $i++)
		        {
		            $inserthighlight='';
		            if ($highlight)
		                $inserthighlight="class='highlight'";

		            if ($fnames[$i][0] == 'completed')
		            {
		                if ($idrow['submitdate'] == NULL || $idrow['submitdate'] == "N") { $answervalue = "N"; }
		                else { $answervalue = "Y"; }
		            }
		            else
		            {
		                if (isset($fnames[$i]['type']) && $fnames[$i]['type'] == "|")
		                {
		                    $index = $fnames[$i]['index'];
		                    $metadata = $fnames[$i]['metadata'];
		                    $phparray = json_decode($idrow[$fnames[$i][0]], true);
		                    if (isset($phparray[$index]))
		                    {
		                        if ($metadata === "size")
		                            $answervalue = rawurldecode(((int)($phparray[$index][$metadata]))." KB");
		                        else if ($metadata === "name")
		                            $answervalue = "<a href='#' onclick=\" ".get2post('?action=browse&amp;subaction=all&amp;downloadindividualfile=' . $phparray[$index][$metadata] . '&amp;fieldname='.$fnames[$i][0].'&amp;id='.$id.'&amp;sid='.$surveyid)."\" >".rawurldecode($phparray[$index][$metadata])."</a>";
		                        else
		                            $answervalue = rawurldecode($phparray[$index][$metadata]);
		                    }
		                    else
		                        $answervalue = "";
		                }
		                else
		                    $answervalue = htmlspecialchars(strip_tags(strip_javascript(getextendedanswer($surveyid, "browse", $fnames[$i][0], $idrow[$fnames[$i][0]], ''))), ENT_QUOTES);
		            }
					$data['answervalue'] = $answervalue;
					$data['inserthighlight'] = $inserthighlight;
					$data['fnames'] = $fnames;
					$data['i'] = $i;
					$this->load->view("admin/browse/browseidrow_view", $data);
		            $highlight=!$highlight;
		        }
		    }
			$this->load->view("admin/browse/browseidfooter_view", $data);
		}

		elseif ($subaction == "all")
		{
		    if(isset($var3)) $order = $var3;

		    /**
		     * fnames is used as informational array
		     * it containts
		     *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);
		     */

		    if (!isset($_POST['sql']))
		    {$browseoutput .= $surveyoptions;} //don't show options when called from another script with a filter on
		    else
		    {
		        $browseoutput .= "\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Browse Responses").":</strong> $surveyname</td></tr>\n"
		                ."\n<tr><td><table width='100%' align='center' border='0' bgcolor='#EFEFEF'>\n"
		                ."\t<tr>\n"
		                ."<td align='center'>\n"
		                ."".$clang->gT("Showing Filtered Results")."<br />\n"
		                ."&nbsp;[<a href=\"javascript:window.close()\">".$clang->gT("Close")."</a>]"
		                ."</font></td>\n"
		                ."\t</tr>\n"
		                ."</table></td></tr>\n";

		    }

		    //Delete Individual answer using inrow delete buttons/links - checked
		    if (isset($_POST['deleteanswer']) && $_POST['deleteanswer'] != '' && $_POST['deleteanswer'] != 'marked' && bHasSurveyPermission($surveyid,'responses','delete'))
		    {
		        $_POST['deleteanswer']=(int) $_POST['deleteanswer']; // sanitize the value

		        // delete the files as well if its a fuqt

		        $fieldmap = createFieldMap($surveyid);
		        $fuqtquestions = array();
		        // find all fuqt questions
		        foreach ($fieldmap as $field)
		        {
		            if ($field['type'] == "|" && strpos($field['fieldname'], "_filecount") == 0)
		                $fuqtquestions[] = $field['fieldname'];
		        }

		        if (!empty($fuqtquestions))
		        {
		            // find all responses (filenames) to the fuqt questions
		            $query="SELECT " . implode(", ", $fuqtquestions) . " FROM $surveytable where id={$_POST['deleteanswer']}";
		            $responses = db_execute_assoc($query) or safe_die("Could not fetch responses<br />$query<br />".$connect->ErrorMsg());

		            foreach ($responses->result_array() as $json)
		            {
		                foreach ($fuqtquestions as $fieldname)
		                {
		                    $phparray = json_decode($json[$fieldname]);
		                    foreach($phparray as $metadata)
		                    {
		                        $path = $CI->config->item('uploaddir')."/surveys/".$surveyid."/files/";
		                        unlink($path.$metadata->filename); // delete the file
		                    }
		                }
		            }
		        }

		        // delete the row
		        $query="delete FROM $surveytable where id=".mysql_real_escape_string($_POST['deleteanswer']);
		        db_execute_assoc($query) or safe_die("Could not delete response<br />$dtquery<br />".$connect->ErrorMsg()); // checked
		    }
		    // Marked responses -> deal with the whole batch of marked responses
		    if (isset($_POST['markedresponses']) && count($_POST['markedresponses'])>0 && bHasSurveyPermission($surveyid,'responses','delete'))
		    {
		        // Delete the marked responses - checked
		        if (isset($_POST['deleteanswer']) && $_POST['deleteanswer'] === 'marked')
		        {
                    $fieldmap = createFieldMap($surveyid);
                    $fuqtquestions = array();
                    // find all fuqt questions
		            foreach ($fieldmap as $field)
		            {
                        if ($field['type'] == "|" && strpos($field['fieldname'], "_filecount") == 0)
                            $fuqtquestions[] = $field['fieldname'];
		                }

		            foreach ($_POST['markedresponses'] as $iResponseID)
		            {
		                $iResponseID=(int)$iResponseID; // sanitize the value

                        if (!empty($fuqtquestions))
		                {
                            // find all responses (filenames) to the fuqt questions
                            $query="SELECT " . implode(", ", $fuqtquestions) . " FROM $surveytable where id={$iResponseID}";
                            $responses = db_execute_assoc($query) or safe_die("Could not fetch responses<br />$query<br />".$connect->ErrorMsg());

                            foreach ($responses->result_array() as $json)
		                    {
                                foreach ($fuqtquestions as $fieldname)
		                        {
                                    $phparray = json_decode($json[$fieldname]);
                                    foreach($phparray as $metadata)
                                    {
                                        $path = $CI->config->item('uploaddir')."/surveys/{$surveyid}/files/";
                                        unlink($path.$metadata->filename); // delete the file
		                        }
		                    }
		                }
		            }

		                $query="delete FROM {$surveytable} where id={$iResponseID}";
		                db_execute_assoc($query) or safe_die("Could not delete response<br />{$dtquery}<br />".$connect->ErrorMsg());  // checked
		            }
		            }
		        // Download all files for all marked responses  - checked
		        else if (isset($_POST['downloadfile']) && $_POST['downloadfile'] === 'marked')
		        {
		            // Now, zip all the files in the filelist
                    $zipfilename = "Responses_for_survey_" . $surveyid . ".zip";
                    $this->zipFiles($_POST['markedresponses'], $zipfilename);
		        }
		    }
		    // Download all files for this entry - checked
		    else if (isset($_POST['downloadfile']) && $_POST['downloadfile'] != '' && $_POST['downloadfile'] !== true)
		    {
		        // Now, zip all the files in the filelist
		        $zipfilename = "LS_Responses_for_" . $_POST['downloadfile'] . ".zip";
                $this->zipFiles($_POST['downloadfile'], $zipfilename);
		        }
		    else if (isset($_POST['downloadindividualfile']) && $_POST['downloadindividualfile'] != '')
		    {
		        $id = (int)$_POST['id'];
		        $downloadindividualfile = $_POST['downloadindividualfile'];
		        $fieldname = $_POST['fieldname'];

		        $query = "SELECT ".db_quote_id($fieldname)." FROM {$surveytable} WHERE id={$id}";
		        $result=db_execute_assoc($query);
		        $row=$result->row_array();
		        $phparray = json_decode(reset($row));

		        for ($i = 0; $i < count($phparray); $i++)
		        {
		            if ($phparray[$i]->name == $downloadindividualfile)
		            {
		                $file = $CI->config->item('uploaddir')."/surveys/" . $surveyid . "/files/" . $phparray[$i]->filename;

		                if (file_exists($file)) {
		                    header('Content-Description: File Transfer');
		                    header('Content-Type: application/octet-stream');
		                    header('Content-Disposition: attachment; filename="' . rawurldecode($phparray[$i]->name) . '"');
		                    header('Content-Transfer-Encoding: binary');
		                    header('Expires: 0');
		                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		                    header('Pragma: public');
		                    header('Content-Length: ' . filesize($file));
		                    ob_clean();
		                    flush();
		                    readfile($file);
		                    exit;
		                }
		                break;
		            }
		        }
		    }


		    //add token to top of list if survey is not private
		    if ($surveyinfo['anonymized'] == "N" && db_tables_exist($tokentable)) //add token to top of list if survey is not private
		    {
		        $fnames[] = array("token", "Token", $clang->gT("Token ID"), 0);
		        $fnames[] = array("firstname", "First name", $clang->gT("First name"), 0);
		        $fnames[] = array("lastname", "Last name", $clang->gT("Last name"), 0);
		        $fnames[] = array("email", "Email", $clang->gT("Email"), 0);
		    }

		    $fnames[] = array("submitdate", $clang->gT("Completed"), $clang->gT("Completed"), "0", 'D');
		    $fields = createFieldMap($surveyid, 'full', false, false, $language);

		    $fnames[] = array("submitdate", "Completed", $clang->gT("Completed"), "0", 'D');
		    $fields = createFieldMap($surveyid, 'full', false, false, $language);

		    foreach ($fields as $fielddetails)
		    {
		        if ($fielddetails['fieldname']=='lastpage' || $fielddetails['fieldname'] == 'submitdate')
		            continue;

		        $question=$fielddetails['question'];
		        if ($fielddetails['type'] != "|")
		        {
		            if ($fielddetails['fieldname']=='lastpage' || $fielddetails['fieldname'] == 'submitdate' || $fielddetails['fieldname'] == 'token')
		                continue;

		            // no headers for time data
		            if ($fielddetails['type']=='interview_time')
					    continue;
		            if ($fielddetails['type']=='page_time')
					    continue;
				    if ($fielddetails['type']=='answer_time')
					    continue;
		            if (isset($fielddetails['subquestion']) && $fielddetails['subquestion']!='')
		                $question .=' ('.$fielddetails['subquestion'].')';
		            if (isset($fielddetails['subquestion1']) && isset($fielddetails['subquestion2']))
		                $question .=' ('.$fielddetails['subquestion1'].':'.$fielddetails['subquestion2'].')';
		            if (isset($fielddetails['scale_id']))
		            $question .='['.$fielddetails['scale'].']';
		            $fnames[]=array($fielddetails['fieldname'],$question);
		        }
		        else
		        {
		            if ($fielddetails['aid']!=='filecount')
		            {
		                $qidattributes=getQuestionAttributeValues($fielddetails['qid']);

		                for ($i = 0; $i < $qidattributes['max_files']; $i++)
		                {
		                    if ($qidattributes['show_title'] == 1)
		                        $fnames[] = array($fielddetails['fieldname'], "File ".($i+1)." - ".$fielddetails['question']."(Title)",     "type"=>"|", "metadata"=>"title",   "index"=>$i);

		                    if ($qidattributes['show_comment'] == 1)
		                        $fnames[] = array($fielddetails['fieldname'], "File ".($i+1)." - ".$fielddetails['question']."(Comment)",   "type"=>"|", "metadata"=>"comment", "index"=>$i);

		                    $fnames[] = array($fielddetails['fieldname'], "File ".($i+1)." - ".$fielddetails['question']."(File name)", "type"=>"|", "metadata"=>"name",    "index"=>$i);
		                    $fnames[] = array($fielddetails['fieldname'], "File ".($i+1)." - ".$fielddetails['question']."(File size)", "type"=>"|", "metadata"=>"size",    "index"=>$i);
		                    //$fnames[] = array($fielddetails['fieldname'], "File ".($i+1)." - ".$fielddetails['question']."(extension)", "type"=>"|", "metadata"=>"ext",     "index"=>$i);
		                }
		            }
		            else
		                $fnames[] = array($fielddetails['fieldname'], "File count");
		        }
		    }

		    $fncount = count($fnames);

		    //NOW LETS CREATE A TABLE WITH THOSE HEADINGS

		    $tableheader = "<!-- DATA TABLE -->";
		    if ($fncount < 10) {$tableheader .= "<table class='browsetable' width='100%'>\n";}
		    else {$tableheader .= "<table class='browsetable'>\n";}
		    $tableheader .= "\t<thead><tr valign='top'>\n"
		            . "<th><input type='checkbox' id='selectall'></th>\n"
		            . "<th>".$clang->gT('Actions')."</th>\n";
		    foreach ($fnames as $fn)
		    {
		        if (!isset($currentgroup))  {$currentgroup = $fn[1]; $gbc = "odd";}
		        if ($currentgroup != $fn[1])
		        {
		            $currentgroup = $fn[1];
		            if ($gbc == "odd") {$gbc = "even";}
		            else {$gbc = "odd";}
		            }
		        $tableheader .= "<th class='$gbc'><strong>"
		                . FlattenText("$fn[1]")
		                . "</strong></th>\n";
		    }
		    $tableheader .= "\t</tr></thead>\n\n";
		    $tableheader .= "\t<tfoot><tr><td colspan=".($fncount+2).">";
		    if (bHasSurveyPermission($surveyid,'responses','delete'))
		    {
		        $tableheader .= "<img id='imgDeleteMarkedResponses' src='".$this->config->item("imageurl")."/token_delete.png' alt='".$clang->gT('Delete marked responses')."' />";
		    }
		    if (bHasFileUploadQuestion($surveyid))
		    {
		        $tableheader .="<img id='imgDownloadMarkedFiles' src='".$this->config->item("imageurl")."/down_all.png' alt='".$clang->gT('Download marked files')."' />";
		    }
		    $tableheader .="</td></tr></tfoot>\n\n";

		    if(isset($var1)) $start = (int) $var1; else $start=returnglobal('start');
		    if(isset($var2)) $limit = (int) $var2; else $limit=returnglobal('limit');
		    if (!isset($limit) || $limit== '') {$limit = 50;}
		    if (!isset($start) || $start =='') {$start = 0;}

		    //Create the query
		    if ($surveyinfo['anonymized'] == "N" && db_tables_exist($tokentable))
		    {
		        $sql_from = "{$surveytable} LEFT JOIN {$tokentable} ON {$surveytable}.token = {$tokentable}.token";
		    } else {
		        $sql_from = $surveytable;
		    }

		    $selectedgroup = returnglobal('selectgroup'); // group token id

		    $sql_where = "";
		    if (incompleteAnsFilterstate() == "inc")
		    {
		        $sql_where .= "submitdate IS NULL";

		    }
		    elseif (incompleteAnsFilterstate() == "filter")
		    {
		        $sql_where .= "submitdate IS NOT NULL";

		    }

		    //LETS COUNT THE DATA
		    $dtquery = "SELECT count(*) FROM $sql_from";
		    if ($sql_where!="")
		    {
		        $dtquery .=" WHERE $sql_where";
		    }

		    $dtresult=db_execute_assoc($dtquery) or safe_die("Couldn't get response data<br />$dtquery<br />".$connect->ErrorMsg());
		    $dtrow=$dtresult->row_array();
			$dtcount=reset($dtrow);

		    if ($limit > $dtcount) {$limit=$dtcount;}

		    //NOW LETS SHOW THE DATA
		    if (isset($_POST['sql']))
		    {
		        if ($_POST['sql'] == "NULL" )
		        {
		            if ($surveyinfo['anonymized'] == "N" && db_tables_exist($tokentable))
		                $dtquery = "SELECT * FROM $surveytable LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token ";
		            else
		                $dtquery = "SELECT * FROM $surveytable ";
		            // group token id
		            $selectedgroup = returnglobal('selectgroup');
		            if (incompleteAnsFilterstate() == "inc")
		            {
		                $dtquery .= "WHERE submitdate IS NULL ";
		            }
		            elseif (incompleteAnsFilterstate() == "filter")
		            {
		                $dtquery .= " WHERE submitdate IS NOT NULL ";
		            }

		            $dtquery .= " ORDER BY {$surveytable}.id";
		        }
		        else
		        {
		            if ($surveytable['anonymized'] == "N" && db_tables_exist($tokentable))
		                $dtquery = "SELECT * FROM $surveytable LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token where 1=1 ";
		            else
		                $dtquery = "SELECT * FROM $surveytable where 1=1 ";
		            $selectedgroup = returnglobal('selectgroup');
		            if (incompleteAnsFilterstate() == "inc")
		            {
		                $dtquery .= " AND submitdate IS NULL ";
		                }
		            elseif (incompleteAnsFilterstate() == "filter")
		            {
		                $dtquery .= " AND submitdate IS NOT NULL ";
		                }
		            if (stripcslashes($_POST['sql']) !== "")
		            {
		                $dtquery .=  ' AND '. stripcslashes($_POST['sql'])." ";
		            }
		            $dtquery .= " ORDER BY {$surveytable}.id";
		        }
		    }
		    else
		    {
		        if ($surveyinfo['anonymized'] == "N" && db_tables_exist($tokentable))
		            $dtquery = "SELECT * FROM $surveytable LEFT JOIN $tokentable ON $surveytable.token = $tokentable.token ";
		        else
		            $dtquery = "SELECT * FROM $surveytable ";
		        if (incompleteAnsFilterstate() == "inc")
		        {
		            $dtquery .= " WHERE submitdate IS NULL ";

		        }
		        elseif (incompleteAnsFilterstate() == "filter")
		        {
		            $dtquery .= " WHERE submitdate IS NOT NULL ";
		        }

		        $dtquery .= " ORDER BY {$surveytable}.id";
		    }
		    if ($order == "desc") {$dtquery .= " DESC";}

		    if (isset($limit))
		    {
		        if (!isset($start)) {$start = 0;}
		        $dtresult = db_select_limit_assoc($dtquery, $limit, $start) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
		    }
		    else
		    {
		        $dtresult = db_execute_assoc($dtquery) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
		    }
		    $dtcount2 = $dtresult->num_rows();
		    $cells = $fncount+1;


		    //CONTROL MENUBAR
		    $last=$start-$limit;
		    $next=$start+$limit;
		    $end=$dtcount-$limit;
		    if ($end < 0) {$end=0;}
		    if ($last <0) {$last=0;}
		    if ($next >= $dtcount) {$next=$dtcount-$limit;}
		    if ($end < 0) {$end=0;}

			$data['dtcount2'] = $dtcount2;
			$data['start'] = $start;
			$data['tableheader'] = $tableheader;
			$data['limit'] = $limit;
			$data['last'] = $last;
			$data['next'] = $next;
			$data['end'] = $end;
			$this->load->view("admin/browse/browseallheader_view", $data);

		    foreach ($dtresult->result_array() as $dtrow)
		    {
				if (!isset($bgcc)) {$bgcc="even";}
				else
				{
				    if ($bgcc == "even") {$bgcc = "odd";}
				    else {$bgcc = "even";}
				}
				$data['bgcc'] = $bgcc;
				$data['dtrow'] = $dtrow;
				$data['surveyinfo'] = $surveyinfo;
				$data['fncount'] = $fncount;
				$data['fnames'] = $fnames;
				$this->load->view("admin/browse/browseallrow_view", $data);
		    }
		    $this->load->view("admin/browse/browseallfooter_view", $data);
		}
		elseif ($surveyinfo['savetimings']=="Y" && $subaction == "time"){
			$browseoutput .= $surveyoptions;
			$browseoutput .= '<div class="header ui-widget-header">'.$clang->gT('Time statistics').'</div>';

			// table of time statistics - only display completed surveys
		    $browseoutput .= "\n<script type='text/javascript'>
		                          var strdeleteconfirm='".$clang->gT('Do you really want to delete this response?','js')."';
		                          var strDeleteAllConfirm='".$clang->gT('Do you really want to delete all marked responses?','js')."';
		                        </script>\n";

		    if (isset($_POST['deleteanswer']) && $_POST['deleteanswer']!='')
		    {
		        $_POST['deleteanswer']=(int) $_POST['deleteanswer']; // sanitize the value
		        $query="delete FROM $surveytable where id={$_POST['deleteanswer']}";
		        db_execute_assoc($query) or safe_die("Could not delete response<br />$dtquery<br />".$connect->ErrorMsg()); // checked
		    }

		    if (isset($_POST['markedresponses']) && count($_POST['markedresponses'])>0)
		    {
		        foreach ($_POST['markedresponses'] as $iResponseID)
		        {
		            $iResponseID=(int)$iResponseID; // sanitize the value
		            $query="delete FROM $surveytable where id={$iResponseID}";
		            db_execute_assoc($query) or safe_die("Could not delete response<br />$dtquery<br />".$connect->ErrorMsg());  // checked
		        }
		    }

		    $fields=createTimingsFieldMap($surveyid,'full');

		    foreach ($fields as $fielddetails)
		    {
		        // headers for answer id and time data
				if ($fielddetails['type']=='id')
					$fnames[]=array($fielddetails['fieldname'],$fielddetails['question']);
		        if ($fielddetails['type']=='interview_time')
					$fnames[]=array($fielddetails['fieldname'],$clang->gT('Total time'));
		        if ($fielddetails['type']=='page_time')
					$fnames[]=array($fielddetails['fieldname'],$clang->gT('Group').": ".$fielddetails['group_name']);
				if ($fielddetails['type']=='answer_time')
					$fnames[]=array($fielddetails['fieldname'],$clang->gT('Question').": ".$fielddetails['title']);
		    }
		    $fncount = count($fnames);

		    //NOW LETS CREATE A TABLE WITH THOSE HEADINGS
		    $tableheader = "<!-- DATA TABLE -->";
		    if ($fncount < 10) {$tableheader .= "<table class='browsetable' width='100%'>\n";}
		    else {$tableheader .= "<table class='browsetable'>\n";}
		    $tableheader .= "\t<thead><tr valign='top'>\n"
		            . "<th><input type='checkbox' id='selectall'></th>\n"
		            . "<th>".$clang->gT('Actions')."</th>\n";
		    foreach ($fnames as $fn)
		    {
		        if (!isset($currentgroup))  {$currentgroup = $fn[1]; $gbc = "oddrow";}
		        if ($currentgroup != $fn[1])
		        {
		            $currentgroup = $fn[1];
		            if ($gbc == "oddrow") {$gbc = "evenrow";}
		            else {$gbc = "oddrow";}
		            }
		        $tableheader .= "<th class='$gbc'><strong>"
		                . strip_javascript("$fn[1]")
		                . "</strong></th>\n";
		    }
		    $tableheader .= "\t</tr></thead>\n\n";
		    $tableheader .= "\t<tfoot><tr><td colspan=".($fncount+2).">"
		                   ."<img id='imgDeleteMarkedResponses' src='$imageurl/token_delete.png' alt='".$clang->gT('Delete marked responses')."' />"
		                   ."\t</tr></tfoot>\n\n";

		    $start=returnglobal('start');
		    $limit=returnglobal('limit');
		    if (!isset($limit) || $limit== '') {$limit = 50;}
		    if (!isset($start) || $start =='') {$start = 0;}

		    //LETS COUNT THE DATA
		    $dtquery = "SELECT count(tid) FROM {$surveytimingstable} INNER JOIN {$surveytable} ON {$surveytimingstable}.id={$surveytable}.id WHERE submitdate IS NOT NULL ";

		    $dtresult=db_execute_assoc($dtquery) or safe_die("Couldn't get response data<br />$dtquery<br />".$connect->ErrorMsg());
		    $dtrow=$dtresult->row_array();
			$dtcount=reset($dtrow);

		    if ($limit > $dtcount) {$limit=$dtcount;}

		    //NOW LETS SHOW THE DATA
		    $dtquery = "SELECT t.* FROM {$surveytimingstable} t INNER JOIN {$surveytable} ON t.id={$surveytable}.id WHERE submitdate IS NOT NULL ORDER BY {$surveytable}.id";

		    if ($order == "desc") {$dtquery .= " DESC";}

		    if (isset($limit))
		    {
		        if (!isset($start)) {$start = 0;}
		        $dtresult = db_select_limit_assoc($dtquery, $limit, $start) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
		    }
		else
		{
		        $dtresult = db_execute_assoc($dtquery) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
		    }
		    $dtcount2 = $dtresult->num_rows();
		    $cells = $fncount+1;

		    //CONTROL MENUBAR
		    $last=$start-$limit;
		    $next=$start+$limit;
		    $end=$dtcount-$limit;
		    if ($end < 0) {$end=0;}
		    if ($last <0) {$last=0;}
		    if ($next >= $dtcount) {$next=$dtcount-$limit;}
		    if ($end < 0) {$end=0;}

		    $browseoutput .= "<div class='menubar'>\n"
		            . "\t<div class='menubar-title ui-widget-header'>\n"
		            . "<strong>".$clang->gT("Data view control")."</strong></div>\n"
		            . "\t<div class='menubar-main'>\n";
		    if (!isset($_POST['sql']))
		    {
		        $browseoutput .= "<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=0&amp;limit=$limit' "
		                ."title='".$clang->gTview("Show start...")."' >"
		                ."<img name='DataBegin' align='left' src='$imageurl/databegin.png' alt='".$clang->gT("Show start...")."' /></a>\n"
		                ."<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit' "
		                ."title='".$clang->gTview("Show previous..")."' >"
		                ."<img name='DataBack' align='left'  src='$imageurl/databack.png' alt='".$clang->gT("Show previous..")."' /></a>\n"
		                ."<img src='$imageurl/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />\n"

		                ."<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit' " .
		                "title='".$clang->gT("Show next...")."' >".
		                "<img name='DataForward' align='left' src='$imageurl/dataforward.png' alt='".$clang->gT("Show next..")."' /></a>\n"
		                ."<a href='$scriptname?action=browse&amp;subaction=time&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit' " .
		                "title='".$clang->gT("Show last...")."' >" .
		                "<img name='DataEnd' align='left' src='$imageurl/dataend.png' alt='".$clang->gT("Show last..")."' /></a>\n"
		                ."<img src='$imageurl/seperator.gif' border='0' hspace='0' align='left' alt='' />\n";
		    }
		    $selectshow='';
		    $selectinc='';
		    $selecthide='';

		    if(incompleteAnsFilterstate() == "inc") { $selectinc="selected='selected'"; }
		    elseif (incompleteAnsFilterstate() == "filter") { $selecthide="selected='selected'"; }
		    else { $selectshow="selected='selected'"; }

		    $browseoutput .="<form action='$scriptname?action=browse' id='browseresults' method='post'><font size='1' face='verdana'>\n"
		            ."<img src='$imageurl/blank.gif' width='31' height='20' border='0' hspace='0' align='right' alt='' />\n"
		            ."".$clang->gT("Records displayed:")."<input type='text' size='4' value='$dtcount2' name='limit' id='limit' />\n"
		            ."&nbsp;&nbsp; ".$clang->gT("Starting from:")."<input type='text' size='4' value='$start' name='start' id='start' />\n"
		            ."&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Show")."' />\n"
		            ."</font>\n"
		            ."<input type='hidden' name='sid' value='$surveyid' />\n"
		            ."<input type='hidden' name='action' value='browse' />\n"
		            ."<input type='hidden' name='subaction' value='time' />\n";
		    if (isset($_POST['sql']))
		    {
		        $browseoutput .= "<input type='hidden' name='sql' value='".html_escape($_POST['sql'])."' />\n";
		    }
		    $browseoutput .= 	 "</form></div>\n"
		            ."\t</div><form action='$scriptname?action=browse' id='resulttableform' method='post'>\n";

		    $browseoutput .= $tableheader;

		    foreach($dtresult->result_array() as $dtrow)
		    {
		        if (!isset($bgcc)) {$bgcc="evenrow";}
		        else
		        {
		            if ($bgcc == "evenrow") {$bgcc = "oddrow";}
		            else {$bgcc = "evenrow";}
		            }
		        $browseoutput .= "\t<tr class='$bgcc' valign='top'>\n"
		                ."<td align='center'><input type='checkbox' class='cbResponseMarker' value='{$dtrow['id']}' name='markedresponses[]' /></td>\n"
		                ."<td align='center'>
		        <a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id={$dtrow['id']}'><img src='$imageurl/token_viewanswer.png' alt='".$clang->gT('View response details')."'/></a>
		        <a href='$scriptname?action=dataentry&amp;sid=$surveyid&amp;subaction=edit&amp;id={$dtrow['id']}'><img src='$imageurl/token_edit.png' alt='".$clang->gT('Edit this response')."'/></a>
		        <a><img id='deleteresponse_{$dtrow['id']}' src='$imageurl/token_delete.png' alt='".$clang->gT('Delete this response')."' class='deleteresponse'/></a></td>\n";

		        for ($i = 0; $i<$fncount; $i++)
		        {
		            $browsedatafield=htmlspecialchars($dtrow[$fnames[$i][0]]);

		            // seconds -> minutes & seconds
					if (strtolower(substr($fnames[$i][0],-4)) == "time")
					{
		                $minutes = (int)($browsedatafield/60);
		                $seconds = $browsedatafield%60;
		                $browsedatafield = '';
		                if ($minutes > 0)
						    $browsedatafield .= "$minutes min ";
		                $browsedatafield .= "$seconds s";
		            }
		            $browseoutput .= "<td align='center'>$browsedatafield</td>\n";
		        }
		        $browseoutput .= "\t</tr>\n";
		    }
		    $browseoutput .= "</table>
		    <input type='hidden' name='sid' value='$surveyid' />
		    <input type='hidden' name='subaction' value='time' />
		    <input id='deleteanswer' name='deleteanswer' value='' type='hidden' />
		    </form>\n<br />\n";

			// Interview time
			$browseoutput .= '<div class="header ui-widget-header">'.$clang->gT('Interview time').'</div>';

			//interview Time statistics
			$count=false;
			//$survstats=substr($surveytableNq);
			$queryAvg="SELECT AVG(timings.interviewtime) AS avg, COUNT(timings.id) AS count FROM {$surveytableNq}_timings AS timings JOIN {$surveytable} AS surv ON timings.id=surv.id WHERE surv.submitdate IS NOT NULL";
			$queryAll="SELECT timings.interviewtime FROM {$surveytableNq}_timings AS timings JOIN {$surveytable} AS surv ON timings.id=surv.id WHERE surv.submitdate IS NOT NULL ORDER BY timings.interviewtime";
			$browseoutput .= '<table class="statisticssummary">';
			if($result=db_execute_assoc($queryAvg)){

				$row=$result->row_array();
				$min = (int)($row['avg']/60);
				$sec = $row['avg']%60;
				$count=$row['count'];
				$browseoutput .= '<tr><Th>'.$clang->gT('Average interview time: ')."</th><td>{$min} min. {$sec} sec.</td></tr>";
			}

			if($count && $result=db_execute_assoc($queryAll)){

				$middleval = floor(($count-1)/2);
				$i=0;
				if($count%2){
					foreach($result->result_array() as $row){

						if($i==$middleval){
							$median=$row['interviewtime'];
							break;
						}
						$i++;
					}
				}else{
					foreach($result->result_array() as $row){
						if($i==$middleval){
							$nextrow=$result->row_array();
							$median=($row['interviewtime']+$nextrow['interviewtime'])/2;
							break;
						}
						$i++;
					}
				}
				$min = (int)($median/60);
				$sec = $median%60;
				$browseoutput.='<tr><Th>'.$clang->gT('Median: ')."</th><td>{$min} min. {$sec} sec.</td></tr>";
			}
			$browseoutput .= '</table>';
		}
		elseif ($subaction=="time")
		{
		    $browseoutput .= $surveyoptions;
		    $browseoutput .= "<div class='header ui-widget-header'>".$clang->gT("Timings")."</div>";
			$browseoutput .= "Timing saving is disabled or the timing table does not exist. Try to reactivate survey.\n";
		}
		else
		{
		    $browseoutput .= $surveyoptions;
		    $num_total_answers=0;
		    $num_completed_answers=0;
		    $gnquery = "SELECT count(id) FROM $surveytable";
		    $gnquery2 = "SELECT count(id) FROM $surveytable WHERE submitdate IS NOT NULL";
		    $gnresult = db_execute_assoc($gnquery);
		    $gnresult2 = db_execute_assoc($gnquery2);

			$gnrow=$gnresult->row_array();
			$data['num_total_answers']=reset($gnrow);
			$gnrow2=$gnresult2->row_array();
			$data['num_completed_answers']=reset($gnrow2);
			$this->load->view("admin/browse/browseindex_view", $data);
		}

		echo $browseoutput;
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

	}


    /**
     * Supply an array with the responseIds and all files will be added to the zip
     * and it will be be spit out on success
     *
     * @param array $responseIds
     * @return ZipArchive
     */
    function zipFiles($responseIds, $zipfilename) {
        global $surveyid, $surveytable;

        $tmpdir = $CI->config->item('uploaddir'). "/surveys/" . $surveyid . "/files/";

        $filelist = array();
        $fieldmap = createFieldMap($surveyid, 'full');

        foreach ($fieldmap as $field)
        {
            if ($field['type'] == "|" && $field['aid']!=='filecount')
            {
                $filequestion[] = $field['fieldname'];
            }
        }

        $initquery = "SELECT " . implode(', ', $filequestion);

        foreach ((array)$responseIds as $responseId)
        {
            $responseId=(int)$responseId; // sanitize the value

            $query = $initquery . " FROM $surveytable WHERE id=$responseId";
            $filearray = db_execute_assoc($query) or safe_die("Could not download response<br />$query<br />".$connect->ErrorMsg());
            $metadata = array();
            $filecount = 0;
            while ($metadata = $filearray->FetchRow())
            {
                foreach ($metadata as $data)
                {
                    $phparray = json_decode($data, true);
                    if (is_array($phparray)) {
                        foreach($phparray as $file) {
                            $filecount++;
                            $file['responseid'] = $responseId;
                            $file['name'] = rawurldecode($file['name']);
                            $file['index'] = $filecount;
                            /*
                             * Now add the file to the archive, prefix files with responseid_index to keep them
                             * unique. This way we can have 234_1_image1.gif, 234_2_image1.gif as it could be
                             * files from a different source with the same name.
                             */
                            $filelist[] = array(PCLZIP_ATT_FILE_NAME          =>$tmpdir . $file['filename'],
                                                PCLZIP_ATT_FILE_NEW_FULL_NAME =>sprintf("%05s_%02s_%s", $file['responseid'], $file['index'], $file['name']));
                        }
                    }
                }
            }
        }

        if (count($filelist)>0) {
            $this->load->library("admin/pclzip/pclzip",array('p_zipname' => $tempdir.$zipfilename));
            $zip = new PclZip($tmpdir . $zipfilename);
            if ($zip->create($filelist)===0) {
                //Oops something has gone wrong!
            }

            if (file_exists($tmpdir."/".$zipfilename)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($zipfilename));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tmpdir."/".$zipfilename));
                ob_clean();
                flush();
                readfile($tmpdir . "/" . $zipfilename);
                unlink($tmpdir . "/" . $zipfilename);
                exit;
            }
        }
    }

}