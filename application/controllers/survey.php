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
 * $Id: survey.php 10433 2011-07-06 14:18:45Z dionet $
 *
 */

class survey extends LSCI_Controller {

    function __construct()
    {
        parent::__construct();
    }

    public function _remap($method, $params = array())
    {
        array_unshift($params, $method);
        return call_user_func_array(array($this, 'action'), $params);
    }

    function action()
    {
        global $surveyid, $thistpl, $totalquestions;
        global $thissurvey, $thisstep;
        global $clienttoken, $tokensexist, $token;

        @ini_set('session.gc_maxlifetime', $this->config->item('sess_expiration'));

        $this->_loadRequiredHelpersAndLibraries();

        $_POST = $this->input->post();
        $param = $this->_getParameters(func_get_args(), $_POST);

        $surveyid = $param['sid'];
        $thisstep = $param['thisstep'];
        $move = $param['move'];
        $clienttoken = $param['token'];
        $standardtemplaterootdir = $this->config->item('standardtemplaterootdir');

        // unused vars in this method (used in methods using compacted method vars)
        $loadname = $param['loadname'];
        $loadpass = $param['loadpass'];
        $sitename = $this->config->item('sitename');
        $relativeurl = $this->config->item('relativeurl');

        $this->_setSessionToSurvey($surveyid);

        list($surveyExists, $isSurveyActive) = $this->_surveyExistsAndIsActive($surveyid);

        // collect all data in this method to pass on later
        $redata = compact(array_keys(get_defined_vars()));

        $clang = $this->_loadLimesurveyLang($surveyid);

        if ( $this->_isClientTokenDifferentFromSessionToken($clienttoken) )
        {
            $asMessage = array(
                    $clang->gT('Token mismatch'),
                    $clang->gT('The token you provided doesn\'t match the one in your session.'),
                    $clang->gT('Please wait to begin with a new session.')
                );
            $this->_createNewUserSessionAndRedirect($surveyid, $redata, __LINE__, $asMessage);
        }

        if ( $this->_isSurveyFinished() )
        {
            $asMessage = array(
                    $clang->gT('Previous session is set to be finished.'),
                    $clang->gT('Your browser reports that it was used previously to answer this survey. We are resetting the session so that you can start from the beginning.'),
                    $clang->gT('Please wait to begin with a new session.')
                );
            $this->_createNewUserSessionAndRedirect($surveyid, $redata, __LINE__, $asMessage);
        }


        if ($this->_isPreviewAction($param) && !$this->_canUserPreviewSurvey($surveyid))
        {
            $asMessage = array(
                    $clang->gT('Error'),
                    $clang->gT('We are sorry but you don\'t have permissions to do this.')
                );
            $this->_niceExit($redata, __LINE__, null, $asMessage);
        }

        if ( $this->_surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $isSurveyActive, $surveyExists) )
        {
            // TODO where is $stg_SessionName comming from?
            // TODO where is $sessionhandler comming from?
            $bPreviewRight = $this->_importAdminSessionForSurveyToGetPreviewAccess($surveyid, $stg_SessionName, $sessionhandler, $clang);

            if ($bPreviewRight === false)
            {
                // TODO is this still required (we have the basepath check at the top)
                if (isset($_REQUEST['rootdir']))
                {
                    $asMessage = array(
                        $clang->gT('Error'),
                        'You cannot start this script directly'
                    );
                    $this->_niceExit($redata, __LINE__, null, $asMessage);
                }

                $asMessage = array(
                        $clang->gT("Error"),
                        $clang->gT("We are sorry but you don't have permissions to do this."),
                        sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
                    );
                $this->_niceExit($redata, __LINE__, null, $asMessage);
            }
        }


        // TODO can this be moved to the top?
        // (Used to be global, used in ExpressionManager, merged into amVars. If not filled in === '')
        // can this be added in the first computation of $redata?
        if (isset($_SESSION['srid']))
        {
            $saved_id = $_SESSION['srid'];
        }
        // recompute $redata since $saved_id used to be a global
        $redata = compact(array_keys(get_defined_vars()));


        if ( $this->_didSessionTimeOut() )
        {
            // TODO is this still required (we have the basepath check at the top)
            if (isset($param['rootdir']))
            {
                $asMessage = array(
                        $clang->gT('Error'),
                        'You cannot start this script directly'
                    );
                $this->_kil_niceExitlPage($redata, __LINE__, null, $asMessage);
            }

            $asMessage = array(
                    $clang->gT("Error"),
                    $clang->gT("We are sorry but your session has expired."),
                    $clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection."),
                    sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
                );
            $this->_niceExit($redata, __LINE__, null, $asMessage);
        };

        // Set the language of the survey, either from POST, GET parameter of session var
        $sTempLanguage = null;
        if ( !empty($_POST['lang']) )
        {
            $sTempLanguage = $_POST['lang'];
        }
        else if ( isset($param['lang']) && !empty($surveyid) )
        {
            $sTempLanguage = $param['lang'];
        }

