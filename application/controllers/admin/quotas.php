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
 * $Id: quotas.php 11128 2011-10-08 22:23:24Z dionet $
 *
 */

/**
 * Quotas Controller
 *
 * This controller performs quota actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class quotas extends Survey_Common_Action {

	/**
	 * Base function
	 *
	 * @access publlic
	 * @param int $surveyid
	 * @param string $subaction
	 * @return void
	 */
	public function run($surveyid, $subaction = null)
	{

		$surveyid = sanitize_int($surveyid);

		$this->getController()->_js_admin_includes(Yii::app()->getConfig("generalscripts").'/jquery/jquery.tablesorter.min.js');
		$this->getController()->_js_admin_includes(Yii::app()->getConfig("adminscripts").'/quotas.js');

		if(!bHasSurveyPermission($surveyid, 'quotas','read'))
		{
			show_error("no permissions");
		}

		$clang = $this->getController()->lang;
		$conn = Yii::app()->db;

		Yii::app()->loadHelper('surveytranslator');

		$data = array('clang' => $clang, 'surveyid' => $surveyid);

	    if (isset($_POST['quotamax'])) $_POST['quotamax']=sanitize_int($_POST['quotamax']);
	    if (!isset($action)) $action=returnglobal('action');
	    if (!isset($action)) $action="quotas";
	    if (!isset($subaction)) $subaction=returnglobal('subaction');
	    //if (!isset($quotasoutput)) $quotasoutput = "";
	    if (!isset($_POST['autoload_url']) || empty($_POST['autoload_url'])) {$_POST['autoload_url']=0;}

		//Get the languages used in this survey
        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
		$data['baselang'] = $baselang;
        array_push($langs, $baselang);

		$css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
		Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
		$this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu();
        $this->_surveybar($surveyid);

	    if($subaction == "insertquota" && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
	  		if(!isset($_POST['quota_limit']) || $_POST['quota_limit'] < 0 || empty($_POST['quota_limit']) || !is_numeric($_POST['quota_limit']))
	        {
	            $_POST['quota_limit'] = 0;

	        }

	    	$comm = $conn->createCommand("INSERT INTO {{quota}} (sid,name,qlimit,action,autoload_url)
			          VALUES (:surveyid, :quota_name, :quota_limit, :quota_action, :autoload_url)");
	    	$comm->execute(array(
	    		':surveyid' => $surveyid,
	    		':quota_name' => $_POST['quota_name'],
	    		':quota_limit' => $_POST['quota_limit'],
	    		':quota_action' => $_POST['quota_action'],
	    		':autoload_url' => $_POST['autoload_url'],
	    	));

	        $quotaid = $conn->lastInsertID;//$connect->Insert_Id($this->db->dbprefix_nq('quota'),"id");

	        //Get the languages used in this survey
	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs, $baselang);
	        //Iterate through each language, and make sure there is a quota message for it
	        $errorstring = '';
	        foreach ($langs as $lang)
	        {
	            if (!$_POST['quotals_message_'.$lang]) { $errorstring.= GetLanguageNameFromCode($lang,false)."\\n";}
	        }
	        if ($errorstring!='')
	        {
	            $data['showerror'] = "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Quota could not be added.\\n\\nIt is missing a quota message for the following languages","js").":\\n".$errorstring."\")\n //-->\n</script>\n";
	        }
	        else
	        //All the required quota messages exist, now we can insert this info into the database
	        {

	            foreach ($langs as $lang) //Iterate through each language
	            {
	                //Clean XSS - Automatically provided by CI input class
	                $_POST['quotals_message_'.$lang] = html_entity_decode($_POST['quotals_message_'.$lang], ENT_QUOTES, "UTF-8");

	                // Fix bug with FCKEditor saving strange BR types
	                $_POST['quotals_message_'.$lang]=fix_FCKeditor_text($_POST['quotals_message_'.$lang]);

	            	$comm = $conn->createCommand("
						INSERT INTO {{quota_languagesettings}} (quotals_quota_id, quotals_language, quotals_name, quotals_message, quotals_url, quotals_urldescrip)
	            	VALUES (:quotaid, :lang, :quota_name, :quotal_message, :quotal_url, :quotal_urldesc)");
	            	$comm->execute(array(
	            		':quotaid' => $quotaid,
	            		':lang' => $lang,
	            		':quota_name' => $_POST['quota_name'],
	            		':quotal_message' => $_POST['quotals_message_' . $lang],
	            		':quotal_url' => $_POST['quotals_url_' . $lang],
	            		':quotal_urldesc' => $_POST['quotals_urldescrip_' . $lang],
	            	));
	            }
	        } //End insert language based components
	        $viewquota = "1";

	    } //End foreach $lang

	    if($subaction == "modifyquota" && bHasSurveyPermission($surveyid, 'quotas','update'))
	    {
	    	$query = "
				UPDATE {{quota}}
				SET name=:name,
					qlimit=:limit,
					action=:action,
					autoload_url=:autoload_url
				WHERE id=:id";
	    	$conn->createCommand($query)->execute(array(
	    		':name' => $_POST['quota_name'],
	    		':limit' => $_POST['quota_limit'],
	    		':action' => $_POST['quota_action'],
	    		':autoload_url' => $_POST['autoload_url'],
	    		':id' => $_POST['quota_id'],
	    	));

	        //Get the languages used in this survey
	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs, $baselang);
	        //Iterate through each language, and make sure there is a quota message for it
	        $errorstring = '';
	        foreach ($langs as $lang)
	        {
	            if (!$_POST['quotals_message_'.$lang]) { $errorstring.= GetLanguageNameFromCode($lang,false)."\\n";}
	        }
	        if ($errorstring!='')
	        {
	            $data['showerror'] = "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Quota could not be added.\\n\\nIt is missing a quota message for the following languages","js").":\\n".$errorstring."\")\n //-->\n</script>\n";
	        }
	        else
	        //All the required quota messages exist, now we can insert this info into the database
	        {

	            foreach ($langs as $lang) //Iterate through each language
	            {
	                //Clean XSS - Automatically provided by CI
	                $_POST['quotals_message_'.$lang] = html_entity_decode($_POST['quotals_message_'.$lang], ENT_QUOTES, "UTF-8");

	                // Fix bug with FCKEditor saving strange BR types
	                $_POST['quotals_message_'.$lang]=fix_FCKeditor_text($_POST['quotals_message_'.$lang]);

	            	$query = "
						UPDATE {{quota_languagesettings}}
						SET quotals_name=:name,
							quotals_message=:message,
							quotals_url=:url,
							quotals_urldescrip=:desc
						WHERE
							quotals_quota_id=:id
							AND quotals_language=:lang";
	            	$conn->createCommand($query)->execute(array(
	            		':name' => $_POST['quota_name'],
	            		':message' => $_POST['quotals_message_' . $lang],
	            		':url' => $_POST['quotals_url_' . $lang],
	            		':desc' => $_POST['quotals_urldescrip_' . $lang],
	            		':id' => $_POST['quota_id'],
	            		':lang' => $lang,
	            	));
	            }
	        } //End insert language based components


	        $viewquota = "1";
	    }

	    if($subaction == "insertquotaanswer" && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
			$query = "INSERT INTO {{quota_members}} (sid, qid, quota_id, code)
					  VALUES (:survey_id, :quota_qid, :quota_id, :quota_anscode)";
	    	$conn->createCommand($query)->execute(array(
	    		':survey_id' => $surveyid,
	    		':quota_qid' => $_POST['quota_qid'],
	    		':quota_id' => $_POST['quota_id'],
	    		':quota_anscode' => $_POST['quota_anscode'],
	    	));

			if(isset($_POST['createanother']) && $_POST['createanother'] == "on") {
				$_POST['action']="quotas";
				$_POST['subaction']="new_answer";
				$subaction="new_answer";
			} else {
				$viewquota = "1";
			}
	    }

	    if($subaction == "quota_delans" && bHasSurveyPermission($surveyid, 'quotas','delete'))
	    {
			$query = "DELETE FROM {{quota_members}} WHERE id = :quota_member_id AND qid = :quota_qid AND code = :quota_anscode";
	    	$conn->createCommand($query)->execute(array(
	    		':quota_member_id' => $_POST['quota_member_id'],
	    		':quota_qid' => $_POST['quota_qid'],
	    		':quota_anscode' => $_POST['quota_anscode'],
	    	));
	        $viewquota = "1";

	    }

	    if($subaction == "quota_delquota" && bHasSurveyPermission($surveyid, 'quotas','delete'))
	    {
	    	$query = "DELETE FROM {{quota}} WHERE id=:quota_id";
	    	$conn->createCommand($query)->execute(array(':quota_id' => $_POST['quota_id']));

			$query = "DELETE FROM {{quota_languagesettings}} WHERE quotals_quota_id=:quota_id";
	    	$conn->createCommand($query)->execute(array(':quota_id' => $_POST['quota_id']));

	    	$query = "DELETE FROM {{quota_members}} WHERE quota_id=:quota_id";
	    	$conn->createCommand($query)->execute(array(':quota_id' => $_POST['quota_id']));

	        $viewquota = "1";
	    }

	    if ($subaction == "quota_editquota" && bHasSurveyPermission($surveyid, 'quotas','update'))
	    {
	        $query = "SELECT * FROM {{quota}}
			          WHERE id=:quota_id";
	    	$comm = $conn->createCommand($query);
	    	$reader = $comm->query(array(':quota_id' => $_POST['quota_id']));
	        $quotainfo = $reader->read();

	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs,$baselang);

			$data['quotainfo'] = $quotainfo;
			$this->getController()->render("/admin/quotas/editquota_view",$data);

	        foreach ($langs as $lang)
	        {
	        	$langquery = "SELECT * FROM {{quota_languagesettings}} WHERE quotals_quota_id=:quota_id AND quotals_language=:lang";
	        	$comm = $conn->createCommand($langquery);
	        	$reader = $comm->query(array(':lang' => $lang, ':quota_id' => $_POST['quota_id']));
	        	$langquotainfo = $reader->read();

				$data['langquotainfo'] = $langquotainfo;
				$data['lang'] = $lang;
	        	$this->getController()->render("/admin/quotas/editquotalang_view",$data);

	        };
	        $this->getController()->render("/admin/quotas/editquotafooter_view",$data);
	    }

	    $totalquotas=0;
	    $totalcompleted=0;
	    $csvoutput=array();
	    if (($action == "quotas" && !isset($subaction)) || isset($viewquota))
	    {
			$query = "SELECT * FROM {{quota}} AS q
						LEFT JOIN {{quota_languagesettings}} as qls ON (q.id = qls.quotals_quota_id)
					  WHERE sid=:survey
					  	AND quotals_language=:lang
					  ORDER BY name";
	    	$result = $conn->createCommand($query)->query(array(':survey' => $surveyid, ':lang' => $baselang));

			$this->getController()->render("/admin/quotas/viewquotas_view",$data);

	        //if there are quotas let's proceed
	        if ($result->getRowCount() > 0)
	        {
	            //loop through all quotas
	            foreach ($result->readAll() as $quotalisting)
	            {
	            	$totalquotas+=$quotalisting['qlimit'];
					$completed=get_quotaCompletedCount($surveyid, $quotalisting['id']);
					$highlight=($completed >= $quotalisting['qlimit']) ? "" : "style='color: red'"; //Incomplete quotas displayed in red
					$totalcompleted=$totalcompleted+$completed;
					$csvoutput[]=$quotalisting['name'].",".$quotalisting['qlimit'].",".$completed.",".($quotalisting['qlimit']-$completed)."\r\n";

					$data['quotalisting'] = $quotalisting;
					$data['highlight'] = $highlight;
					$data['completed'] = $completed;
					$data['totalquotas'] = $totalquotas;
					$data['totalcompleted'] = $totalcompleted;
					$this->getController()->render("/admin/quotas/viewquotasrow_view",$data);

	                //check how many sub-elements exist for a certain quota
	                $query = "SELECT id,code,qid FROM {{quota_members}} where quota_id=:id";
	            	$result2 = $conn->createCommand($query)->query(array('id' => $quotalisting['id']));

	                if ($result2->getRowCount() > 0)
	                {
	                    //loop through all sub-parts
	                    foreach ($result2->readAll() as $quota_questions )
	                    {
	                        $question_answers = self::getQuotaAnswers($quota_questions['qid'],$surveyid,$quotalisting['id']);
							$data['question_answers'] = $question_answers;
	                    	$data['quota_questions'] = $quota_questions;
							$this->getController()->render("/admin/quotas/viewquotasrowsub_view",$data);
	                    }
	                }

	            }

	        }
	        else
	        {
	        	// No quotas have been set for this survey
	        	$this->getController()->render("/admin/quotas/viewquotasempty_view",$data);
	        }

	    	$data['totalquotas'] = $totalquotas;
	    	$data['totalcompleted'] = $totalcompleted;

			$this->getController()->render("/admin/quotas/viewquotasfooter_view",$data);
	    }
	    if(isset($_GET['quickreport']) && $_GET['quickreport'])
	    {
	        header("Content-Disposition: attachment; filename=results-survey".$surveyid.".csv");
	        header("Content-type: text/comma-separated-values; charset=UTF-8");
	        header("Pragma: public");
	        echo $clang->gT("Quota name").",".$clang->gT("Limit").",".$clang->gT("Completed").",".$clang->gT("Remaining")."\r\n";
	        foreach($csvoutput as $line)
	        {
	            echo $line;
	        }
	        die;
	    }
	    if(($subaction == "new_answer" || ($subaction == "new_answer_two" && !isset($_POST['quota_qid']))) && bHasSurveyPermission($surveyid,'quotas','create'))
	    {
	        if ($subaction == "new_answer_two") $_POST['quota_id'] = $_POST['quota_id'];

	        $allowed_types = "(type ='G' or type ='M' or type ='Y' or type ='A' or type ='B' or type ='I' or type = 'L' or type='O' or type='!')";

	        $query = "SELECT name FROM {{quota}} WHERE id=:quota_id";
	        $result = $conn->createCommand($query)->query(array(':quota_id' => $_POST['quota_id']));
	        foreach ($result->readAll() as $quotadetails)
	        {
	            $quota_name=$quotadetails['name'];
	        }

	        $query = "SELECT qid, title, question FROM {{questions}} WHERE $allowed_types AND sid=:surveyid AND language=:lang";
	    	$result = $conn->createCommand($query)->query(array(':surveyid' => $surveyid, ':lang' => $baselang));
	        if ($result->getRowCount() == 0)
	        {
				$this->getController()->render("/admin/quotas/newanswererror_view", $data);
	        } else
	        {
	        	$data['newanswer_result'] = $result->readAll();
				$data['quota_name'] = $quota_name;
				$this->getController()->render("/admin/quotas/newanswer_view", $data);
	        }
	    }

	    if($subaction == "new_answer_two" && isset($_POST['quota_qid']) && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
	        $query = "SELECT name FROM {{quota}} WHERE id=:id";
	        $result = $conn->createCommand($query)->query(array(':id' => $_POST['quota_qid']));
	        foreach ($result->readAll() as $quotadetails)
	        {
	            $quota_name=$quotadetails['name'];
	        }

	        $question_answers = self::getQuotaAnswers($_POST['quota_qid'],$surveyid,$_POST['quota_id']);
	        $x=0;

	        foreach ($question_answers as $qacheck)
	        {
	            if (isset($qacheck['rowexists'])) $x++;
	        }

	        reset($question_answers);
			$data['question_answers'] = $question_answers;
			$data['x'] = $x;
	    	$data['quota_name'] = $quota_name;
		    $this->getController()->render("/admin/quotas/newanswertwo_view", $data);

	    }

	    if ($subaction == "new_quota" && bHasSurveyPermission($surveyid, 'quotas','create'))
	    {
	        $langs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselang = GetBaseLanguageFromSurveyID($surveyid);
	        array_push($langs,$baselang);
	        $thissurvey=getSurveyInfo($surveyid);
			$data['thissurvey'] = $thissurvey;
			$data['langs'] = $langs;
			$this->getController()->render("/admin/quotas/newquota_view", $data);

	    }
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
	}

	function getQuotaAnswers($qid,$surveyid,$quota_id)
	{
	    $clang = $this->getController()->lang;
		$conn = Yii::app()->db;
	    $baselang = GetBaseLanguageFromSurveyID($surveyid);
		$query = "SELECT type, title FROM {{questions}} WHERE qid=:id AND language=:lang";
		$result = $conn->createCommand($query)->query(array(':id' => $qid, ':lang' => $baselang));
	    $qtype = $result->read();

	    if ($qtype['type'] == 'G')
	    {
	        $query = "SELECT * FROM {{quota_members}} WHERE sid=:sid and qid=:qid and quota_id=:quota_id";
			$result = $conn->createCommand($query)->query(array(':sid' => $surveyid, ':qid' => $qid, ':quota_id' => $quota_id));

	        $answerlist = array('M' => array('Title' => $qtype['title'], 'Display' => $clang->gT("Male"), 'code' => 'M'),
			'F' => array('Title' => $qtype['title'],'Display' => $clang->gT("Female"), 'code' => 'F'));

	        if ($result->getRowCount() > 0)
	        {
	            foreach ($result->readAll() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }

	        }
	    }

	    if ($qtype['type'] == 'M')
	    {
	        $query = "SELECT title,question FROM {{questions}} WHERE parent_qid=:qid";
	    	$result = $conn->createCommand($query)->query(array(':qid' => $qid));

	        $answerlist = array();

	        while ($dbanslist = $result->read())
	        {
	            $tmparrayans = array('Title' => $qtype['title'], 'Display' => substr($dbanslist['question'],0,40), 'code' => $dbanslist['title']);
	            $answerlist[$dbanslist['title']]	= $tmparrayans;
	        }

	    	$query = "SELECT * FROM {{quota_members}} WHERE sid=:sid and qid=:qid and quota_id=:quota_id";
	    	$result = $conn->createCommand($query)->query(array(':sid' => $surveyid, ':qid' => $qid, ':quota_id' => $quota_id));

	        if ($result->getRowCount() > 0)
	        {
	            while ($quotalist = $result->read())
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }

	        }
	    }

	    if ($qtype['type'] == 'L' || $qtype['type'] == 'O' || $qtype['type'] == '!')
	    {
	        $query = "SELECT * FROM {{quota_members}} WHERE sid=:sid and qid=:qid and quota_id=:quota_id";
	        $result = $conn->createCommand($query)->query(array(':sid' => $surveyid, ':qid' => $qid, ':quota_id' => $quota_id));

	        $query = "SELECT code,answer FROM {{answers}} WHERE qid=:qid";
	        $ansresult = $conn->createCommand($query)->query(array(':qid' => $qid));

	        $answerlist = array();

	        foreach ($ansresult->readAll() as $dbanslist)
	        {
	            $answerlist[$dbanslist['code']] = array('Title'=>$qtype['title'],
			                                                  'Display'=>substr($dbanslist['answer'],0,40),
			                                                  'code'=>$dbanslist['code']);
	        }

	    }

	    if ($qtype['type'] == 'A')
	    {
	    	$query = "SELECT * FROM {{quota_members}} WHERE sid=:sid and qid=:qid and quota_id=:quota_id";
	    	$result = $conn->createCommand($query)->query(array(':sid' => $surveyid, ':qid' => $qid, ':quota_id' => $quota_id));

	        $query = "SELECT title,question FROM {{questions}} WHERE parent_qid=:qid";
	    	$ansresult = $conn->createCommand($query)->query(array(':qid' => $qid));

	        $answerlist = array();

	        foreach ($ansresult->readAll() as $dbanslist)
	        {
	            for ($x=1; $x<6; $x++)
	            {
	                $tmparrayans = array('Title' => $qtype['title'], 'Display' => substr($dbanslist['question'],0,40).' ['.$x.']', 'code' => $dbanslist['title']);
	                $answerlist[$dbanslist['title']."-".$x]	= $tmparrayans;
	            }
	        }

	        if ($result->getRowCount() > 0)
	        {
	            foreach ($result->readAll() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }

	        }
	    }

	    if ($qtype['type'] == 'B')
	    {
	    	$query = "SELECT * FROM {{quota_members}} WHERE sid=:sid and qid=:qid and quota_id=:quota_id";
	    	$result = $conn->createCommand($query)->query(array(':sid' => $surveyid, ':qid' => $qid, ':quota_id' => $quota_id));

	        $query = "SELECT code,answer FROM {{answers}} WHERE qid=:qid";
	        $ansresult = $conn->createCommand($query)->query(array('qid' => $qid));

	        $answerlist = array();

	        foreach ($ansresult->readAll() as $dbanslist)
	        {
	            for ($x=1; $x<11; $x++)
	            {
	                $tmparrayans = array('Title' => $qtype['title'], 'Display' => substr($dbanslist['answer'],0,40).' ['.$x.']', 'code' => $dbanslist['code']);
	                $answerlist[$dbanslist['code']."-".$x]	= $tmparrayans;
	            }
	        }

	        if ($result->getRowCount() > 0)
	        {
	            foreach ($result->readAll() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }

	        }
	    }

	    if ($qtype['type'] == 'Y')
	    {
	    	$query = "SELECT * FROM {{quota_members}} WHERE sid=:sid and qid=:qid and quota_id=:quota_id";
	    	$result = $conn->createCommand($query)->query(array(':sid' => $surveyid, ':qid' => $qid, ':quota_id' => $quota_id));

	        $answerlist = array('Y' => array('Title' => $qtype['title'], 'Display' => $clang->gT("Yes"), 'code' => 'Y'),
			'N' => array('Title' => $qtype['title'],'Display' => $clang->gT("No"), 'code' => 'N'));

	        if ($result->getRowCount() > 0)
	        {
	            foreach ($ansresult->readAll() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }

	        }
	    }

	    if ($qtype['type'] == 'I')
	    {

	        $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        array_unshift($slangs,$baselang);

	    	$query = "SELECT * FROM {{quota_members}} WHERE sid=:sid and qid=:qid and quota_id=:quota_id";
	    	$result = $conn->createCommand($query)->query(array(':sid' => $surveyid, ':qid' => $qid, ':quota_id' => $quota_id));

	        while(list($key,$value) = each($slangs))
	        {
	            $tmparrayans = array('Title' => $qtype['title'], 'Display' => getLanguageNameFromCode($value,false), $value);
	            $answerlist[$value]	= $tmparrayans;
	        }

	        if ($result->getRowCount() > 0)
	        {
	            foreach ($result->readAll() as $quotalist)
	            {
	                $answerlist[$quotalist['code']]['rowexists'] = '1';
	            }

	        }
	    }

	    if (!isset($answerlist))
	    {
	        return array();
	    }
	    else
	    {
	        return $answerlist;
	    }
	}


}
