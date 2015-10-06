<?php
use ls\models\Survey;
use ls\models\Template;

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
        $aLSJavascriptVar= [];
        $aLSJavascriptVar['bFixNumAuto']=(int)(bool)Yii::app()->getConfig('bFixNumAuto',1);
        $aLSJavascriptVar['bNumRealValue']=(int)(bool)Yii::app()->getConfig('bNumRealValue',0);
        $aLangData=\ls\helpers\SurveyTranslator::getLanguageData();
        $aRadix=\ls\helpers\SurveyTranslator::getRadixPointData($aLangData[ Yii::app()->getConfig('defaultlang')]['radixpoint']);
        $aLSJavascriptVar['sLEMradix']=$aRadix['separator'];
        $sLSJavascriptVar="LSvar=".json_encode($aLSJavascriptVar) . ';';
        $clientScript = App()->clientScript;
        $clientScript->registerScript('sLSJavascriptVar',$sLSJavascriptVar,CClientScript::POS_HEAD);

        $clientScript->registerPackage('jqueryui');
        $clientScript->registerPackage('jquery-touch-punch');

        $clientScript->registerPackage('SurveyRuntime');

        ob_start(function($buffer, $phase) use ($clientScript) {
            $clientScript->render($buffer);
            $clientScript->reset();
            return $buffer;
        });
        ob_implicit_flush(false);
        $this->action();
        ob_flush();
    }

    public function action()
    {
        bP();
        $request = App()->request;
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

        $validMoves = ['default','movenext','movesubmit','moveprev','saveall','loadall','clearall','changelang'];
        // We can control is save and load are OK : todo fix according to survey settings
        // Maybe allow $aAcceptedMove in Plugin
        $move = $request->getParam('move');
        foreach($validMoves as $validMove)
        {
            if($request->getParam($validMove))
                $move = $validMove;
        }
        if($move=='default')
        {
            $session = App()->surveySessionManager->current;
            $iSessionStep = $session->step;
            $iSessionTotalSteps = $session->totalSteps;
            if ($iSessionStep > 0 && ($iSessionStep == $iSessionTotalSteps) || $session->format == Survey::FORMAT_ALL_IN_ONE)
            {
                $move="movesubmit";
            }
            else
            {
                $move="movenext";
            }
        }

        if ($session->isFinished
            && (
                !$session->survey->bool_alloweditaftercompletion
                || !$session->survey->bool_tokenanswerspersistence)
        ) {
            $params = [
                "surveys/start",
                'id' => $session->surveyId,
            ];
            if (!empty($session->response->token)) {
                $params['token'] = $session->response->token;
            }
            $this->controller->redirect($params);
        }



        //CHECK FOR REQUIRED INFORMATION (sid)
        $event = new PluginEvent('beforeSurveyPage');
        $event->set('surveyId', $session->surveyId);
        $event->dispatch();
        if (!is_null($event->get('template'))) {
            $session->templateDir = $event->get('template');
        }

        sendCacheHeaders();
        eP();
        //Send local variables to the appropriate survey type
        Yii::import('application.helpers.SurveyRuntimeHelper');
        bP('runtimehelper');
        (new \ls\helpers\SurveyRuntime())->run($session, $move);
        eP('runtimehelper');
        if (isset($_POST['saveall']) || isset($flashmessage))
        {
            echo "<script type='text/javascript'> $(document).ready( function() { alert('".gT("Your responses were successfully saved.","js")."');}) </script>";
        }
    }

    function _getParameters($args = [], $post = [])
    {
        $param = [];
        if(@$args[0]==__CLASS__) array_shift($args);
        if(count($args)%2 == 0) {
            for ($i = 0; $i < count($args); $i+=2) {
                //Sanitize input from URL with returnGlobal
                $param[$args[$i]] = returnGlobal($args[$i], true);
            }
        }

        // Need some $param (else PHP notice)
        foreach(['lang','action','newtest','qid','gid','sid','loadname','loadpass','scid','thisstep','move','token'] as $sNeededParam)
        {
            $param[$sNeededParam]=returnGlobal($sNeededParam,true);
        }

        return $param;
    }

    protected function _loadRequiredHelpersAndLibraries()
    {
        //Load helpers, libraries and config vars
        App()->loadHelper("database");
    }


    function _surveyCantBeViewedWithCurrentPreviewAccess($id, $bIsSurveyActive)
    {
        $bSurveyPreviewRequireAuth = \ls\models\SettingGlobal::get('surveyPreview_require_Auth');
        return $id && $bIsSurveyActive === false && isset($bSurveyPreviewRequireAuth) && $bSurveyPreviewRequireAuth == true &&  !$this->_canUserPreviewSurvey($id);
    }

    function _canUserPreviewSurvey($iSurveyID)
    {
       return App()->user->checkAccess('surveycontent', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $iSurveyID]);
    }

    function _niceExit(&$redata, $iDebugLine, $sTemplateDir = null, $asMessage = [])
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

    function _createNewUserSessionAndRedirect($surveyid, &$redata, $iDebugLine, $asMessage = [])
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
        echo \ls\helpers\Replacements::templatereplace(file_get_contents($sTemplateFile), [], $redata, 'survey[' . $iDebugLine . ']');
    }


}

/* End of file survey.php */
/* Location: ./application/controllers/survey.php */