        if ( $sTempLanguage !== null )
        {
            $sTempLanguage = sanitize_languagecode($sTempLanguage);
            $clang = SetSurveyLanguage( $surveyid, $sTempLanguage);
            UpdateSessionGroupList($surveyid, $sTempLanguage);         // to refresh the language strings in the group list session variable
            UpdateFieldArray();                                        // to refresh question titles and question text
        }

        if (isset($_SESSION['s_lang']))
        {
            $clang = SetSurveyLanguage( $surveyid, $_SESSION['s_lang']);
        }
        elseif (!empty($surveyid))
        {
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $clang = SetSurveyLanguage( $surveyid, $baselang);
        }
        else
        {
            $baselang = $this->config->item("defaultlang");
        }

        if (isset($param['embedded_inc']))
        {
            $asMessage = array(
                    $clang->gT('Error'),
                    'You cannot start this script directly'
                );
            $this->_niceExit($redata, __LINE__, null, $asMessage);
        }


        //CHECK FOR REQUIRED INFORMATION (sid)
        if (!$surveyid)
        {
            if(isset($param['lang']))
            {
                $baselang = sanitize_languagecode($param['lang']);
            }
            elseif (!isset($baselang))
            {
                $baselang = $this->config->item("defaultlang");
            }
            $clang = $this->_loadLimesurveyLang($baselang);
            if(!isset($defaulttemplate))
            {
                $defaulttemplate=$this->config->item("defaulttemplate");
            }
            $languagechanger = makelanguagechanger();
            //Find out if there are any publicly available surveys
            $query = "SELECT a.sid, b.surveyls_title, a.publicstatistics
                      FROM ".$this->db->dbprefix('surveys')." AS a
                      INNER JOIN ".$this->db->dbprefix('surveys_languagesettings')." AS b
                      ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language )
                      WHERE surveyls_survey_id=a.sid
                      AND surveyls_language=a.language
                      AND surveyls_language='$baselang'
                      AND a.active='Y'
                      AND a.listpublic='Y'
                      AND ((a.expires >= '".date("Y-m-d H:i")."') OR (a.expires is null))
                      AND ((a.startdate <= '".date("Y-m-d H:i")."') OR (a.startdate is null))
                      ORDER BY surveyls_title";
            $result = db_execute_assoc($query,false,true) or show_error("Could not connect to database. If you try to install LimeSurvey please refer to the <a href='http://docs.limesurvey.org'>installation docs</a> and/or contact the system administrator of this webpage."); //Checked
            $list=array();

