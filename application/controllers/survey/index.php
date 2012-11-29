<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class index extends CAction {

    public function run()
    {
        $this->action();
    }

    function action()
    {
        global $surveyid;
        global $thisstep;
        global $clienttoken, $tokensexist, $token;
        global $clang;
        $clang = Yii::app()->lang;
        @ini_set('session.gc_maxlifetime', Yii::app()->getConfig('sess_expiration'));

        $this->_loadRequiredHelpersAndLibraries();

        $param = $this->_getParameters(func_get_args(), $_POST);
        $surveyid = $param['sid'];
        Yii::app()->setConfig('surveyID',$surveyid);
        $thisstep = $param['thisstep'];
        $move = $param['move'];
        $clienttoken = $param['token'];
        $standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');
        $thissurvey = getSurveyInfo($surveyid);

        // unused vars in this method (used in methods using compacted method vars)
        @$loadname = $param['loadname'];
        @$loadpass = $param['loadpass'];
        $sitename = Yii::app()->getConfig('sitename');

        if (isset($param['newtest']) && $param['newtest'] == "Y")
        {
            killSurveySession($surveyid);
        }


        list($surveyExists, $isSurveyActive) = $this->_surveyExistsAndIsActive($surveyid);

        // collect all data in this method to pass on later
        $redata = compact(array_keys(get_defined_vars()));

        $clang = $this->_loadLimesurveyLang($surveyid);

        if ( $this->_isClientTokenDifferentFromSessionToken($clienttoken,$surveyid) )
        {
            $aMessage = array(
            $clang->gT('Token mismatch'),
            $clang->gT('The token you provided doesn\'t match the one in your session.'),
            $clang->gT('Please wait to begin with a new session.')
            );
            $this->_createNewUserSessionAndRedirect($surveyid, $redata, __LINE__, $aMessage);
        }

        if ( $this->_isSurveyFinished($surveyid) )
        {
            $aMessage = array(
            $clang->gT('Previous session is set to be finished.'),
            $clang->gT('Your browser reports that it was used previously to answer this survey. We are resetting the session so that you can start from the beginning.'),
            $clang->gT('Please wait to begin with a new session.')
            );
            $this->_createNewUserSessionAndRedirect($surveyid, $redata, __LINE__, $aMessage);
        }


        if ($this->_isPreviewAction($param) && !$this->_canUserPreviewSurvey($surveyid))
        {
            $aMessage = array(
            $clang->gT('Error'),
            $clang->gT("We are sorry but you don't have permissions to do this.")
            );
            $this->_niceExit($redata, __LINE__, null, $aMessage);
        }

        if ( $this->_surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $isSurveyActive, $surveyExists) )
        {
            $bPreviewRight = $this->_userHasPreviewAccessSession($surveyid);

            if ($bPreviewRight === false)
            {
                $aMessage = array(
                $clang->gT("Error"),
                $clang->gT("We are sorry but you don't have permissions to do this."),
                sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
                );
                $this->_niceExit($redata, __LINE__, null, $aMessage);
            }
        }


        // TODO can this be moved to the top?
        // (Used to be global, used in ExpressionManager, merged into amVars. If not filled in === '')
        // can this be added in the first computation of $redata?
        if (isset($_SESSION['survey_'.$surveyid]['srid']))
        {
            $saved_id = $_SESSION['survey_'.$surveyid]['srid'];
        }
        // recompute $redata since $saved_id used to be a global
        $redata = compact(array_keys(get_defined_vars()));


        /*if ( $this->_didSessionTimeOut() )
        {
        // @TODO is this still required ?
        $asMessage = array(
        $clang->gT("Error"),
        $clang->gT("We are sorry but your session has expired."),
        $clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection."),
        sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
        );
        $this->_niceExit($redata, __LINE__, null, $asMessage);
        };*/

        // Set the language of the survey, either from POST, GET parameter of session var
        if ( !empty($_REQUEST['lang']) )
        {
            $sTempLanguage = sanitize_languagecode($_REQUEST['lang']);
        }
        elseif ( !empty($param['lang']) )
        {
            $sTempLanguage = sanitize_languagecode($param['lang']);
        }
        elseif (isset($_SESSION['survey_'.$surveyid]['s_lang']))
        {
            $sTempLanguage = $_SESSION['survey_'.$surveyid]['s_lang'];
        }
        else
        {
            $sTempLanguage='';
        }

        //CHECK FOR REQUIRED INFORMATION (sid)
        if ($surveyid && $surveyExists)
        {
            LimeExpressionManager::SetSurveyId($surveyid); // must be called early - it clears internal cache if a new survey is being used
            $clang = SetSurveyLanguage( $surveyid, $sTempLanguage);
            UpdateSessionGroupList($surveyid, $sTempLanguage);  // to refresh the language strings in the group list session variable
            UpdateFieldArray();        // to refresh question titles and question text

        }
        else
        {
            if (!is_null($param['lang']))
            {
                $sDisplayLanguage=$param['lang'];
            }
            else{
                $sDisplayLanguage = Yii::app()->getConfig('defaultlang');
            }
            $clang = $this->_loadLimesurveyLang(sanitize_languagecode($sDisplayLanguage));
            if(!isset($defaulttemplate))
            {
                $defaulttemplate=Yii::app()->getConfig("defaulttemplate");
            }

            //Find out if there are any publicly available surveys
            $sQuery="SELECT sid, ls1.surveyls_title as basetitle, ls2.surveyls_title as localetitle, publicstatistics, language
            FROM lime_surveys
            INNER JOIN lime_surveys_languagesettings ls1 ON (ls1.surveyls_survey_id = sid) AND (ls1.surveyls_language= language)
            left JOIN lime_surveys_languagesettings ls2 ON (ls2.surveyls_survey_id = sid) AND (ls2.surveyls_language= :displaylanguage)
            WHERE active='Y' 
            AND listpublic='Y' 
            AND ((expires >= '".date("Y-m-d H:i")."') OR (expires IS NULL))
            AND ((startdate <= '".date("Y-m-d H:i")."') OR (startdate IS NULL))
            ORDER BY ls1.surveyls_title";
            
            try {
                // Query is too complex to be changed to ActiveRecord
                $oResult=Yii::app()->db->createCommand($sQuery)->bindValues(array(':displaylanguage'=>$sDisplayLanguage))->query();
            } catch(CDbException $e) {
                safeDie("Could not connect to database. If you try to install LimeSurvey please refer to the <a href='http://docs.limesurvey.org'>installation docs</a> and/or contact the system administrator of this webpage."); //Checked                    
            }
            $aSurveyLinkList=array();

            foreach($oResult->readAll() as $aRow)
            {
                if (!is_null($aRow['localetitle']) )
                {
                    $aRow['basetitle']=$aRow['localetitle'];
                    $sLinkLanguage = $sDisplayLanguage;
                }
                else
                {
                    $sLinkLanguage = $aRow['language'];
                }
                $link = "<li><a href='".$this->getController()->createUrl('/survey/index/sid/'.$aRow['sid'].'/lang/'.$sLinkLanguage);
                $link .= "' lang='{$sLinkLanguage}' class='surveytitle'>".$aRow['basetitle']."</a>\n";
                if ($aRow['publicstatistics'] == 'Y') $link .= "<a href='".$this->getController()->createUrl("/statistics_user/action/surveyid/".$aRow['sid'])."/language/".$sLinkLanguage."'>(".$clang->gT('View statistics').")</a>";
                $link .= "</li>\n";
                $aSurveyLinkList[]=$link;
            }

            //Check for not set started surveys which allow public registration.
            // @TODO add a new template replace {SURVEYREGISTERLIST} ?
            $sQuery="SELECT sid, ls1.surveyls_title as basetitle, ls2.surveyls_title as localetitle, publicstatistics, language
            FROM lime_surveys
            INNER JOIN lime_surveys_languagesettings ls1 ON (ls1.surveyls_survey_id = sid) AND (ls1.surveyls_language= language)
            left JOIN lime_surveys_languagesettings ls2 ON (ls2.surveyls_survey_id = sid) AND (ls2.surveyls_language= :displaylanguage)
            WHERE allowregister='Y'
            AND active='Y'
            AND listpublic='Y'
            AND ((expires >= '".date("Y-m-d H:i")."') OR (expires is null))
            AND (startdate >= '".date("Y-m-d H:i")."')
            ORDER BY ls1.surveyls_title";
            // Query is too complex to be changed to ActiveRecord
            $sResult=Yii::app()->db->createCommand($sQuery)->bindValues(array(':displaylanguage'=>$sDisplayLanguage))->query();

            $aRows=$sResult->readAll();
            if(count($aRows) > 0)
            {
                $aSurveyLinkList[] = "</ul>"
                ." <div class=\"survey-list-heading\">".$clang->gT("Following survey(s) are not yet active but you can register for them.")."</div>"
                ." <ul>"; // TODO give it to template
                foreach($aRows as $aRow)
                {
                    if (!is_null($aRow['localetitle']) )
                    {
                        $aRow['basetitle']=$aRow['localetitle'];
                        $sLinkLanguage = $sDisplayLanguage;
                    }
                    else
                    {
                        $sLinkLanguage = $aRow['language'];
                    }
                    $link = "<li><a href=\"#\" id='inactivesurvey' onclick = 'sendreq(".$aRow['sid'].");' ";
                    //$link = "<li><a href=\"#\" id='inactivesurvey' onclick = 'convertGETtoPOST(".$this->getController()->createUrl('survey/send/')."?sid={$rows['sid']}&amp;)sendreq(".$rows['sid'].",".$rows['startdate'].",".$rows['expires'].");' ";
                    $link .= " lang='{$sLinkLanguage}' class='surveytitle'>".$aRow['surveyls_title']."</a>\n";
                    $link .= "</li><div id='regform'></div>\n";
                    $aSurveyLinkList[]=$link;
                }
            }

            if(count($aSurveyLinkList) < 1)
            {
                $aSurveyLinkList[]="<li class='surveytitle'>".$clang->gT("No available surveys")."</li>";
            }
            if(!$surveyid)
            {
                $thissurvey['name']=Yii::app()->getConfig("sitename");
                $nosid=$clang->gT("You have not provided a survey identification number");
            }
            else
            {
                $thissurvey['name']=$clang->gT("The survey identification number is invalid");
                $nosid=$clang->gT("The survey identification number is invalid");
            }
            $surveylist=array(
            "nosid"=>$nosid,
            "contact"=>sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),Yii::app()->getConfig("siteadminname"),encodeEmail(Yii::app()->getConfig("siteadminemail"))),
            "listheading"=>$clang->gT("The following surveys are available:"),
            "list"=>implode("\n",$aSurveyLinkList),
            );

            $thissurvey['templatedir'] = $defaulttemplate;

            $data['thissurvey'] = $thissurvey;
            //$data['privacy'] = $privacy;
            $data['surveylist'] = $surveylist;
            $data['surveyid'] = $surveyid;
            $data['templatedir'] = getTemplatePath($defaulttemplate);
            $data['templateurl'] = getTemplateURL($defaulttemplate)."/";
            $data['templatename'] = $defaulttemplate;
            $data['sitename'] = Yii::app()->getConfig("sitename");
            $data['languagechanger'] = makeLanguageChanger($sDisplayLanguage);

            //A nice exit
            sendCacheHeaders();
            doHeader();
            $this->_printTemplateContent(getTemplatePath($defaulttemplate)."/startpage.pstpl", $data, __LINE__);

            $this->_printTemplateContent(getTemplatePath($defaulttemplate)."/surveylist.pstpl", $data, __LINE__);

            echo '<script type="text/javascript" >
            function sendreq(surveyid)
            {

            $.ajax({
            type: "GET",
            url: "'.$this->getController()->createUrl("/register/ajaxregisterform/surveyid").'/" + surveyid,
            }).done(function(msg) {
            document.getElementById("regform").innerHTML = msg;
            });
            }
            </script>';

            $this->_printTemplateContent(getTemplatePath(Yii::app()->getConfig("defaulttemplate"))."/endpage.pstpl", $data, __LINE__);
            doFooter();
            exit;
        }

        // Get token
        if (!isset($token))
        {
            $token=$clienttoken;
        }

        //GET BASIC INFORMATION ABOUT THIS SURVEY
        $thissurvey=getSurveyInfo($surveyid, $_SESSION['survey_'.$surveyid]['s_lang']);

        //SEE IF SURVEY USES TOKENS
        if ($surveyExists == 1 && Survey::model()->hasTokens($surveyid))
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
            $thistpl = getTemplatePath($defaulttemplate);
        }
        else
        {
            $thistpl = getTemplatePath($thissurvey['templatedir']);
        }

        $timeadjust = Yii::app()->getConfig("timeadjust");
        //MAKE SURE SURVEY HASN'T EXPIRED
        if ($thissurvey['expiry']!='' and dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)>$thissurvey['expiry'] && $thissurvey['active']!='N')
        {
            $redata = compact(array_keys(get_defined_vars()));
            $aMessage = array(
            $clang->gT("Error"),
            $clang->gT("This survey is no longer available."),
            sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
            );

            $this->_niceExit($redata, __LINE__, $thissurvey['templatedir'], $aMessage);
        }

        //MAKE SURE SURVEY IS ALREADY VALID
        if ($thissurvey['startdate']!='' and  dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)<$thissurvey['startdate'] && $thissurvey['active']!='N')
        {
            $redata = compact(array_keys(get_defined_vars()));
            $aMessage = array(
            $clang->gT("Error"),
            $clang->gT("This survey is not yet started."),
            sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
            );

            $this->_niceExit($redata, __LINE__, $thissurvey['templatedir'], $aMessage);
        }

        //CHECK FOR PREVIOUSLY COMPLETED COOKIE
        //If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
        $sCookieName="LS_".$surveyid."_STATUS";
        if (isset($_COOKIE[$sCookieName]) && $_COOKIE[$sCookieName] == "COMPLETE" && $thissurvey['usecookie'] == "Y" && $tokensexist != 1 && (!isset($param['newtest']) || $param['newtest'] != "Y"))
        {
            $redata = compact(array_keys(get_defined_vars()));
            $aMessage = array(
            $clang->gT("Error"),
            $clang->gT("You have already completed this survey."),
            sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])
            );

            $this->_niceExit($redata, __LINE__, $thissurvey['templatedir'], $aMessage);
        }

        if (isset($_GET['loadall']) && $_GET['loadall'] == "reload")
        {
            if (returnGlobal('loadname') && returnGlobal('loadpass'))
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
            if (function_exists("ImageCreate") && isCaptchaEnabled('saveandloadscreen',$thissurvey['usecaptcha']))
            {
                if ( (!isset($_POST['loadsecurity']) ||
                !isset($_SESSION['survey_'.$surveyid]['secanswer']) ||
                $_POST['loadsecurity'] != $_SESSION['survey_'.$surveyid]['secanswer']) &&
                !isset($_GET['scid']))
                {
                    $errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
                }
            }

            // Load session before loading the values from the saved data
            if (isset($_GET['loadall']))
            {
                buildsurveysession($surveyid);
            }

            $_SESSION['survey_'.$surveyid]['holdname'] = $param['loadname']; //Session variable used to load answers every page.
            $_SESSION['survey_'.$surveyid]['holdpass'] = $param['loadpass']; //Session variable used to load answers every page.

            if ($errormsg == "") loadanswers();
            $move = "movenext";

            if ($errormsg)
            {
                $_POST['loadall'] = $clang->gT("Load unfinished survey");
            }
        }
        //Allow loading of saved survey
        if (isset($_POST['loadall']) && $_POST['loadall'] == $clang->gT("Load unfinished survey"))
        {
            $redata = compact(array_keys(get_defined_vars()));
            Yii::import("application.libraries.Load_answers");
            $tmp = new Load_answers();
            $tmp->run($redata);
        }


        //Check if TOKEN is used for EVERY PAGE
        //This function fixes a bug where users able to submit two surveys/votes
        //by checking that the token has not been used at each page displayed.
        // bypass only this check at first page (Step=0) because
        // this check is done in buildsurveysession and error message
        // could be more interresting there (takes into accound captcha if used)
        if ($tokensexist == 1 && isset($token) && $token &&
        isset($_SESSION['survey_'.$surveyid]['step']) && $_SESSION['survey_'.$surveyid]['step']>0)
        {
            //check if tokens actually haven't been already used
            $areTokensUsed = usedTokens(trim(strip_tags(returnGlobal('token'))),$surveyid);
            // check if token actually does exist
            // check also if it is allowed to change survey after completion
            if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
                $sQuery = "SELECT * FROM {{tokens_".$surveyid."}} WHERE token='".$token."'";
            } else {
                $sQuery = "SELECT * FROM {{tokens_".$surveyid."}} WHERE token='".$token."' AND (completed = 'N' or completed='')";
            }
            
            $aRow = Yii::app()->db->createCommand($sQuery)->queryRow();
            $tokendata = $aRow; 
            if (!$aRow || ($areTokensUsed && $thissurvey['alloweditaftercompletion'] != 'Y'))
            {
                sendCacheHeaders();
                doHeader();
                //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                $redata = compact(array_keys(get_defined_vars()));
                $this->_printTemplateContent($thistpl.'/startpage.pstpl', $redata, __LINE__);
                $this->_printTemplateContent($thistpl.'/survey.pstpl', $redata, __LINE__);
                $aMessage = array(
                null,
                $clang->gT("This is a controlled survey. You need a valid token to participate."),
                sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']." (<a href='mailto:{$thissurvey['adminemail']}'>"."{$thissurvey['adminemail']}</a>)")
                );

                $this->_niceExit($redata, __LINE__, $thistpl, $aMessage, true);
            }
        }
        if ($tokensexist == 1 && isset($token) && $token) //check if token is in a valid time frame
        {
            // check also if it is allowed to change survey after completion
            if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
                $condition = '';
            } else {
                $condition = "(completed = 'N' or completed='')";
            }
            $tkresult = Tokens_dynamic::model($surveyid)->findAllByAttributes(array('token'=>$token), $condition);
            if (count($tkresult)>0) {
                $tokendata = array_shift($tkresult);
            } else {
                $tokendata = array();
            }
            if (isset($tokendata['validfrom']) && (trim($tokendata['validfrom'])!='' && $tokendata['validfrom']>dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)) ||
            isset($tokendata['validuntil']) && (trim($tokendata['validuntil'])!='' && $tokendata['validuntil']<dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)))
            {
                sendCacheHeaders();
                doHeader();
                //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                $redata = compact(array_keys(get_defined_vars()));
                $this->_printTemplateContent($thistpl.'/startpage.pstpl', $redata, __LINE__);
                $this->_printTemplateContent($thistpl.'/survey.pstpl', $redata, __LINE__);

                $aMessage = array(
                null,
                $clang->gT("We are sorry but you are not allowed to enter this survey."),
                $clang->gT("Your token seems to be valid but can be used only during a certain time period."),
                sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']." (<a href='mailto:{$thissurvey['adminemail']}'>"."{$thissurvey['adminemail']}</a>)")
                );

                $this->_niceExit($redata, __LINE__, $thistpl, $aMessage, true);
            }
        }


        //Clear session and remove the incomplete response if requested.
        if (isset($move) && $move == "clearall")
        {
            // delete the response but only if not already completed
            $s_lang = $_SESSION['survey_'.$surveyid]['s_lang'];
            if (isset($_SESSION['survey_'.$surveyid]['srid']) && !Survey_dynamic::model($surveyid)->isCompleted($_SESSION['survey_'.$surveyid]['srid']))
            {
                // delete the response but only if not already completed
                $oResult= dbExecuteAssoc('DELETE FROM {{survey_'.$surveyid.'}} WHERE id='.$_SESSION['survey_'.$surveyid]['srid']." AND submitdate IS NULL");
                if($oResult->count()>0){ // Using count() here *should* be okay for MSSQL because it is a delete statement
                    // find out if there are any fuqt questions - checked
                    $fieldmap = createFieldMap($surveyid,false,false,$s_lang);
                    foreach ($fieldmap as $q)
                    {
                        if (is_a($q, 'QuestionModule') && $q->fileUpload() && !strpos($q->fieldname, "_filecount"))
                        {
                            if (!isset($questions)) { $questions = array(); }
                            $qid[] = $q;
                        }
                    }

                    // if yes, extract the response json to those questions
                    if (isset($qid))
                    {
                        $sQuery = "SELECT * FROM {{survey_".$surveyid."}} WHERE id=".$_SESSION['survey_'.$surveyid]['srid'];
                        $oResult = dbExecuteAssoc($sQuery);
                        foreach($oResult->readAll() as $row)
                        {
                            foreach ($qid as $question)
                            {
                                $json = $row[$q->fieldname];
                                if ($json == "" || $json == NULL)
                                    continue;

                                // decode them
                                $phparray = json_decode($json);

                                foreach ($phparray as $metadata)
                                {
                                    $target = Yii::app()->getConfig("uploaddir")."/surveys/".$surveyid."/files/";
                                    // delete those files
                                    unlink($target.$metadata->filename);
                                }
                            }
                        }
                    }
                    // done deleting uploaded files
                }

                // also delete a record from saved_control when there is one
                dbExecuteAssoc('DELETE FROM {{saved_control}} WHERE srid='.$_SESSION['survey_'.$surveyid]['srid'].' AND sid='.$surveyid);
            }
            killSurveySession($surveyid);
            sendCacheHeaders();
            doHeader();

            $redata = compact(array_keys(get_defined_vars()));
            $this->_printTemplateContent($thistpl.'/startpage.pstpl', $redata, __LINE__);
            echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
            ."\t<script type='text/javascript'>\n"
            ."\t<!--\n"
            ."function checkconditions(value, name, type, evt_type)\n"
            ."\t{\n"
            ."\t}\n"
            ."\t//-->\n"
            ."\t</script>\n\n";

            //Present the clear all page using clearall.pstpl template
            $this->_printTemplateContent($thistpl.'/clearall.pstpl', $redata, __LINE__);

            $this->_niceExit($redata, __LINE__, $thistpl);
        }


        //Check to see if a refering URL has been captured.
        if (!isset($_SESSION['survey_'.$surveyid]['refurl']))
        {
            $_SESSION['survey_'.$surveyid]['refurl']=GetReferringUrl(); // do not overwrite refurl
        }

        // Let's do this only if
        //  - a saved answer record hasn't been loaded through the saved feature
        //  - the survey is not anonymous
        //  - the survey is active
        //  - a token information has been provided
        //  - the survey is setup to allow token-response-persistence

        if (!isset($_SESSION['srid']) && $thissurvey['anonymized'] == "N" && $thissurvey['active'] == "Y" && isset($token) && $token !='')
        {
            // load previous answers if any (dataentry with nosubmit)
            $sQuery="SELECT id,submitdate,lastpage FROM {$thissurvey['tablename']} WHERE {$thissurvey['tablename']}.token='{$token}' order by id desc";
            $aRow = Yii::app()->db->createCommand($sQuery)->queryRow();
            if ( $aRow )
            {
                if(($aRow['submitdate']==''  && $thissurvey['tokenanswerspersistence'] == 'Y' )|| ($aRow['submitdate']!='' && $thissurvey['alloweditaftercompletion'] == 'Y'))
                {
                    $_SESSION['survey_'.$surveyid]['srid'] = $aRow['id'];
                    if (!is_null($aRow['lastpage']) && $aRow['submitdate']=='')
                    {
                        $_SESSION['survey_'.$surveyid]['LEMtokenResume'] = true;
                        $_SESSION['survey_'.$surveyid]['step'] = $aRow['lastpage'];
                    }
                }
                buildsurveysession();
                loadanswers();
            }        
        }

        //        // SAVE POSTED ANSWERS TO DATABASE IF MOVE (NEXT,PREV,LAST, or SUBMIT) or RETURNING FROM SAVE FORM
        //        if (isset($move) || isset($_POST['saveprompt']))
        //        {
        //            $redata = compact(array_keys(get_defined_vars()));
        //            //save.php
        //            Yii::import("application.libraries.Save");
        //            $tmp = new Save();
        //            $tmp->run($redata);
        //
        //            // RELOAD THE ANSWERS INCASE SOMEONE ELSE CHANGED THEM
        //            if ($thissurvey['active'] == "Y" &&
        //            ( $thissurvey['allowsave'] == "Y" || $thissurvey['tokenanswerspersistence'] == "Y") )
        //            {
        //                loadanswers();
        //            }
        //        }

        if (isset($param['action']) && $param['action'] == 'previewgroup')
        {
            $thissurvey['format'] = 'G';
            buildsurveysession($surveyid,true);
        }

        if (isset($param['action']) && $param['action'] == 'previewquestion')
        {
            $thissurvey['format'] = 'S';
            buildsurveysession($surveyid,true);
        }

        sendCacheHeaders();

        //Send local variables to the appropriate survey type
        unset($redata);
        $redata = compact(array_keys(get_defined_vars()));
        Yii::import('application.helpers.SurveyRuntimeHelper');
        $tmp = new SurveyRuntimeHelper();
        $tmp->run($surveyid,$redata);

        if (isset($_POST['saveall']) || isset($flashmessage))
        {
            echo "<script type='text/javascript'> $(document).ready( function() { alert('".$clang->gT("Your responses were successfully saved.","js")."');}) </script>";
        }
    }

    function _getParameters($args = array(), $post = array())
    {
        $param = array();
        if(@$args[0]==__CLASS__) array_shift($args);
        if(count($args)%2 == 0) {
            for ($i = 0; $i < count($args); $i+=2) {
                //Sanitize input from URL with returnGlobal
                $param[$args[$i]] = returnGlobal($args[$i], $args[$i+1]);
            }
        }

        if( !isset($param['lang']) )
            $param['lang'] = returnGlobal('lang');
        if( !isset($param['action']) )
            $param['action'] = returnGlobal('action');
        if( !isset($param['newtest']) )
            $param['newtest'] = returnGlobal('newtest');
        if( !isset($param['qid']) )
            $param['qid'] = returnGlobal('qid');
        if( !isset($param['gid']) )
            $param['gid'] = returnGlobal('gid');
        if ( !isset($param['sid']) )
            $param['sid'] = returnGlobal('sid');
        if ( !isset($param['loadname']) )
            $param['loadname'] = returnGlobal('loadname');
        if ( !isset($param['loadpass']) )
            $param['loadpass'] = returnGlobal('loadpass');
        if ( !isset($param['scid']) )
            $param['scid'] = returnGlobal('scid');
        if ( !isset($param['thisstep']) )
            $param['thisstep'] = returnGlobal('thisstep');
        if ( !isset($param['move']) )
            $param['move'] = returnGlobal('move');
        if ( !isset($param['token']) )
            $param['token'] = returnGlobal('token');

        if ( !isset($param['thisstep']) )
            $param['thisstep'] = '';

        return $param;
    }

    function _loadRequiredHelpersAndLibraries()
    {
        //Load helpers, libraries and config vars
        Yii::app()->loadHelper("database");
        Yii::app()->loadHelper("frontend");
        Yii::app()->loadHelper("surveytranslator");
    }

    function _loadLimesurveyLang($mvSurveyIdOrBaseLang)
    {
        if ( is_int($mvSurveyIdOrBaseLang) )
        {
            $baselang = Survey::model()->findByPk($surveyId)->language;
        }
        elseif (!empty($mvSurveyIdOrBaseLang))
        {
            $baselang = $mvSurveyIdOrBaseLang;
        }
        else
        {
            $baselang = Yii::app()->getConfig('defaultlang');
        }

        Yii::import("application.libraries.Limesurvey_lang");

        return new Limesurvey_lang($baselang);
    }

    function _surveyExistsAndIsActive($surveyId)
    {
        $isSurveyActive = false;
        $surveyExists = false;

        if ($surveyId)
        {
            $aRow = dbExecuteAssoc("SELECT active FROM {{surveys}} WHERE sid='".$surveyId."'")->read();
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


    function _isClientTokenDifferentFromSessionToken($clientToken, $surveyid)
    {
        return $clientToken != '' && isset($_SESSION['survey_'.$surveyid]['token']) && $clientToken != $_SESSION['survey_'.$surveyid]['token'];
    }

    function _isSurveyFinished($surveyid)
    {
        return isset($_SESSION['survey_'.$surveyid]['finished']) && $_SESSION['survey_'.$surveyid]['finished'] === true;
    }

    function _isPreviewAction($param = array())
    {
        return isset($param['action']) && ($param['action'] == 'previewgroup' || $param['action'] == 'previewquestion');
    }

    function _surveyCantBeViewedWithCurrentPreviewAccess($surveyid, $bIsSurveyActive, $bSurveyExists)
    {
        $bSurveyPreviewRequireAuth = Yii::app()->getConfig('survey_preview_admin_only');
        return $surveyid && $bIsSurveyActive === false && $bSurveyExists && isset($bSurveyPreviewRequireAuth) && $bSurveyPreviewRequireAuth == true &&  !$this->_canUserPreviewSurvey($surveyid);
    }

    function _didSessionTimeout()
    {
        return !isset($_SESSION['survey_'.$surveyid]['s_lang']);
    }

    function _canUserPreviewSurvey($iSurveyID)
    {
        if ( !isset($_SESSION['loginID'], $_SESSION['USER_RIGHT_SUPERADMIN']) )
            return false;

        if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1 )
            return true;

        $sQuery = "SELECT uid
        FROM {{survey_permissions}}
        WHERE sid = ".$iSurveyID." AND uid = ".$_SESSION['loginID'];
        $aRow = Yii::app()->db->createCommand($sQuery)->queryRow();
        if ( $aRow )
            return true;
        return false;
    }

    function _userHasPreviewAccessSession($iSurveyID){
        return (isset($_SESSION['USER_RIGHT_PREVIEW']) && ($_SESSION['USER_RIGHT_PREVIEW'] == $iSurveyID));
    }

    function _niceExit(&$redata, $iDebugLine, $sTemplateDir = null, $asMessage = array())
    {
        if ( $sTemplateDir === null )
            $sTemplateDir = Yii::app()->getConfig("standardtemplaterootdir").DIRECTORY_SEPARATOR.'default';

        sendCacheHeaders();

        doHeader();

        $this->_printTemplateContent($sTemplateDir.DIRECTORY_SEPARATOR.'startpage.pstpl', $redata, $iDebugLine);
        $this->_printMessage($asMessage);
        $this->_printTemplateContent($sTemplateDir.DIRECTORY_SEPARATOR.'endpage.pstpl', $redata, $iDebugLine);

        doFooter();

        exit;
    }

    function _createNewUserSessionAndRedirect($surveyId, &$redata, $iDebugLine, $asMessage = array())
    {
        $clang = Yii::app()->lang;
        killSurveySession($surveyId);
        // Let's redirect the client to the same URL after having reset the session
        $this->_niceExit($redata, $iDebugLine, null, $asMessage);
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


}
