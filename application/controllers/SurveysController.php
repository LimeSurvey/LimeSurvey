<?php

    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends LSYii_Controller
    {
        /* All this part is for PUBLIC view : maybe move to LSYii_Controller ? */
        /* @var string : Default layout when using render : leave at bare actually : just send content */
        public $layout= 'public';
        /* @var string the template name to be used when using layout */
        public $sTemplate= 'default';
        /* @var string[] Replacement data when use templatereplace function in layout, @see templatereplace $replacements */
        public $aReplacementData= array();
        /* @var array Global data when use templatereplace function  in layout, @see templatereplace $redata */
        public $aGlobalData= array();
        /* @var boolean did we need survey.pstpl when using layout */
        public $bStartSurvey= false;

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

            $this->sTemplate = $oTemplate->name;
            $this->aGlobalData['languagechanger'] = makeLanguageChanger(App()->language);

            $aData = array(
                    'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                    'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),
                );
            $htmlOut = $this->renderPartial('publicSurveyList',  $aData,true );

            $event = new PluginEvent('beforeSurveysStartpageRender', $this);
            $event->set('aData', $aData);
            App()->getPluginManager()->dispatchEvent($event);

            if($event->get('result'))
            {
                $htmlFromEvent = $event->get('result');
                $htmlOut = $htmlFromEvent['html'];
                $this->layout=$event->get('layout',$this->layout); // with bare : directly render whole display, default is to add head/footer etc ... from template
            }
            $this->render("/surveys/display",array('content'=>$htmlOut));
            /**
             * OR
             * $this->render("/survey/system/display",array('content'=>$htmlOut));
             * ? template must be allowed to add content after and before all page ?
             */
            App()->end();
        }
        /**
         * System error : only 404 error are managed here (2016-11-29)
         * SurveysController is the default controller set in internal
         * @see http://www.yiiframework.com/doc/guide/1.1/en/topics.error#handling-errors-using-an-action
         */
        public function actionError()
        {
            $oTemplate = Template::model()->getInstance(Yii::app()->getConfig("defaulttemplate"));

            $this->sTemplate = $oTemplate->name;

            $error = Yii::app()->errorHandler->error;
            if ($error){
                App()->setConfig('sitename',"Not found");
                $this->render('/system/error'.$error['code'], array('error'=>$error,'admin'=>encodeEmail(Yii::app()->getConfig("siteadminemail"))));
            }else{
                throw new CHttpException(404, 'Page not found.');
            }
        }

    }
?>