            if($result->num_rows() > 0)
            {
                foreach($result->result_array() as $rows)
                {
                    $link = "<li><a href='".site_url($rows['sid']);
                    if (isset($param['lang']))
                    {
                        $link .= "/lang-".sanitize_languagecode($param['lang']);
                    }
                    $link .= "'  class='surveytitle'>".$rows['surveyls_title']."</a>\n";
                    if ($rows['publicstatistics'] == 'Y') $link .= "<a href='".site_url("statistics_user/".$rows['sid'])."'>(".$clang->gT('View statistics').")</a>";
                    $link .= "</li>\n";
                    $list[]=$link;
                }
            }
            //Check for inactive surveys which allow public registration.
            $squery = "SELECT a.sid, b.surveyls_title, a.publicstatistics
                      FROM ".$this->db->dbprefix('surveys')." AS a
                      INNER JOIN ".$this->db->dbprefix('surveys_languagesettings')." AS b
                      ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language )
                      WHERE surveyls_survey_id=a.sid
                      AND surveyls_language=a.language
                      AND surveyls_language='$baselang'
                      AND a.allowregister='Y'
                      AND a.active='N'
                      AND a.listpublic='Y'
                      AND a.expires is not null
                      AND a.startdate is not null
                      ORDER BY surveyls_title";

            $sresult = db_execute_assoc($squery) or show_error("Couldn't execute $squery");


            if($sresult->num_rows() > 0)
            {
                $list[] = "<br/>".$clang->gT("Following survey(s) are not yet active but you can register for them.");
                foreach($sresult->result_array() as $rows)
                {
                    $link = "<li><a href=\"#\" id='inactivesurvey' onclick = 'sendreq(".$rows['sid'].");' ";
                    //$link = "<li><a href=\"#\" id='inactivesurvey' onclick = 'get2post(".site_url('survey/send/')."?sid={$rows['sid']}&amp;)sendreq(".$rows['sid'].",".$rows['startdate'].",".$rows['expires'].");' ";
                    $link .= "  class='surveytitle'>".$rows['surveyls_title']."</a>\n";
                    if ($rows['publicstatistics'] == 'Y') $link .= "<a href='".site_url("statistics_user/".$rows['sid'])."'>(".$clang->gT('View statistics').")</a>";
                    $link .= "</li><div id='regform'></div>\n";
                    $list[]=$link;
                }
            }

            if(count($list) < 1)
            {
                $list[]="<li class='surveytitle'>".$clang->gT("No available surveys")."</li>";
            }
            $surveylist=array(
                    "nosid"=>$clang->gT("You have not provided a survey identification number"),
                    "contact"=>sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$this->config->item("siteadminname"),encodeEmail($this->config->item("siteadminemail"))),
                    "listheading"=>$clang->gT("The following surveys are available:"),
                    "list"=>implode("\n",$list),
            );

            $thissurvey['name'] = $this->config->item("sitename");
            $thissurvey['templatedir'] = $defaulttemplate;

            $data['thissurvey'] = $thissurvey;
            //$data['privacy'] = $privacy;
            $data['surveylist'] = $surveylist;
            $data['surveyid'] = $surveyid;
            $data['templatedir'] = sGetTemplatePath($defaulttemplate);
            $data['templateurl'] = sGetTemplateURL($defaulttemplate)."/";
            $data['templatename'] = $defaulttemplate;
            $data['sitename'] = $this->config->item("sitename");
            $data['languagechanger'] = $languagechanger;

            //A nice exit
            sendcacheheaders();
            doHeader();
            $this->_printTemplateContent(sGetTemplatePath($defaulttemplate)."/startpage.pstpl", $data, __LINE__);

            $this->_printTemplateContent(sGetTemplatePath($defaulttemplate)."/surveylist.pstpl", $data, __LINE__);

            echo '<script type="text/javascript" >
            function sendreq(surveyid)
            {

                $.ajax({
                  type: "GET",
                  url: "'.site_url("register/ajaxregisterform").'/" + surveyid,
                }).done(function(msg) {
                  document.getElementById("regform").innerHTML = msg;
                });
            }
            </script>';




            $this->_niceExit($redata, __LINE__);
        }

        // Get token
        if (!isset($token))
        {
            $token=$clienttoken;
        }

        //GET BASIC INFORMATION ABOUT THIS SURVEY
        $totalBoilerplatequestions =0;
        $thissurvey=getSurveyInfo($surveyid, $_SESSION['s_lang']);

        if (isset($param['newtest']) && $param['newtest'] == "Y")
        {
            //Removes any existing timer cookies so timers will start again
            setcookie ("limesurvey_timers", "", time() - 3600);
        }



        //SEE IF SURVEY USES TOKENS AND GROUP TOKENS
        $i = 0; //$tokensexist = 0;
        if ($surveyExists == 1 && tableExists('tokens_'.$thissurvey['sid']))
        {
            $tokensexist = 1;

        }
        else
        {
            $tokensexist = 0;
            unset($_POST['token']);
            unset($param['token']);
            unset($token);
            unset($clienttoken);
        }




        //SET THE TEMPLATE DIRECTORY
        if (!$thissurvey['templatedir'])
        {
            $thistpl = sGetTemplatePath($defaulttemplate);
        }
        else
        {
            $thistpl = sGetTemplatePath($thissurvey['templatedir']);
        }


        $timeadjust = $this->config->item("timeadjust");
        //MAKE SURE SURVEY HASN'T EXPIRED
        if ($thissurvey['expiry']!='' and date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)>$thissurvey['expiry'] && $thissurvey['active']!='N')
        {
            $redata = compact(array_keys(get_defined_vars()));
            $asMessage = array(
                    $clang->gT("Error"),
                    $clang->gT("This survey is no longer available."),
                    sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
                );

            $this->_niceExit($redata, __LINE__, $thistpl, $asMessage);
        }

        //MAKE SURE SURVEY IS ALREADY VALID
        if ($thissurvey['startdate']!='' and  date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)<$thissurvey['startdate'] && $thissurvey['active']!='N')
        {
            $redata = compact(array_keys(get_defined_vars()));
            $asMessage = array(
                    $clang->gT("Error"),
                    $clang->gT("This survey is not yet started."),
                    sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
                );

            $this->_niceExit($redata, __LINE__, $thistpl, $asMessage);
        }

        //CHECK FOR PREVIOUSLY COMPLETED COOKIE
        //If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
        $cookiename="PHPSID".returnglobal('sid')."STATUS";
        if (isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == "COMPLETE" && $thissurvey['usecookie'] == "Y" && $tokensexist != 1 && (!isset($param['newtest']) || $param['newtest'] != "Y"))
        {
            $redata = compact(array_keys(get_defined_vars()));
            $asMessage = array(
                    $clang->gT("Error"),
                    $clang->gT("You have already completed this survey."),
                    sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
                );

            $this->_niceExit($redata, __LINE__, $thistpl, $asMessage);
        }


        //CHECK IF SURVEY ID DETAILS HAVE CHANGED
        if (isset($_SESSION['oldsid']))
        {
            $oldsid=$_SESSION['oldsid'];
        }

        if (!isset($oldsid))
        {
            $_SESSION['oldsid'] = $surveyid;
        }

        if (isset($oldsid) && $oldsid && $oldsid != $surveyid)
        {
            $savesessionvars=Array();
            if (isset($_SESSION['USER_RIGHT_PREVIEW']))
            {
                $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
                $savesessionvars["loginID"]=$_SESSION['loginID'];
                $savesessionvars["user"]=$_SESSION['user'];
            }
            session_unset();
            $_SESSION['oldsid']=$surveyid;
            foreach ($savesessionvars as $sesskey => $sessval)
            {
                $_SESSION[$sesskey]=$sessval;
            }
        }



        if (isset($_GET['loadall']) && $_GET['loadall'] == "reload")
        {
            if (returnglobal('loadname') && returnglobal('loadpass'))
            {
                $_POST['loadall']="reload";
            }
        }

        //LOAD SAVED SURVEY
        if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
        {
            $errormsg="";
            if ( !isset($param['loadname']) || $param['loadname'] == null )
            {
                $errormsg .= $clang->gT("You did not provide a name")."<br />\n";
            }
            if (!isset($param['loadpass']) || $param['loadpass'] == null )
            {
                $errormsg .= $clang->gT("You did not provide a password")."<br />\n";
            }

            // if security question answer is incorrect
            // Not called if scid is set in GET params (when using email save/reload reminder URL)
            if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
            {
                if ( (!isset($_POST['loadsecurity']) ||
                !isset($_SESSION['secanswer']) ||
                $_POST['loadsecurity'] != $_SESSION['secanswer']) &&
                !isset($_GET['scid']))
                {
                    $errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
                }
            }

            // Load session before loading the values from the saved data
            if (isset($_GET['loadall']))
            {
                buildsurveysession();
            }

            $_SESSION['holdname'] = $param['loadname']; //Session variable used to load answers every page.
            $_SESSION['holdpass'] = $param['loadpass']; //Session variable used to load answers every page.

            if ($errormsg == "") loadanswers();
            $move = "movenext";

            if ($errormsg)
            {
                $_POST['loadall'] = $clang->gT("Load Unfinished Survey");
            }
        }
        //Allow loading of saved survey
        if (isset($_POST['loadall']) && $_POST['loadall'] == $clang->gT("Load Unfinished Survey"))
        {
            $redata = compact(array_keys(get_defined_vars()));
            $this->load->library("load_answers");
            $this->load_answers->run($redata);
        }


        //Check if TOKEN is used for EVERY PAGE
        //This function fixes a bug where users able to submit two surveys/votes
        //by checking that the token has not been used at each page displayed.
        // bypass only this check at first page (Step=0) because
        // this check is done in buildsurveysession and error message
        // could be more interresting there (takes into accound captcha if used)
        if ($tokensexist == 1 && isset($token) && $token &&
            isset($_SESSION['step']) && $_SESSION['step']>0 && db_tables_exist($this->db->dbprefix('tokens_'.$surveyid)))
        {
            //check if tokens actually haven't been already used
            $areTokensUsed = usedTokens(trim(strip_tags(returnglobal('token'))),$surveyid);
            // check if token actually does exist
            // check also if it is allowed to change survey after completion
            if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
                $tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." ";
            } else {
                $tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." AND (completed = 'N' or completed='')";
            }
            $tkresult = db_execute_assoc($tkquery); //Checked
            $tokendata = $tkresult->row_array();
            if ($tkresult->num_rows()==0 || $areTokensUsed)
            {
                sendcacheheaders();
                doHeader();
                //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                $redata = compact(array_keys(get_defined_vars()));
                $this->_printTemplateContent($thistpl.'/startpage.pstpl', $redata, __LINE__);
                $this->_printTemplateContent($thistpl.'/survey.pstpl', $redata, __LINE__);
                $asMessage = array(
                        null,
                        $clang->gT("This is a controlled survey. You need a valid token to participate."),
                        sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']." (<a href='mailto:{$thissurvey['adminemail']}'>"."{$thissurvey['adminemail']}</a>)")
                    );

                $this->_niceExit($redata, __LINE__, $thistpl, $asMessage, true);
            }
        }
        if ($tokensexist == 1 && isset($token) && $token && db_tables_exist($this->db->dbprefix('tokens_'.$surveyid))) //check if token is in a valid time frame
        {
            // check also if it is allowed to change survey after completion
            if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
                $tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." ";
            } else {
                $tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." AND (completed = 'N' or completed='')";
            }
            $tkresult = db_execute_assoc($tkquery); //Checked
            $tokendata = $tkresult->row_array();
            if (isset($tokendata['validfrom']) && (trim($tokendata['validfrom'])!='' && $tokendata['validfrom']>date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)) ||
            isset($tokendata['validuntil']) && (trim($tokendata['validuntil'])!='' && $tokendata['validuntil']<date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)))
            {
                sendcacheheaders();
                doHeader();
                //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                $redata = compact(array_keys(get_defined_vars()));
                $this->_printTemplateContent($thistpl.'/startpage.pstpl', $redata, __LINE__);
                $this->_printTemplateContent($thistpl.'/survey.pstpl', $redata, __LINE__);

                $asMessage = array(
                        null,
                        $clang->gT("We are sorry but you are not allowed to enter this survey."),
                        $clang->gT("Your token seems to be valid but can be used only during a certain time period."),
                        sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']." (<a href='mailto:{$thissurvey['adminemail']}'>"."{$thissurvey['adminemail']}</a>)")
                    );

                $this->_niceExit($redata, __LINE__, $thistpl, $asMessage, true);
            }
        }



        //Clear session and remove the incomplete response if requested.
        if (isset($move) && $move == "clearall")
        {
            $s_lang = $_SESSION['s_lang'];
            if (isset($_SESSION['srid']))
            {
                // find out if there are any fuqt questions - checked
                $fieldmap = createFieldMap($surveyid);
                foreach ($fieldmap as $field)
                {
                    if ($field['type'] == "|" && !strpos($field['fieldname'], "_filecount"))
                    {
                        if (!isset($qid)) { $qid = array(); }
                        $qid[] = $field['fieldname'];
                    }
                }

                // if yes, extract the response json to those questions
                if (isset($qid))
                {
                    $query = "SELECT * FROM ".$this->db->dbprefix("survey_".$surveyid)." WHERE id=".$_SESSION['srid'];
                    $result = db_execute_assoc($query);
                    foreach($result->result_array() as $row)
                    {
                        foreach ($qid as $question)
                        {
                            $json = $row[$question];
                            if ($json == "" || $json == NULL)
                                continue;

                            // decode them
                            $phparray = json_decode($json);

                            foreach ($phparray as $metadata)
                            {
                                $target = $this->config->item("uploaddir")."/surveys/".$surveyid."/files/";
                                // delete those files
                                unlink($target.$metadata->filename);
                            }
                        }
                    }
                }
                // done deleting uploaded files


                // delete the response but only if not already completed
                db_execute_assoc('DELETE FROM '.$this->db->dbprefix('survey_'.$surveyid).' WHERE id='.$_SESSION['srid']." AND submitdate IS NULL");

                // also delete a record from saved_control when there is one
                db_execute_assoc('DELETE FROM '.$this->db->dbprefix('saved_control'). ' WHERE srid='.$_SESSION['srid'].' AND sid='.$surveyid);
            }
            session_unset();
            session_destroy();
            setcookie(session_name(),"EXPIRED",time()-120);
            sendcacheheaders();
            if (isset($_GET['redirect']))
            {
                session_write_close();
                header("Location: {$_GET['redirect']}");
            }
            doHeader();

            $redata = compact(array_keys(get_defined_vars()));
            $this->_printTemplateContent($thistpl.'/startpage.pstpl', $redata, __LINE__);
            echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
            ."\t<script type='text/javascript'>\n"
            ."\t<!--\n"
            ."function checkconditions(value, name, type)\n"
            ."\t{\n"
            ."\t}\n"
            ."\t//-->\n"
            ."\t</script>\n\n";

            //Present the clear all page using clearall.pstpl template
            $this->_printTemplateContent($thistpl.'/clearall.pstpl', $redata, __LINE__);

            $this->_niceExit($redata, __LINE__, $thistpl);
        }

        if (isset($param['newtest']) && $param['newtest'] == "Y")
        {
            $savesessionvars=Array();
            if (isset($_SESSION['USER_RIGHT_PREVIEW']))
            {
                $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
                $savesessionvars["loginID"]=$_SESSION['loginID'];
                $savesessionvars["user"]=$_SESSION['user'];
            }
            session_unset();
            $_SESSION['oldsid']=$surveyid;
            foreach ($savesessionvars as $sesskey => $sessval)
            {
                $_SESSION[$sesskey]=$sessval;
            }
            //DELETE COOKIE (allow to use multiple times)
            setcookie($cookiename, "INCOMPLETE", time()-120);
            //echo "Reset Cookie!";
        }

        //Check to see if a refering URL has been captured.
        GetReferringUrl();
        // Let's do this only if
        //  - a saved answer record hasn't been loaded through the saved feature
        //  - the survey is not anonymous
        //  - the survey is active
        //  - a token information has been provided
        //  - the survey is setup to allow token-response-persistence

        if ($thissurvey['tokenanswerspersistence'] == 'Y' && !isset($_SESSION['srid']) && $thissurvey['anonymized'] == "N" && $thissurvey['active'] == "Y" && isset($token) && $token !='')
        {

            // load previous answers if any (dataentry with nosubmit)
            $srquery="SELECT id FROM {$thissurvey['tablename']}"
            . " WHERE {$thissurvey['tablename']}.token='".$this->db->escape($token)."' order by id desc";

            $result = db_select_limit_assoc($srquery,1);
            if ($result->num_rows()>0)
            {
                $row=reset($result->result_array());
                if($row['submitdate']=='' || ($row['submitdate']!='' && $thissurvey['alloweditaftercompletion'] == 'Y'))
                    $_SESSION['srid'] = $row['id'];
            }
            buildsurveysession();
            loadanswers();
        }

        // SAVE POSTED ANSWERS TO DATABASE IF MOVE (NEXT,PREV,LAST, or SUBMIT) or RETURNING FROM SAVE FORM
        if (isset($move) || isset($_POST['saveprompt']))
        {
            $redata = compact(array_keys(get_defined_vars()));
            //save.php
            $this->load->library("Save");
            $this->save->run($redata);

            // RELOAD THE ANSWERS INCASE SOMEONE ELSE CHANGED THEM
            if ($thissurvey['active'] == "Y" &&
                    ( $thissurvey['allowsave'] == "Y" || $thissurvey['tokenanswerspersistence'] == "Y") )
            {
                loadanswers();
            }
        }

        if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'previewgroup')){
                $thissurvey['format'] = 'G';
                buildsurveysession();
        }

        sendcacheheaders();

        //Send local variables to the appropriate survey type
        $redata = compact(array_keys(get_defined_vars()));

        //CALL APPROPRIATE SCRIPT
        switch ($thissurvey['format'])
        {
            case "A": //All in one
                $this->load->library("Survey_format");
                $this->survey_format->run($redata);
                break;
            case "G": //Group at a time
                $this->load->library("Group_format");
                $this->group_format->run($redata);
                break;
            case "S": //One at a time
             default:
                $this->load->library("Question_format");
                $this->question_format->run($redata);
        }

		if (isset($_POST['saveall']) || isset($flashmessage))
		{
		    echo "<script language='JavaScript'> $(document).ready( function() { alert('".$clang->gT("Your responses were successfully saved.","js")."');}) </script>";
		}
    }

    function _getParameters($args = array(), $post = array())
    {
        $param = array();
        if($args[0]==__CLASS__) array_shift($args);
        if(count($args)%2 == 0) {
            for ($i = 0; $i < count($args); $i+=2) {
                //Sanitize input from URL with returnglobal
                $param[$args[$i]] = returnglobal($args[$i], $args[$i+1]);
            }
        }

        if( !isset($param['action']) )
            $param['action'] = isset($post['action']) ? $post['action'] : null;
        if( !isset($param['newtest']) )
            $param['newtest'] = isset($post['newtest']) ? $post['newtest'] : null;
        if( !isset($param['gid']) )
            $param['gid'] = isset($post['gid']) ? $post['gid'] : null;

        if ( !isset($param['sid']) )
            $param['sid'] = returnglobal('sid');
        if ( !isset($param['loadname']) )
            $param['loadname'] = returnglobal('loadname');
        if ( !isset($param['loadpass']) )
            $param['loadpass'] = returnglobal('loadpass');
        if ( !isset($param['scid']) )
            $param['scid'] = returnglobal('scid');
        if ( !isset($param['thisstep']) )
            $param['thisstep'] = returnglobal('thisstep');
        if ( !isset($param['move']) )
            $param['move'] = returnglobal('move');
        if ( !isset($param['token']) )
            $param['token'] = returnglobal('token');

        if ( !isset($param['thisstep']) )
            $param['thisstep'] = '';

        return $param;
    }

    function _getSessionName($surveyId)
    {
        // Compute the Session name
        // Session name is based:
        // * on this specific limesurvey installation (Value SessionName in DB)
        // * on the surveyid (from Get or Post param). If no surveyid is given we are on the public surveys portal
        $sSessionname = getGlobalSetting('SessionName');
        if ($sSessionname != '')
        {
            if ($surveyId)
            {
                return $sSessionname.'-runtime-'.$surveyId;
            }
            return $sSessionname.'-runtime-publicportal';
        }
        return 'LimeSurveyRuntime-'.$surveyId;
    }

    /**
     * Switch to survey session, from admin session if applicable.
     *
     * @param string $surveyId
     */
    function _setSessionToSurvey($surveyId)
    {
        $sSessionname = $this->_getSessionName($surveyId);
        if (LS_PHP_Session::isActive())
        {
            throw new BadMethodCallException('Session already started.');
        }
        session_name($sSessionname);
        $sCurrentname = $this->session->getActiveName();
        if ($sCurrentname !== $sSessionname)
        {
            throw new RuntimeException(sprintf('Session name mismatch, must be %s, is %s.', $sSessionname, $sCurrentname));
        }
        // check if session is new, if not check for admin session, pick data to merge if available
        // and get back on survey session name.
        $aMergevars = array('loginID', 'USER_RIGHT_SUPERADMIN');
        $aMerge = array();
        if (!$this->session->all_userdata())
        {
            $this->session->sess_destroy();
            $sAdminName = 'PHPSESSID';
            session_name($sAdminName);
            $sCurrentname = $this->session->getActiveName();
            if ($sCurrentname !== $sAdminName)
            {
                throw new RuntimeException(sprintf('Session name mismatch, must be %s, is %s.', $sAdminName, $sCurrentname));
            }
            if ($aData = $this->session->all_userdata())
            {
                foreach ($aMergevars as $sVar)
                {
                    if (isset ($aData[$sVar]))
                    {
                        $aMerge[$sVar] = $aData[$sVar];
                    }
                }
                // switch session from admin to survey
                LS_PHP_Session::changeTo($sSessionname);
                $this->session->close();
            }
            else
            {
                // there is no admin session (data), destroy it
                // and change the session name
                $this->session->sess_destroy();
                session_name($sSessionname);
            }
        }

        // @todo check if $this->session handles PHP configuration
        //       -> on start(). check what is required. -> give a setCookieParams() to private config
        session_set_cookie_params(0, $this->config->item("relativeurl"));
        // @todo move that up and/or where it belongs to, session might need to be restarted if necessary to set
        //       for survey session
        $sCurrentname = $this->session->getActiveName();
        if ($sCurrentname !== $sSessionname)
        {
            throw new RuntimeException(sprintf('Session name mismatch, must be %s, is %s.', $sSessionname, $sCurrentname));
        }
        foreach($aMerge as $sVar => $mValue)
        {
            isset ($_SESSION[$sVar]) || $_SESSION[$sVar] = $mValue;
        }
    }

    function _saveSessionVars($surveyId, $bPreviewRight)
    {
        if ( !$bPreviewRight )
            return array();

        $saveSessionVars['USER_RIGHT_PREVIEW'] = $surveyId;
        $saveSessionVars['loginID'] = $_SESSION['loginID'];
        $saveSessionVars['user'] = $_SESSION['user'];

        return $saveSessionVars;
    }

    function _loadAdminSession($stg_SessionName) {
        // Store initial session name
        $sOriginalSessionName = session_name();

        // One way (not implemented here) would be to start the
        // user session from a duplicate of the admin session
        // - destroy the new session
        // - load admin session (with correct session name)
        // - close admin session
        // - change used session name to default
        // - open new session (takes admin session id)
        // - regenerate brand new session id for this session

        // The solution implemented here is to copy some
        // fields from the admin session to the new session
        // - first destroy the new (empty) user session
        // - then open admin session
        // - record interresting values from the admin session
        // - duplicate admin session under another name and Id
        // - destroy the duplicated admin session
        // - start a brand new user session
        // - copy interresting values in this user session

        session_destroy();    // make it silent because for
        // some strange reasons it fails sometimes
        // which is not a problem
        // but if it throws an error then future
        // session functions won't work because
        // headers are already sent.

        if (!empty($stg_SessionName))
        {
            session_name($stg_SessionName);
        }
        else
        {
            session_name('LimeSurveyAdmin');
        }
        session_start(); // Loads Admin Session

        return $sOriginalSessionName;

    }

    function _destroyOldSession($sInitialSessionName, $sSessionHandler, $clang)
    {
        // change session name and id
        // then delete this new session
        // ==> the original admin session remains valid
        // ==> it is possible to start a new session
        // @todo session_destroy() used in here (and elsewehere in this file)
        //       , not ->session->sess_destroy(), check.
        session_name($sInitialSessionName);
        if ( $sSessionHandler == 'db' )
        {
            adodb_session_regenerate_id();
        }
        elseif ( session_regenerate_id() === false )
        {
            $asMessage = array(
                $clang->gT('Error'),
                'Error Regenerating Session Id'
            );
            $this->_niceExit($redata, __LINE__, null, $asMessage);
        }
        session_destroy();
    }

    function _restoreSavedSession($sSessionHandler, $amSavedSessionVars, $bPreviewRight, $clang)
    {
        // start new session
        session_start();
        // regenerate id so that the header geenrated by previous
        // regenerate_id is overwritten
        // needed after clearall
        if  ( $sSessionHandler == 'db' )
        {
            adodb_session_regenerate_id();
        }
        elseif ( session_regenerate_id() === false )
        {
            $asMessage = array(
                    $clang->gT('Error'),
                	'Error Regenerating Session Id'
                );
            $this->_niceExit($redata, __LINE__, null, $asMessage);
        }

        if ( $bPreviewRight )
        {
            foreach ($amSavedSessionVars as $sSessionKey => $mSessionValue)
            {
                $_SESSION[$sSessionKey] = $mSessionValue;
            }
        }
    }

    function _loadRequiredHelpersAndLibraries()
    {
        //Load helpers, libraries and config vars
        $this->load->helper("database");
        $this->load->helper("frontend");
        $this->load->helper("surveytranslator");
//        $this->load->library("Dtexts");
    }

    function _loadLimesurveyLang($mvSurveyIdOrBaseLang)
    {
        if ( is_int($mvSurveyIdOrBaseLang) )
        {
            $baselang = GetBaseLanguageFromSurveyID($surveyId);
        }
        else
        {
            $baselang = $mvSurveyIdOrBaseLang;
        }

        $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));

        return $this->limesurvey_lang;
    }

    function _surveyExistsAndIsActive($surveyId)
    {
        $isSurveyActive = false;
        $surveyExists = false;

        if ($surveyId)
        {
            $aRow = db_execute_assoc("SELECT active FROM ".$this->db->dbprefix('surveys')." WHERE sid='".$surveyId."'")->row_array();
            if (isset($aRow['active']))
            {
                $surveyExists = true;
                if($aRow['active'] == 'Y')
                {
                    $isSurveyActive = true;
                }
            }
        }

        return array($surveyExists, $isSurveyActive);
    }


    function _isClientTokenDifferentFromSessionToken($clientToken)
    {
        return $clientToken != '' && isset($_SESSION['token']) && $clientToken != $_SESSION['token'];
    }

    function _isSurveyFinished()
    {
        return isset($_SESSION['finished']) && $_SESSION['finished'] === true;
    }

    function _isPreviewAction($param = array())
    {
        return isset($param['action']) && $param['action'] == 'previewgroup';
    }

    function _surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $bIsSurveyActive, $bSurveyExists)
    {
        $bSurveyPreviewRequireAuth = $this->config->item('surveyPreview_require_Auth');
        return $surveyid && $bIsSurveyActive === false && $bSurveyExists && isset($bSurveyPreviewRequireAuth) && $bSurveyPreviewRequireAuth == true &&  !$this->_canUserPreviewSurvey($surveyid);
    }

    function _didSessionTimeout()
    {
        return !isset($_SESSION['s_lang']);
    }

    function _canUserPreviewSurvey($surveyId)
    {
        if ( !isset($_SESSION['loginID'], $_SESSION['USER_RIGHT_SUPERADMIN']) )
            return false;

        if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 )
            return true;

        $rightresult = db_execute_assoc(
        	"SELECT uid
        	FROM ".$this->db->dbprefix('survey_permissions')."
        	WHERE sid = ".$this->db->escape($surveyId)."
        	AND uid = '".$this->db->escape($_SESSION['loginID'])."'
        	GROUP BY uid");
        if ( $rightresult->num_rows() > 0 )
            return true;
        return false;
    }

    function _userHasPreviewAccessSession($surveyId){
        return (!(isset($_SESSION['USER_RIGHT_PREVIEW']) && ($_SESSION['USER_RIGHT_PREVIEW'] == $surveyId)));
    }

    function _niceExit(&$redata, $iDebugLine, $sTemplateDir = null, $asMessage = array(), $bKillSession = false)
    {
        if ( $sTemplateDir === null )
            $sTemplateDir = $this->config->item("standardtemplaterootdir").DIRECTORY_SEPARATOR.'default';

        sendcacheheaders();

        doHeader();

        $this->_printTemplateContent($sTemplateDir.DIRECTORY_SEPARATOR.'startpage.pstpl', $redata, $iDebugLine);
        $this->_printMessage($asMessage);
        $this->_printTemplateContent($sTemplateDir.DIRECTORY_SEPARATOR.'endpage.pstpl', $redata, $iDebugLine);

        doFooter();

        if ( $bKillSession )
            killSession();

        exit;
    }

    function _createNewUserSessionAndRedirect($surveyId, &$redata, $iDebugLine, $asMessage = array())
    {
        $baselang = GetBaseLanguageFromSurveyID($surveyId);
        $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
        $clang = $this->limesurvey_lang;
        // Let's first regenerate a session id
        killSession();
        // Let's redirect the client to the same URL after having reseted the session
        //header("Location: $rooturl/index.php?" .$_SERVER['QUERY_STRING']);
        $this->_niceExit($redata, $iDebugLine, null, $asMessage);
    }

    function _importAdminSessionForSurveyToGetPreviewAccess($surveyId, $stg_SessionName, $sSessionHandler, $clang)
    {
        // admin session and permission have not already been imported
        // for this particular survey
        if ( !$this->_userHasPreviewAccessSession($surveyId) )
        {
            $sOriginalSessionName = $this->_loadAdminSession($stg_SessionName);

            $bPreviewRight = $this->_canUserPreviewSurvey($surveyId);
            $amSavedSessionVars = $this->_saveSessionVars($surveyId, $bPreviewRight);

            $this->_destroyOldSession($sOriginalSessionName, $sSessionHandler, $clang);
            $this->_restoreSavedSession($sSessionHandler, $amSavedSessionVars, $bPreviewRight, $clang);
        }
        else
        {
            // already authorized
            $bPreviewRight = true;
        }

        return $bPreviewRight;
    }

    function _printMessage($asLines)
    {
        if ( func_num_args() > 1 )
            $asLines = func_get_args();

        if ( count($asLines) == 0 )
            return;

        $sError = array_shift($asLines);

        echo "\t<div id='wrapper'>\n";
        echo "\t<p id='tokenmessage'>\n";
        if ( $sError != null )
        {
            echo "\t<span class='error'>".$sError."</span><br /><br />\n";
        }
        echo "\t".implode ("<br /><br />\n\t", $asLines)."<br /><br />\n";
        echo "\t</p>\n";
        echo "\t</div>\n";
    }

    function _printTemplateContent($sTemplateFile, &$redata, $iDebugLine = -1)
    {
        echo templatereplace(file_get_contents($sTemplateFile),array(),$redata,'survey['.$iDebugLine.']');
    }

    function test($yes)
    {
        debugbreak();
    }
}

/* End of file survey.php */
/* Location: ./application/controllers/survey.php */
