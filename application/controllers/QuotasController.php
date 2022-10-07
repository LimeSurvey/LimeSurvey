<?php

class QuotasController extends LSBaseController
{
    /**
     * Here we have to use the correct layout (NOT main.php)
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        $this->layout = 'layout_questioneditor';
        LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
        LimeExpressionManager::StartProcessingPage(false, true);

        return parent::beforeRender($view);
    }


    public function actionIndex($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $oSurvey->sid;
        // Set number of page
        if (Yii::app()->getRequest()->getQuery('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->getRequest()->getQuery('pageSize'));
        }
        $aData['iGridPageSize'] = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $oDataProvider = new CArrayDataProvider($oSurvey->quotas, array(
            'pagination' => array(
                'pageSize' => $aData['iGridPageSize'],
                'pageVar' => 'page'
            ),
        ));

        //logic part here, get data for the index view
        $oQuotasService = new \LimeSurvey\Models\Services\Quotas($oSurvey);

        // Set number of page
        if (Yii::app()->getRequest()->getQuery('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->getRequest()->getQuery('pageSize'));
        }
        $aData['oDataProvider'] = new CArrayDataProvider($oSurvey->quotas, array(
            'pagination' => array(
                'pageSize' => Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']),
                'pageVar' => 'page'
            ),
        ));
        // TopBar
        $aData['topBar']['name'] = 'surveyTopbar_view';
        $aData['topBar']['leftSideView'] = 'quotasTopbarLeft_view';

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['subaction'] = gT("Survey quotas");
        $aData['sidemenu']['state'] = false;
        $this->aData = $aData;
        $this->render('index', [
            'quotasData' => $oQuotasService->getQuotaStructure(),
            'oDataProvider' => $oDataProvider,
            'oSurvey' => $oSurvey,
            'iGridPageSize' => Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize'])
        ]);
    }

    /**
     * @param $surveyid
     * @return void
     */
    public function actionQuickCSVReport($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);

        /* Export a quickly done csv file */
        header("Content-Disposition: attachment; filename=quotas-survey" . $surveyid . ".csv");
        header("Content-type: text/comma-separated-values; charset=UTF-8");
        echo gT("Quota name") . "," . gT("Limit") . "," . gT("Completed") . "," . gT("Remaining") . "\r\n";
        if (!empty($oSurvey->quotas)) {
            foreach ($oSurvey->quotas as $oQuota) {
                $completed = $oQuota->completeCount;
                echo $oQuota->name . "," . $oQuota->qlimit . "," . $completed . "," . ($oQuota->qlimit - $completed) . "\r\n";
            }
        }
        App()->end();
    }


    /**
     * @param $surveyid
     * @return void
     * @throws CDbException
     */
    public function actionAddNewQuota($surveyid){

        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'create'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $oSurvey->sid;
        $aData['thissurvey'] = getSurveyInfo($surveyid); //todo do we need this here? why?
        $aData['langs'] = $oSurvey->allLanguages;
        $aData['baselang'] = $oSurvey->language;

        $aData['sidemenu']['state'] = false;
        $aData['topBar']['showSaveButton'] = true;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['surveybar']['savebutton']['form'] = 'frmeditgroup';

        $oQuota = new Quota();
        $oQuota->sid = $oSurvey->primaryKey;

        if (isset($_POST['Quota'])) {
            $oQuota->attributes = $_POST['Quota'];
            if ($oQuota->save()) {
                foreach ($_POST['QuotaLanguageSetting'] as $language => $settingAttributes) {
                    $oQuotaLanguageSetting = new QuotaLanguageSetting();
                    $oQuotaLanguageSetting->attributes = $settingAttributes;
                    $oQuotaLanguageSetting->quotals_quota_id = $oQuota->primaryKey;
                    $oQuotaLanguageSetting->quotals_language = $language;

                    //Clean XSS - Automatically provided by CI
                    $oQuotaLanguageSetting->quotals_message = html_entity_decode($oQuotaLanguageSetting->quotals_message, ENT_QUOTES, "UTF-8");
                    // Fix bug with FCKEditor saving strange BR types
                    $oQuotaLanguageSetting->quotals_message = fixCKeditorText($oQuotaLanguageSetting->quotals_message);
                    $oQuotaLanguageSetting->save(false);

                    if (!$oQuotaLanguageSetting->validate()) {
                        $oQuota->addErrors($oQuotaLanguageSetting->getErrors());
                    }
                }
                if (!$oQuota->getErrors()) {
                    Yii::app()->user->setFlash('success', gT("New quota saved"));
                    //self::redirectToIndex($surveyid);
                    $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
                } else {
                    // if any of the parts fail to save we delete the quota and and try again
                    $oQuota->delete();
                }
            }
        }

        // create QuotaLanguageSettings
        foreach ($oSurvey->getAllLanguages() as $language) {
            $oQuotaLanguageSetting = new QuotaLanguageSetting();
            $oQuotaLanguageSetting->quotals_name = $oQuota->name;
            $oQuotaLanguageSetting->quotals_quota_id = $oQuota->primaryKey;
            $oQuotaLanguageSetting->quotals_language = $language;
            $oQuotaLanguageSetting->quotals_url = $oSurvey->languagesettings[$language]->surveyls_url;
            $siteLanguage = Yii::app()->language;
            // Switch language temporarily to get the default text in right language
            Yii::app()->language = $language;
            $oQuotaLanguageSetting->quotals_message = gT("Sorry your responses have exceeded a quota on this survey.");
            Yii::app()->language = $siteLanguage;
            $aQuotaLanguageSettings[$language] = $oQuotaLanguageSetting;
        }
        $this->aData = $aData;
        $this->render('newquota_view', [
            'oQuota' => $oQuota,
            'aQuotaLanguageSettings' => $aQuotaLanguageSettings
        ]);
    }


    public function actionEditQuota(){

    }
}
