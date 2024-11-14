<?php

/**
 * Class IndexController
 */
class DashboardController extends LSBaseController
{
    /**
     * responses constructor.
     * @param $controller
     * @param $id
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        App()->loadHelper('surveytranslator');
    }

    /**
     * Set filters for all actions
     * @return string[]
     */
    public function filters()
    {
        return [];
    }

    /**
     * this is part of _renderWrappedTemplate implement in old responses.php
     *
     * @param string $view
     * @return bool
     */
    public function beforeRender($view)
    {
        $this->layout = 'with_sidebar';

        return parent::beforeRender($view);
    }

    /**
     * View the dashboard index/index
     */
    public function actionView(): void
    {
        $aData = $this->getData();
        $this->render('welcome', $aData);
    }

    /**
     * Used to get responses data for browse etc
     *
     * @param int|null $surveyId
     * @param int|null $responseId
     * @param string|null $language
     * @return array
     */
    private function getData(): array
    {
        $aData = [];
        $aData['issuperadmin'] = false;
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $aData['issuperadmin'] = true;
        }
        // display old dashboard interface
        $aData['oldDashboard'] = App()->getConfig('display_old_dashboard') === '1';
        $setting_entry = 'last_survey_' . App()->user->getId();
        $lastsurvey = App()->getConfig($setting_entry);
        if ($lastsurvey) {
            try {
                $survey = Survey::model()->findByPk($lastsurvey);
                if ($survey) {
                    $aData['showLastSurvey'] = true;
                    $iSurveyID = $lastsurvey;
                    $aData['surveyTitle'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
                    $aData['surveyUrl'] = $this->createUrl("surveyAdministration/view/surveyid/{$iSurveyID}");
                } else {
                    $aData['showLastSurvey'] = false;
                }
            } catch (Exception $e) {
                $aData['showLastSurvey'] = false;
            }
        } else {
            $aData['showLastSurvey'] = false;
        }

        // We get the last question visited by user
        $setting_entry = 'last_question_' . App()->user->getId();
        $lastquestion = App()->getConfig($setting_entry);

        // the question group of this question
        $setting_entry = 'last_question_gid_' . App()->user->getId();
        $lastquestiongroup = App()->getConfig($setting_entry);

        // the sid of this question : last_question_sid_1
        $setting_entry = 'last_question_sid_' . App()->user->getId();
        $lastquestionsid = App()->getConfig($setting_entry);

        if ($lastquestion && $lastquestiongroup && $lastquestionsid) {
            $survey = Survey::model()->findByPk($lastquestionsid);
            if ($survey) {
                $baselang = $survey->language;
                $aData['showLastQuestion'] = true;
                $qid = $lastquestion;
                $gid = $lastquestiongroup;
                $sid = $lastquestionsid;
                $qrrow = Question::model()->findByAttributes(['qid' => $qid, 'gid' => $gid, 'sid' => $sid]);
                if ($qrrow) {
                    $aData['last_question_name'] = $qrrow['title'];
                    if (!empty($qrrow->questionl10ns[$baselang]['question'])) {
                        $aData['last_question_name'] .= ' : ' . $qrrow->questionl10ns[$baselang]['question'];
                    }
                    $aData['last_question_link'] = $this->createUrl("questionAdministration/view/surveyid/$sid/gid/$gid/qid/$qid");
                } else {
                    $aData['showLastQuestion'] = false;
                }
            } else {
                $aData['showLastQuestion'] = false;
            }
        } else {
            $aData['showLastQuestion'] = false;
        }

        $aData['countSurveyList'] = Survey::model()->count();

        //show banner after welcome logo
        $event = new PluginEvent('beforeWelcomePageRender');
        App()->getPluginManager()->dispatchEvent($event);
        $belowLogoHtml = $event->get('html');

        // We get the home page display setting
        $aData['bShowSurveyList'] = (App()->getConfig('show_survey_list') == "show");
        $aData['bShowSurveyListSearch'] = (App()->getConfig('show_survey_list_search') == "show");
        $aData['bShowLogo'] = (App()->getConfig('show_logo') == "show");
        $aData['oSurveySearch'] = new Survey('search');
        $aData['bShowLastSurveyAndQuestion'] = (App()->getConfig('show_last_survey_and_question') == "show");
        $aData['iBoxesByRow'] = (int)App()->getConfig('boxes_by_row');
        $aData['sBoxesOffSet'] = (int)App()->getConfig('boxes_offset');
        $aData['bBoxesInContainer'] = (App()->getConfig('boxes_in_container') == 'yes');
        $aData['belowLogoHtml'] = $belowLogoHtml;

        return $aData;
    }
}
