<?php

class QuotasController extends LSBaseController
{

    /**
     * @return string[] action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + deleteQuota', // we only allow deletion via POST request
        );
    }

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

    /**
     * @param $surveyid
     * @return void
     */
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

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title .
            " (" . gT("ID") . ":" . $surveyid . ")";
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
                echo $oQuota->name . "," . $oQuota->qlimit . "," .
                    $completed . "," . ($oQuota->qlimit - $completed) . "\r\n";
            }
        }
        App()->end();
    }


    /**
     * @param $surveyid
     * @return void
     * @throws CDbException
     */
    public function actionAddNewQuota($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'create'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $oSurvey->sid;
        $aData['thissurvey'] = getSurveyInfo($surveyid);
        $aData['langs'] = $oSurvey->allLanguages;
        $aData['baselang'] = $oSurvey->language;

        $aData['sidemenu']['state'] = false;
        $aData['topBar']['showSaveButton'] = true;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title .
            " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['surveybar']['savebutton']['form'] = 'frmeditgroup';

        $oQuota = new Quota();
        $oQuota->sid = $oSurvey->primaryKey;
        $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);
        if (isset($_POST['Quota'])) {
            $oQuota = $quotaService->saveNewQuota($_POST['Quota']);
            if (!$oQuota->getErrors()) {
                Yii::app()->user->setFlash('success', gT("New quota saved"));
                $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
            }
        }

        // create QuotaLanguageSettings
        foreach ($oSurvey->getAllLanguages() as $language) {
            $oQuotaLanguageSetting = $quotaService->newQuotaLanguageSetting($oQuota, $language);
            $aQuotaLanguageSettings[$language] = $oQuotaLanguageSetting;
        }
        $this->aData = $aData;
        $this->render('newquota_view', [
            'oQuota' => $oQuota,
            'aQuotaLanguageSettings' => $aQuotaLanguageSettings
        ]);
    }

    /**
     * @param $surveyid
     * @return void
     */
    public function actionEditQuota($surveyid)
    {
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
            } else {
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
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title .
            " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['topBar']['showSaveButton'] = true;

        $this->aData = $aData;
        $this->render('editquota_view', [
            'oQuota' => $oQuota,
            'aQuotaLanguageSettings' => $aQuotaLanguageSettings
        ]);
    }

    /**
     * @return void
     */
    public function actionDeleteQuota()
    {
        $surveyid = sanitize_int(Yii::app()->request->getPost('surveyid'));
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'delete'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $quotaId = Yii::app()->request->getPost('quota_id');
        Quota::model()->deleteByPk($quotaId);
        QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $quotaId));
        QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $quotaId));

        Yii::app()->user->setFlash('success', sprintf(gT("Quota with ID %s was deleted"), $quotaId));

        $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
    }


    /**
     * @param int $surveyid
     * @param string $sSubAction
     * @return void
     */
    public function actionNewAnswer(int $surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $surveyid;

        $sSubAction = Yii::app()->request->getParam('sSubaction');
        if ($sSubAction === null) {
            $sSubAction = 'newanswer';
        }

        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'update'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $renderView = array();
        $quotaId = Yii::app()->request->getParam('quota_id');
        $quota = Quota::model()->findByPk($quotaId);
        $aData['oQuota'] = $quota;

        if (($sSubAction == "newanswer" || ($sSubAction == "new_answer_two" && !isset($_POST['quota_qid']))) &&
            Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'create')) {
            $result = $oSurvey->quotableQuestions;
            if (empty($result)) {
                $renderView = 'newanswererror_view';
            } else {
                $renderView = 'newanswer_view';
            }
        }

        $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);
        if ($sSubAction == "new_answer_two" && isset($_POST['quota_qid']) &&
            Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'create')) {
            $questionId = sanitize_int(Yii::app()->request->getPost('quota_qid'));
            $oQuestion = Question::model()
                ->with('questionl10ns', array('language' => $oSurvey->language))
                ->findByPk(array('qid' => $questionId));

            $aQuestionAnswers = $quotaService->getQuotaAnswers(
                $questionId,
                sanitize_int(Yii::app()->request->getPost('quota_id'))
            );

            $isAllAnswersSelected = $quotaService->allAnswersSelected($oQuestion, $aQuestionAnswers);

            $aData['isAllAnswersSelected'] = $isAllAnswersSelected;
            reset($aQuestionAnswers);
            $aData['oQuestion'] = $oQuestion;
            $aData['question_answers'] = $aQuestionAnswers;
            //$aData['x'] = $cntQuestionAnswer;  not needed in any view till now
            $renderView = 'newanswertwo_view';
        }

        $aData['sBaseLang'] = $oSurvey->language;
        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title .
            " (" . gT("ID") . ":" . $surveyid . ")";
       // $aData['surveybar']['closebutton']['url'] = 'admin/quotas/sa/index/surveyid/' . $surveyid; // Close button
       // $aData['surveybar']['closebutton']['forbidden'][] = 'newanswer';

        $this->aData = $aData;
        $this->render($renderView, $aData);
    }

    /**
     * @return void
     */
    public function actionInsertQuotaAnswer($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'update'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $oQuotaMembers = new QuotaMember('create'); // Trigger the 'create' rules
        $oQuotaMembers->sid = $surveyid;
        $oQuotaMembers->qid = Yii::app()->request->getPost('quota_qid');
        $oQuotaMembers->quota_id = Yii::app()->request->getPost('quota_id');
        $oQuotaMembers->code = Yii::app()->request->getPost('quota_anscode');
        if ($oQuotaMembers->save()) {
            if (!empty($_POST['createanother'])) {
                $this->redirect($this->createUrl(
                    'quotas/newAnswer',
                    [
                        'surveyid' => $surveyid,
                        'sSubAction' => 'newanswer',
                        'quota_id' => Yii::app()->request->getPost('quota_id')
                    ]
                ));
            } else {
                $this->redirect($this->createUrl('/quotas/index', ['surveyid' => $surveyid]));
            }
        } else {
            // Save was not successful, redirect back
            //todo: TEST it!!!
            $this->redirect($this->createUrl(
                'quotas/newAnswer',
                [
                    'surveyid' => $surveyid,
                    'sSubAction' => 'newanswer',
                    'quota_id' => Yii::app()->request->getPost('quota_id')
                ]
            ));
        }
    }

    /**
     * @return void
     */
    public function actionDeleteAnswer($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        if (!(Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'delete'))) {
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        QuotaMember::model()->deleteAllByAttributes(array(
            'id' => Yii::app()->request->getPost('quota_member_id'),
            'qid' => Yii::app()->request->getPost('quota_qid'),
            'code' => Yii::app()->request->getPost('quota_anscode'),
        ));

        $this->redirect($this->createUrl('/quotas/index', ['surveyid' => $surveyid]));
    }

    public function actionMassiveAction()
    {
        $action = Yii::app()->request->getQuery('action');
        $allowedActions = array('activate', 'deactivate', 'delete', 'changeLanguageSettings');
        if (isset($_POST) && in_array($action, $allowedActions)) {
            $sItems = Yii::app()->request->getPost('sItems');
            $aQuotaIds = json_decode($sItems);
            $errors = array();
            foreach ($aQuotaIds as $iQuotaId) {
                /** @var Quota $oQuota */
                $oQuota = Quota::model()->findByPk($iQuotaId);
                if (in_array($action, array('activate', 'deactivate'))) {
                    if (!(Permission::model()->hasSurveyPermission($oQuota->sid, 'quotas', 'update'))) {
                        Yii::app()->user->setFlash('error', gT("Access denied."));
                        $this->redirect(Yii::app()->request->urlReferrer);
                    }
                    $oQuota->active = ($action == 'activate' ? 1 : 0);
                    $oQuota->save();
                } elseif ($action == 'delete') {
                    if (!(Permission::model()->hasSurveyPermission($oQuota->sid, 'quotas', 'delete'))) {
                        Yii::app()->user->setFlash('error', gT("Access denied."));
                        $this->redirect(Yii::app()->request->urlReferrer);
                    }
                    $oQuota->delete();
                    QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $iQuotaId));
                    QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $iQuotaId));
                } elseif ($action == 'changeLanguageSettings' && !empty($_POST['QuotaLanguageSetting'])) {
                    if (!(Permission::model()->hasSurveyPermission($oQuota->sid, 'quotas', 'update'))) {
                        Yii::app()->user->setFlash('error', gT("Access denied."));
                        $this->redirect(Yii::app()->request->urlReferrer);
                    }
                    $oQuotaLanguageSettings = $oQuota->languagesettings;
                    foreach ($_POST['QuotaLanguageSetting'] as $language => $aQuotaLanguageSettingAttributes) {
                        $oQuotaLanguageSetting = $oQuota->languagesettings[$language];
                        $oQuotaLanguageSetting->attributes = $aQuotaLanguageSettingAttributes;
                        if (!$oQuotaLanguageSetting->save()) {
                            // save errors
                            $oQuotaLanguageSettings[$language] = $oQuotaLanguageSetting;
                            $errors[] = $oQuotaLanguageSetting->errors;
                        }
                    }
                    // render form again to display errorSummary
                    if (!empty($errors)) {
                        $this->getController()->renderPartial(
                            '/admin/quotas/viewquotas_massive_langsettings_form',
                            array(
                                'oQuota' => $oQuota,
                                'aQuotaLanguageSettings' => $oQuotaLanguageSettings,
                            )
                        );
                        return;
                    }
                }
            }
            if (empty($errors)) {
                eT("OK!");
            }
        }
    }
}
