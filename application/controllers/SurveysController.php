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
            // Validate if languages exists and fall back to default lang if needed
            $aLanguages = getLanguageDataRestricted(false, 'short');
            if (!isset($aLanguages[ $lang ])) {
                $lang = App()->getConfig('defaultlang');
            }
        } else {
            $lang = App()->getConfig('defaultlang');
        }
            App()->setLanguage($lang);


        $oTemplate       = Template::model()->getInstance(getGlobalSetting('defaulttheme'));
        $this->sTemplate = $oTemplate->sTemplateName;

        $aData = array(
            'publicSurveys'     => Survey::model()->active()->open()->with('languagesettings')->findAllPublic(),
            'futureSurveys'     => Survey::model()->active()->registration()->with('languagesettings')->findAllPublic(),
            'oTemplate'         => $oTemplate,
            'sSiteName'         => Yii::app()->getConfig('sitename'),
            'sSiteAdminName'    => Yii::app()->getConfig("siteadminname"),
            'sSiteAdminEmail'   => Yii::app()->getConfig("siteadminemail"),
            'bShowClearAll'     => false,
            'surveyls_title'    => Yii::app()->getConfig('sitename')
        );

        $aData['alanguageChanger']['show'] = false;
        $alanguageChangerDatas = getLanguageChangerDatasPublicList(App()->language);

        if ($alanguageChangerDatas) {
            $aData['alanguageChanger']['show']  = true;
            $aData['alanguageChanger']['datas'] = $alanguageChangerDatas;
        }

        Yii::app()->clientScript->registerScriptFile(Yii::app()->getConfig("generalscripts") . 'nojs.js', CClientScript::POS_HEAD);

        // maintenance mode
        $sMaintenanceMode = getGlobalSetting('maintenancemode');
        if ($sMaintenanceMode == 'hard' || $sMaintenanceMode == 'soft') {
            Yii::app()->twigRenderer->renderTemplateFromFile("layout_maintenance.twig", array('aSurveyInfo' => $aData), false);
        } else {
            Yii::app()->twigRenderer->renderTemplateFromFile("layout_survey_list.twig", array('aSurveyInfo' => $aData), false);
        }
    }

    /**
     * System error : only 404 error are managed here (2016-11-29)
     * SurveysController is the default controller set in internal
     * @see http://www.yiiframework.com/doc/guide/1.1/en/topics.error#handling-errors-using-an-action
     *
     * @throws CException
     * @throws CHttpException
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     * @throws WrongTemplateVersionException
     */
    public function actionError()
    {
        /** @var array */
        $error = Yii::app()->errorHandler->error;
        $oException = Yii::app()->errorHandler->getException();
        $request = Yii::app()->getRequest();
        if ($error && $request->isAjaxRequest) {
            // TODO: Remove? It seems this can never happen because it's already caught by LSYii_Application::onException() (see commit c792c2e).
            $this->spitOutJsonError($error, $oException);
        } elseif ($error) {
            $this->spitOutHtmlError($error, (int) $request->getParam('sid', $request->getParam('surveyid')));
        } else {
            throw new CHttpException(404, 'Page not found.');
        }
    }

    /**
     * Echo $error as HTML and end execution.
     *
     * @param array $error
     * @param string|null $surveyId
     *
     * @return void
     *
     * @throws CException
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     * @throws WrongTemplateVersionException
     */
    public function spitOutHtmlError(array $error, $surveyId)
    {
        if ($surveyId) {
            $oTemplate = Template::model()->getInstance('', $surveyId);
        } else {
            $oTemplate = Template::getLastInstance();
        }
        $this->sTemplate = $oTemplate->sTemplateName;

        $admin = App()->getConfig('siteadminname');
        if (App()->getConfig('showEmailInError')) {
            // Never show email by default
            $admin = CHtml::mailto(App()->getConfig('siteadminname'), App()->getConfig('siteadminemail'));
        }
        $contact = sprintf(gT('If you think this is a server error, please contact %s.'), $admin);
        switch ($error['code']) {
            case '400':
                /* CRSF issue */
                $title = gT('400: Bad Request');
                $message = gT('The request could not be understood by the server due to malformed syntax.')
                    . ' ' . gT('Please do not repeat the request without modifications.');
                break;
            case '401':
                $title = gT('401: Unauthorized');
                $message = gT('You must be logged in to access to this page.');
                // TODO: Remove comment code.
                // $loginurl = $this->getController()->createUrl("/admin/login")
                // header('WWW-Authenticate: MyAuthScheme  realm="'.$loginurl.'"');
                break;
            case '403':
                $title = gT('403: Forbidden');
                $message = gT('You do not have the permission to access this page.');
                break;
            case '404':
                $title = gT('404: Not Found');
                $message = gT('The requested URL was not found on this server.') . " \n"
                    . gT('If you entered the URL manually please check your spelling and try again.');
                break;
            case '500':
                $title = gT('500: Internal Server Error');
                $message = gT('An internal error occurred while the Web server was processing your request.');
                $contact = sprintf(gT('Please contact %s to report this problem.'), $admin);
                break;
            default:
                $title = sprintf(gT('Error %s'), $error['code']);
                $message = gT('The above error occurred when the Web server was processing your request.');
                break;
        }

        // For CDbException, we clear the message in order to avoid showing sensitive information to the user.
        // This method is not usually executed when debug is enabled, but check anyway to be sure to only
        // suppress the error if debug is disabled.
        if (!YII_DEBUG && isset($error['type']) && $error['type'] == 'CDbException') {
            $error['message'] = gT('Database error!');
        }

        $aError['type'] = $error['code'];
        $aError['error'] = $title;
        if (!empty($error['message'])) {
            $aError['title'] = ' - ' . nl2br(CHtml::encode($error['message']) ?? '');
        }
        $aError['message'] = $message;
        $aError['contact'] = $contact;

        if (App()->getConfig('debug') != 0) {
            $aError['trace'] = $error['trace'];
        }

        $aSurveyInfo['aError'] = $aError;
        App()->twigRenderer->renderTemplateFromFile(
            "layout_errors.twig",
            array('aSurveyInfo' => $aSurveyInfo),
            false
        );
        App()->end();
    }

    /**
     * Echo JSON $error and ends execution.
     *
     * @param array $error
     * @param CException $oException
     *
     * @return void
     *
     * @throws CException
     */
    public function spitOutJsonError(array $error, $oException)
    {
        $dataArray = [
            'data' => [
                'success' => false,
                'message' => $error['message'],
                'error'   => $error,
            ]
        ];

        if ($oException instanceof LSUserException) {
            if ($oException->getRedirectUrl() != null) {
                $dataArray['data']['redirectTo'] = $oException->getRedirectUrl();
            }
            if ($oException->getNoReload() != null) {
                $dataArray['data']['noReload'] = $oException->getNoReload();
            }
        }

        echo App()->getController()->renderPartial(
            '/admin/super/_renderJson',
            $dataArray,
            true,
            false
        );
        App()->end();
    }
}
