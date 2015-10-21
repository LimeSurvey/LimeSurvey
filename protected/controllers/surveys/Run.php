<?php


namespace ls\controllers\surveys;

use \Yii;
use \CClientScript;
use ls\models\Survey;
use \PluginEvent;

/**
 * Class Run, runs a survey.
 * @package ls\controllers\surveys
 */
class Run extends \Action
{
    /**
     * Runs the action,
     * @throws \CHttpException
     * @param string $csrfToken
     */
    public function run($csrfToken = null, $button = 'none')
    {

        if (null === $session = App()->surveySessionManager->current) {
            throw new \CHttpException(404, "Session not found");
        }
        if (App()->request->getIsPostRequest() && $csrfToken != $session->postKey) {
            throw new \CHttpException(401, "CSRF Protection triggered.");
        }


        $buttons = [
            'none' => true,
            'default' => true,
            'next' => true,
            'submit' => true,
            'clearall' => true,
            'changelang' => true
        ];

        if (!$session->survey->bool_allowsave) {
            $buttons['saveall'] = true;
            $buttons['loadall'] = true;
        }

        if (count($session->survey->allLanguages) == 1) {
            unset($buttons['changelang']);
        }
        if ($session->survey->bool_allowprev) {
            $buttons['prev'] =  true;
        }
        if (!isset($buttons[$button])) {
            throw new \CHttpException(400, "Invalid move type.");
        }



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


        $clientScript->registerPackage('SurveyRuntime');

        ob_start();
        header('X-ResponseId: ' . $session->responseId);


        if ($session->isFinished && !$session->survey->bool_alloweditaftercompletion) {
            throw new \CHttpException(403, "This response has been finished, you are not allowed to edit it after completion.");
        }


        $event = new PluginEvent('beforeSurveyPage');
        $event->set('surveyId', $session->surveyId);
        $event->dispatch();
        if (!is_null($event->get('template'))) {
            $session->templateDir = $event->get('template');
        }

        //Send local variables to the appropriate survey type
        bP('runtimehelper');
        (new \ls\helpers\SurveyRuntime())->run($session, $button);
        eP('runtimehelper');
        $result = ob_get_clean();
        $clientScript->render($result);
        $clientScript->reset();
        echo $result;
    }

}