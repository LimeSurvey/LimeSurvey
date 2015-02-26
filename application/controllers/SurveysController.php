<?php
namespace ls\controllers;
use Survey;
    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends Controller
    {
        public $layout = 'minimal';
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
            $this->render('index', ['surveys' => new \CActiveDataProvider(Survey::model()->accessible())]);
        }

        public function actionPublicList()
        {
            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

            ));
        }
        
        public function actionView($id) {
            $this->layout = 'survey';
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
            } elseif (!App()->user->checkAccess('survey', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $id])) {
                throw new CHttpException(403);
            }
            return $survey;
        }
    }
?>
