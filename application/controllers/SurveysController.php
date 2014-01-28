<?php

    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends LSYii_Controller
    {
        public $layout = 'bare';
        public $defaultAction = 'publicList';
        public function actionPublicList($lang = null)
        {
            $this->sessioncontrol();
            if (isset($lang))
            {
                App()->setLang(new Limesurvey_lang($lang));
            }

            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

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

            Yii::import('application.libraries.Limesurvey_lang');
            Yii::app()->setLang(new Limesurvey_lang(Yii::app()->session['adminlang']));
        }
    }
?>