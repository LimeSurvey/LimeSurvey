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

        public function actionPublicList()
        {
            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

            ));
        }
        
        public function actionView($id) {
            $this->layout = 'main';
            $survey = $this->loadModel($id);
            $this->survey = $survey;
            $this->render('view', ['survey' => $survey]);
        }
        
        public function filters()
        {
            return array_merge(parent::filters(), ['accessControl']);
        }
        
        protected function loadModel($id) {
            $survey = Survey::model()->findByPk($id);
            if (!isset($survey)) {
                throw new \CHttpException(404, "Survey not found.");
            }
            
            return $survey;
        }
    }
?>
