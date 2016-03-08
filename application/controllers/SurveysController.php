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
            if (!empty($lang))// Control is a real language , in restrictToLanguages ?
            {
                App()->setLanguage($lang);
            }
            else
            {
                App()->setLanguage(App()->getConfig('defaultlang'));
            }
            //$oTemplate = Template::model()->getInstance(Yii::app()->getConfig("defaulttemplate"));

            // To avoid to reinstanciate again the object in the helpers, libraries, views
            // we must make $oTemplate global.
            // using a "getInstance" method without parsing the template model from the controllers
            // to the helpers/libraries/view will not resolve magically the problem. It will just create
            // second instance.
            global $oTemplate;            
            $oTemplate = new TemplateConfiguration;
            $oTemplate->setTemplateConfiguration('',$surveyid);

            if($oTemplate->cssFramework == 'bootstrap')
            {
                App()->bootstrap->register();
            }
            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),
            ));
        }
    }
?>
