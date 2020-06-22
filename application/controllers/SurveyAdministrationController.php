<?php


class SurveyAdministrationController extends LSBaseController
{

    /**
     * It's import to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['view'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    /**
     * This part comes from _renderWrappedTemplate
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = $this->aData['oSurvey'] ?? Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        }

        return parent::beforeRender($view);
    }

    /**
     * Load complete view of survey properties and actions specified by $iSurveyID
     *
     * @param mixed $iSurveyID Given Survey ID
     * @param mixed $gid       Given Group ID
     * @param mixed $qid       Given Question ID
     *
     * @return void
     *
     * @access public
     */
    public function actionView($iSurveyID, $gid = null, $qid = null)
    {
        $beforeSurveyAdminView = new PluginEvent('beforeSurveyAdminView');
        $beforeSurveyAdminView->set('surveyId', $iSurveyID);
        App()->getPluginManager()->dispatchEvent($beforeSurveyAdminView);

        // We load the panel packages for quick actions
        $iSurveyID = sanitize_int($iSurveyID);
        $survey    = Survey::model()->findByPk($iSurveyID);
        $baselang  = $survey->language;

        $aData = array('aAdditionalLanguages' => $survey->additionalLanguages);

        // Reinit LEMlang and LEMsid: ensure LEMlang are set to default lang, surveyid are set to this survey id
        // Ensure Last GetLastPrettyPrintExpression get info from this sid and default lang
        LimeExpressionManager::SetEMLanguage($baselang);
        LimeExpressionManager::SetSurveyId($iSurveyID);
        LimeExpressionManager::StartProcessingPage(false, true);

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['surveyid'] = $iSurveyID;
        $aData['display']['surveysummary'] = true;  //todo this is set because ONLY this leads to render the view surveySummary_view

        // Last survey visited
        $setting_entry = 'last_survey_'.App()->user->getId();
        SettingGlobal::setSetting($setting_entry, $iSurveyID);

        $aData['surveybar']['buttons']['view'] = true;
        $aData['surveybar']['returnbutton']['url'] = $this->createUrl("admin/survey/sa/listsurveys");
        $aData['surveybar']['returnbutton']['text'] = gT('Return to survey list');
        $aData['sidemenu']["survey_menu"] = true;

        // We get the last question visited by user for this survey
        $setting_entry = 'last_question_'.App()->user->getId().'_'.$iSurveyID;
        // TODO: getGlobalSetting() DEPRECATED
        $lastquestion = getGlobalSetting($setting_entry);
        $setting_entry = 'last_question_'.App()->user->getId().'_'.$iSurveyID.'_gid';

        // TODO: getGlobalSetting() DEPRECATED
        $lastquestiongroup = getGlobalSetting($setting_entry);

        if ($lastquestion != null && $lastquestiongroup != null) {
            $aData['showLastQuestion'] = true;
            $iQid = $lastquestion;
            $iGid = $lastquestiongroup;
            $qrrow = Question::model()->findByAttributes(array('qid' => $iQid, 'gid' => $iGid, 'sid' => $iSurveyID));

            $aData['last_question_name'] = $qrrow['title'];
            if (!empty($qrrow->questionl10ns[$baselang]['question'])) {
                $aData['last_question_name'] .= ' : '.$qrrow->questionl10ns[$baselang]['question'];
            }

            $aData['last_question_link'] =
                $this->createUrl("questionEditor/view/surveyid/$iSurveyID/gid/$iGid/qid/$iQid");
        } else {
            $aData['showLastQuestion'] = false;
        }
        $aData['templateapiversion'] = Template::model()->getTemplateConfiguration(null, $iSurveyID)->getApiVersion();

        $user = User::model()->findByPk(App()->session['loginID']);
        $aData['owner'] = $user->attributes;

        if ((empty($aData['display']['menu_bars']['surveysummary']) || !is_string($aData['display']['menu_bars']['surveysummary'])) && !empty($aData['gid'])) {
            $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
        }
        $this->_surveysummary($aData);

        $this->render('survey', [

        ]);
        //$this->_renderWrappedTemplate('survey', array(), $aData);
    }


}