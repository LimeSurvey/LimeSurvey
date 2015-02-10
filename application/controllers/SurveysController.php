<?php

    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends LSYii_Controller
    {
        public $layout = 'bare';
        public $defaultAction = 'publicList';

        public function accessRules() {
            return array_merge([
                ['allow', 'actions' => ['index'], 'users' => ['@']],
                ['allow', 'actions' => ['publicList']],
                ['deny']
            ], parent::accessRules());
        }
        public function actionOrganize($surveyId)
        {
            $this->layout = 'main';
            $groups = QuestionGroup::model()->findAllByAttributes(array(
                'sid' => $surveyId
            ));
            $this->render('organize', compact('groups'));
        }

        public function actionIndex() {
            $this->layout = 'main';
            $surveys = getSurveyList(true);
            $this->render('index', ['surveys' => $surveys]);
        }

        public function actionPublicList($sLanguage = null)
        {
            $this->sessioncontrol();
            if (isset($sLanguage))
            {
                App()->setLanguage($sLanguage);
            }

            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

            ));
        }
        
        public function filters()
        {
            return array_merge(parent::filters(), ['accessControl']);
        }
    }
?>
