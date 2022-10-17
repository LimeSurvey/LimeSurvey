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

        if(isset($_POST['Quota'])) {
            $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);
            if ($quotaService->saveNewQuota($_POST['Quota'])) {
                Yii::app()->user->setFlash('success', gT("New quota saved"));
                $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
            } else {
                Yii::app()->user->setFlash('error', gT("Quota could not be saved."));
            }
        }

        $oQuota = new Quota();
        $oQuota->sid = $oSurvey->primaryKey;
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


    public function actionEditQuota($surveyid){
        $surveyid = sanitize_int($surveyid);
        $oSurvey = Survey::model()->findByPk($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'update'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $quotaId = sanitize_int(Yii::app()->request->getQuery('quota_id'));

        /* @var Quota $oQuota */
        $oQuota = Quota::model()->findByPk($quotaId);

        if (isset($_POST['Quota'])) {
            $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);
            if ($quotaService->editQuota($oQuota, $_POST['Quota'])) {
                Yii::app()->user->setFlash('success', gT("Quota saved"));
                $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
            }else{
                Yii::app()->user->setFlash('error', gT("Quota or quota languages could not be updated."));
            }
        }

        $aQuotaLanguageSettings = [];
        foreach ($oQuota->languagesettings as $languagesetting) {
            /* url is decoded before usage @see https://github.com/LimeSurvey/LimeSurvey/blob/8d8420a4efcf8e71c4fccbb6708648ace263ca80/application/views/admin/survey/editLocalSettings_view.php#L60 */
            $languagesetting['quotals_url'] = htmlspecialchars_decode($languagesetting['quotals_url']);
            $aQuotaLanguageSettings[$languagesetting->quotals_language] = $languagesetting;
        }

        $aData['surveyid'] = $surveyid;
        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['topBar']['showSaveButton'] = true;

        $this->aData = $aData;
        $this->render('editquota_view', [
            'oQuota' => $oQuota,
            'aQuotaLanguageSettings' => $aQuotaLanguageSettings
        ]);
    }
}
