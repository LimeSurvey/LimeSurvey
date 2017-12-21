<?php

    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends LSYii_Controller
    {
        /* All this part is for PUBLIC view : maybe move to LSYii_Controller ? */
        /* @var string : Default layout when using render : leave at bare actually : just send content */
        public $layout = 'public';
        /* @var string the template name to be used when using layout */
        public $sTemplate;
        /* @var string[] Replacement data when use templatereplace function in layout, @see templatereplace $replacements */
        public $aReplacementData = array();
        /* @var array Global data when use templatereplace function  in layout, @see templatereplace $redata */
        public $aGlobalData = array();

        public $defaultAction = 'publicList';

        public function actionPublicList($lang = null)
        {
            if (!empty($lang)) {
                // Control is a real language , in restrictToLanguages ?
                App()->setLanguage($lang);
            } else {
                App()->setLanguage(App()->getConfig('defaultlang'));
            }


            $oTemplate       = Template::model()->getInstance(getGlobalSetting('defaulttheme'));
            $this->sTemplate = $oTemplate->sTemplateName;

            $aData = array(
                    'publicSurveys'     => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                    'futureSurveys'     => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),
                    'oTemplate'         => $oTemplate,
                    'sSiteName'         => Yii::app()->getConfig('sitename'),
                    'sSiteAdminName'    => Yii::app()->getConfig("siteadminname"),
                    'sSiteAdminEmail'   => Yii::app()->getConfig("siteadminemail"),
                    'bShowClearAll'     => false,
                );

            $aData['alanguageChanger']['show'] = false;
            $alanguageChangerDatas = getLanguageChangerDatasPublicList(App()->language);

            if ($alanguageChangerDatas) {
                $aData['alanguageChanger']['show']  = true;
                $aData['alanguageChanger']['datas'] = $alanguageChangerDatas;
            }

            Yii::app()->clientScript->registerScriptFile(Yii::app()->getConfig("generalscripts").'nojs.js', CClientScript::POS_HEAD);

            Yii::app()->twigRenderer->renderTemplateFromFile("layout_survey_list.twig", array('aSurveyInfo'=>$aData), false);

        }
        /**
         * System error : only 404 error are managed here (2016-11-29)
         * SurveysController is the default controller set in internal
         * @see http://www.yiiframework.com/doc/guide/1.1/en/topics.error#handling-errors-using-an-action
         */
        public function actionError()
        {
            $oTemplate = Template::model()->getInstance(getGlobalSetting('defaulttheme'));

            $this->sTemplate = $oTemplate->sTemplateName;

            $error = Yii::app()->errorHandler->error;
            if ($error) {
                App()->setConfig('sitename', "Not found");
                $this->render('/system/error'.$error['code'], array('error'=>$error, 'admin'=>encodeEmail(Yii::app()->getConfig("siteadminemail"))));
            } else {
                throw new CHttpException(404, 'Page not found.');
            }
        }

    }
