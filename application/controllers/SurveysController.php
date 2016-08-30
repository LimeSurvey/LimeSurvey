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
            $oTemplate = Template::model()->getInstance(Yii::app()->getConfig("defaulttemplate"));


            if($oTemplate->cssFramework == 'bootstrap')
            {
                // We now use the bootstrap package isntead of the Yiistrap TbApi::register() method
                // Then instead of using the composer dependency system for templates
                // We can use the package dependency system
                Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1.0', 'viewport');
                App()->bootstrap->registerAllScripts();
            }

            $aData = array(
                    'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                    'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),
                );
            $htmlOut = $this->render('publicSurveyList',  $aData,true );

            $event = new PluginEvent('beforeSurveysStartpageRender', $this);
            $event->set('aData', $aData);
            App()->getPluginManager()->dispatchEvent($event);

            if($event->get('result'))
            {
                $htmlFromEvent = $event->get('result');
                $htmlOut = $htmlFromEvent['html'];
            }
            echo $htmlOut;
        }
    }
?>
