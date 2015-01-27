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

		public function actionOrganize($surveyId)
		{
			Group::model()->with('questions')->findAllByAttributes(array(
				'sid' => $surveyId
			));
		}

        /**
         * Load and set session vars
         * @todo Remove this ugly code. Language settings should be moved to Application instead of Controller.
         * @access protected
         * @return void
         */
        protected function sessioncontrol()
        {
			if (!Yii::app()->session["adminlang"] || Yii::app()->session["adminlang"]=='')
                Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

            Yii::app()->setLanguage(Yii::app()->session['adminlang']);
        }
    }
?>
