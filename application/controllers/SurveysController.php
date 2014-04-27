<?php

    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends LSYii_Controller
    {
        public $layout = 'bare';
        public $defaultAction = 'publicList';

		public function actionOrganize($surveyId)
		{
			$this->layout = 'main';
			$groups = QuestionGroup::model()->findAllByAttributes(array(
				'sid' => $surveyId
			));
			$this->render('organize', compact('groups'));
		}



        public function actionPublicList()
        {
            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

            ));
        }


        
    }
?>