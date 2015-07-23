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
		/*
		 * Instead of manually rendering scripts after this function returns we
		 * use the callback. This ensures that scripts are always rendered, even
		 * if we call exit at some point in the code. (Which we shouldn't, but
		 * it happens.)
		 */
        // Ensure to set some var, but script are replaced in SurveyRuntimeHelper
        $aLSJavascriptVar=array();
        $aLSJavascriptVar['bFixNumAuto']=(int)(bool)Yii::app()->getConfig('bFixNumAuto',1);
        $aLSJavascriptVar['bNumRealValue']=(int)(bool)Yii::app()->getConfig('bNumRealValue',0);
        $aLangData=getLanguageData();
        $aRadix=getRadixPointData($aLangData[ Yii::app()->getConfig('defaultlang')]['radixpoint']);
        $aLSJavascriptVar['sLEMradix']=$aRadix['separator'];
        $sLSJavascriptVar="LSvar=".json_encode($aLSJavascriptVar) . ';';
        App()->clientScript->registerScript('sLSJavascriptVar',$sLSJavascriptVar,CClientScript::POS_HEAD);
        App()->clientScript->registerScript('setJsVar',"setJsVar();",CClientScript::POS_BEGIN);// Ensure all js var is set before rendering the page (User can click before $.ready)

        App()->getClientScript()->registerPackage('jqueryui');
        App()->getClientScript()->registerPackage('jquery-touch-punch');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."survey_runtime.js");

        ob_start(function($buffer, $phase) {
            App()->getClientScript()->render($buffer);
            App()->getClientScript()->reset();
            return $buffer;
        });
        ob_implicit_flush(false);
        call_user_func_array([$this, 'action'], func_get_args());
        ob_flush();
    }

    function action()
    {
        // only attempt to change session lifetime if using a DB backend
        // with file based sessions, it's up to the admin to configure maxlifetime
        if(isset(App()->session->connectionID)) {
            @ini_set('session.gc_maxlifetime', Yii::app()->getConfig('iSessionExpirationTime'));
        }

        $this->_loadRequiredHelpersAndLibraries();

        $param = $this->_getParameters(func_get_args(), $_POST);

        if (null === $session = App()->surveySessionManager->current) {
            throw new \CHttpException(404, "Session not found");
        }

        header('X-ResponseId: ' . $session->responseId);
        $thisstep = $param['thisstep'];

        $move=getMove();

        $standardtemplaterootdir = SettingGlobal::get('standardtemplaterootdir');
        // unused vars in this method (used in methods using compacted method vars)
        @$loadname = $param['loadname'];
        @$loadpass = $param['loadpass'];




        if ($session->isFinished && (!$session->survey->bool_alloweditaftercompletion || !$session->survey->bool_tokenanswerspersistence)) // No test for response update
        {
            $params = [
                "surveys/start",
                'id' => $session->surveyId,
            ];
            if (!empty($session->response->token)) {
                $params['token'] = $session->response->token;
            }
            $this->getController()->redirect($params);
        }

        $previewmode=false;
        if (isset($param['action']) && (in_array($param['action'],array('previewgroup','previewquestion'))))
        {
            if(!$this->_canUserPreviewSurvey($session->surveyId))
            {
                $asMessage = array(
                    gT('Error'),
                    gT("We are sorry but you don't have permissions to do this.")
                );
                $this->_niceExit($redata, __LINE__, null, $asMessage);
            }
            else
            {
                if((intval($param['qid']) && $param['action']=='previewquestion')) $previewmode='question';
                if((intval($param['gid']) && $param['action']=='previewgroup')) $previewmode='group';
            }
        }
        Yii::app()->setConfig('previewmode',$previewmode);
        if ($this->_surveyCantBeViewedWithCurrentPreviewAccess($session->surveyId, $session->survey->bool_active)
            && !$this->_userHasPreviewAccessSession($session->surveyId)) {
            throw new \CHttpException(403, gT("We are sorry but you don't have permissions to do this."));
        }


        // recompute $redata since $saved_id used to be a global
        $redata = compact(array_keys(get_defined_vars()));


        //CHECK FOR REQUIRED INFORMATION (sid)
        SetSurveyLanguage($session->surveyId, $session->language);
        LimeExpressionManager::SetPreviewMode($previewmode);

        $event = new PluginEvent('beforeSurveyPage');
        $event->set('surveyId', $session->surveyId);
        App()->getPluginManager()->dispatchEvent($event);
        if (!is_null($event->get('template')))
        {
            $session->templateDir = $event->get('template');
        }

        //SEE IF SURVEY USES TOKENS
        if ($session->survey->bool_usetokens && Token::valid($session->surveyId)) {
            $tokensexist = 1;
        } else {
            $tokensexist = 0;
            unset($_POST['token']);
            unset($param['token']);
            unset($token);
            unset($clienttoken);
        }

        //SET THE TEMPLATE DIRECTORY

        $thistpl = Template::getTemplatePath($session->templateDir);


        $timeadjust = SettingGlobal::get("timeadjust");
        //MAKE SURE SURVEY HASN'T EXPIRED
        if ($session->survey->isExpired && $session->survey->bool_active && !$previewmode) {
            throw new \CHttpException(421, gT("This survey is currently not available."));
        }

        //CHECK FOR PREVIOUSLY COMPLETED COOKIE
        //If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
        $sCookieName="LS_{$session->surveyId}_STATUS";
        if (isset(App()->request->psr7->getCookieParams()[$sCookieName])
            && App()->request->psr7->getCookieParams()[$sCookieName] == "COMPLETE"
            && $session->survey->bool_usecookie && $tokensexist != 1
            && (!isset($param['newtest']) || $param['newtest'] != "Y"))
        {
            throw new \CHttpException(403, gT("You have already completed this survey."));
        }

        //LOAD SAVED SURVEY
        if (App()->request->getParam('loadall') == "reload")
        {
            $errormsg="";
            $sLoadName=Yii::app()->request->getParam('loadname');
            $sLoadPass=Yii::app()->request->getParam('loadpass');
            if ( isset($sLoadName) && !$sLoadName)
            {
                $errormsg .= gT("You did not provide a name")."<br />\n";
            }
            if ( isset($sLoadPass) && !$sLoadPass)
            {
                $errormsg .= gT("You did not provide a password")."<br />\n";
            }


            if ($errormsg == "") {
                LimeExpressionManager::SetDirtyFlag();
                buildsurveysession($session->surveyId);
                if (loadanswers()){
                    $move = "reload";// veyRunTimeHelper use $move in $arg
                } else {
                    $errormsg .= gT("There is no matching saved survey");
                }
            }
        }
        //Allow loading of saved survey
        if (Yii::app()->getConfig('move')=="loadall")
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
		if ($tokensexist == 1
            && isset($token)
            && $token!=""
            && $session->step > 0) {
            // check also if it is allowed to change survey after completion
			if ($session->survey->bool_alloweditaftercompletion) {
				$tokenInstance = Token::model($session->surveyId)->findByAttributes(array('token' => $token));
            } else {
				$tokenInstance = Token::model($session->surveyId)->usable()->incomplete()->findByAttributes(['token' => $token]);
            }

			if (!isset($tokenInstance) && !$previewmode) {
                throw new \CHttpException(404, gT("This is a controlled survey. You need a valid token to participate."));
            }
        }
        if ($tokensexist == 1 && isset($token) && $token!="" && !$previewmode) //check if token is in a valid time frame
        {
            // check also if it is allowed to change survey after completion
            if ($session->survey->bool_alloweditaftercompletion) {
                $tokenInstance = Token::model($session->surveyId)->editable()->findByAttributes(['token' => $token]);
            } else {
                $tokenInstance = Token::model($session->surveyId)->usable()->incomplete()->findByAttributes(['token' => $token]);
            }
            if (!isset($tokenInstance))
            {
                $oToken = Token::model($session->surveyId)->findByAttributes(array('token' => $token));
                if($oToken)
                {
                    $now = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"));
                    if($oToken->completed != 'N' && !empty($oToken->completed))// This can not happen (TokenInstance must fix this)
                    {
                        $sError = gT("This invitation has already been used.");
                    }
                    elseif(strtotime($now) < strtotime($oToken->validfrom))
                    {
                        $sError = gT("This invitation is not valid yet.");
                    }
                    elseif(strtotime($now) > strtotime($oToken->validuntil))
                    {
                        $sError = gT("This invitation is not valid anymore.");
                    }
                    else // This can not happen
                    {
                        $sError = gT("This is a controlled survey. You need a valid token to participate.");
                    }
                }
                else
                {
                    $sError = gT("This is a controlled survey. You need a valid token to participate.");
                }
                throw new \CHttpException(403, gT("We are sorry but you are not allowed to enter this survey."));
            }
        }

        // Let's do this only if
        //  - a saved answer record hasn't been loaded through the saved feature
        //  - the survey is not anonymous
        //  - the survey is active
        //  - a token information has been provided
        //  - the survey is setup to allow token-response-persistence

        if ($session->survey->bool_anonymized && $session->survey->bool_active && isset($token) && $token !='') {
            // load previous answers if any (dataentry with nosubmit)
             $oResponses  = Response::model($session->surveyId)->findAllByAttributes([
                'token' => $token
            ]);
            if (!empty($oResponses))
            {
                /**
                 * We fire the response selection event when at least 1 response was found.
                 * If there is just 1 response the plugin still has to option to choose
                 * NOT to use it.
                 */
                $event = new PluginEvent('beforeLoadResponse');
                $event->set('responses', $oResponses);
                $event->set('surveyId', $session->surveyId);
                App()->pluginManager->dispatchEvent($event);

                $oResponse = $event->get('response');
                // If $oResponse is false we act as if no response was found.
                // This allows a plugin to deny continuing a response.
                if ($oResponse !== false)
                {
                    // If plugin does not set a response we use the first one found, (this replicates pre-plugin behavior)
                    if (!isset($oResponse) && (!isset($oResponses[0]->submitdate) || $thissurvey['alloweditaftercompletion'] == 'Y') && $thissurvey['tokenanswerspersistence'] == 'Y')
                    {
                        $oResponse = $oResponses[0];
                    }

                    if (isset($oResponse))
                    {
                        if (!empty($oResponse->lastpage))
                        {
                            $_SESSION['survey_'.$surveyid]['LEMtokenResume'] = true;
                            // If the response was completed and user is allowed to edit after completion start at the beginning and not at the last page - just makes more sense
                            if (!($oResponse->submitdate && $session->survey->bool_alloweditaftercompletion))
                            {
                                App()->surveySessionManager->current->setStep($oResponse->lastpage);
                            }
                        }
                        buildsurveysession($session->surveyId);
                        if(!empty($oResponse->submitdate)) // alloweditaftercompletion
                        {
                            App()->surveySessionManager->current->maxStep = App()->surveySessionManager->current->getStepCount();
                            App()->surveySessionManager->current->isFinished = true;
                        }
                        loadanswers();
                    }
                }
            }
        }
        // Preview action : Preview right already tested before
        if ($previewmode)
        {
            // Unset all SESSION: be sure to have the last version
            if ($param['action'] == 'previewgroup')
            {
                $thissurvey['format'] = 'G';
            }
            elseif ($param['action'] == 'previewquestion')
            {
                $thissurvey['format'] = 'S';
            }
            buildsurveysession($surveyid,true);
        }

        sendCacheHeaders();

        //Send local variables to the appropriate survey type
        $redata = compact(array_keys(get_defined_vars()));
        Yii::import('application.helpers.SurveyRuntimeHelper');
        bP();
        (new SurveyRuntimeHelper())->run($session->surveyId,$redata);
        eP();
        if (isset($_POST['saveall']) || isset($flashmessage))
        {
            echo "<script type='text/javascript'> $(document).ready( function() { alert('".gT("Your responses were successfully saved.","js")."');}) </script>";
        }
    }

    function _getParameters($args = array(), $post = array())
    {
        $param = array();
        if(@$args[0]==__CLASS__) array_shift($args);
        if(count($args)%2 == 0) {
            for ($i = 0; $i < count($args); $i+=2) {
                //Sanitize input from URL with returnGlobal
                $param[$args[$i]] = returnGlobal($args[$i], true);
            }
        }

        // Need some $param (else PHP notice)
        foreach(array('lang','action','newtest','qid','gid','sid','loadname','loadpass','scid','thisstep','move','token') as $sNeededParam)
        {
            $param[$sNeededParam]=returnGlobal($sNeededParam,true);
        }

        return $param;
    }

    function _loadRequiredHelpersAndLibraries()
    {
        //Load helpers, libraries and config vars
        Yii::app()->loadHelper("database");
        Yii::app()->loadHelper("frontend");
        Yii::app()->loadHelper("surveytranslator");
    }

    function _isClientTokenDifferentFromSessionToken($clientToken, $surveyid)
    {
        return $clientToken != '' && isset($_SESSION['survey_'.$surveyid]['token']) && $clientToken != $_SESSION['survey_'.$surveyid]['token'];
    }

    function _surveyCantBeViewedWithCurrentPreviewAccess($id, $bIsSurveyActive)
    {
        $bSurveyPreviewRequireAuth = \SettingGlobal::get('surveyPreview_require_Auth');
        return $id && $bIsSurveyActive === false && isset($bSurveyPreviewRequireAuth) && $bSurveyPreviewRequireAuth == true &&  !$this->_canUserPreviewSurvey($id);
    }

    function _canUserPreviewSurvey($iSurveyID)
    {
       return App()->user->checkAccess('surveycontent', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $iSurveyID]);
    }

    function _userHasPreviewAccessSession($iSurveyID){
        return (isset($_SESSION['USER_RIGHT_PREVIEW']) && ($_SESSION['USER_RIGHT_PREVIEW'] == $iSurveyID));
    }

    function _niceExit(&$redata, $iDebugLine, $sTemplateDir = null, $asMessage = array())
    {

        if(isset($redata['surveyid']) && $redata['surveyid'] && !isset($thisurvey))
        {
            $thissurvey=getSurveyInfo($redata['surveyid']);
            $sTemplateDir= Template::getTemplatePath($thissurvey['template']);
        }
        else
        {
            $sTemplateDir= Template::getTemplatePath($sTemplateDir);
        }
        sendCacheHeaders();

        doHeader();
        $this->_printTemplateContent($sTemplateDir.'/startpage.pstpl', $redata, $iDebugLine);
        $this->_printMessage($asMessage);
        $this->_printTemplateContent($sTemplateDir.'/endpage.pstpl', $redata, $iDebugLine);

        doFooter();
		exit;
    }

    function _createNewUserSessionAndRedirect($surveyid, &$redata, $iDebugLine, $asMessage = array())
    {

        $thissurvey=getSurveyInfo($surveyid);
        if($thissurvey)
        {
            $templatename=$thissurvey['template'];
        }
        else
        {
            $templatename=Yii::app()->getConfig('defaulttemplate');;
        }
        // Let's redirect the client to the same URL after having reset the session
        $this->_niceExit($redata, $iDebugLine, $templatename, $asMessage);
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

/* End of file survey.php */
/* Location: ./application/controllers/survey.php */
