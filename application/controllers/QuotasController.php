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
            'postOnly + deleteAnswer, deleteQuota, insertQuotaAnswer', // we only allow deletion via POST request
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
        if (!Permission::model()->hasSurveyPermission($surveyid, 'quotas')) {
            throw new CHttpException(403, gT("You do not have permission for this survey."));
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

        $topbarData = TopbarConfiguration::getSurveyTopbarData($oSurvey->sid);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar_quotas/rightSideButtons',
            [
                'surveyid' => $oSurvey->sid
            ],
            true
        );

        Yii::app()->loadHelper('admin.htmleditor');
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
        if (!Permission::model()->hasSurveyPermission($surveyid, 'quotas')) {
            throw new CHttpException(403, gT("You do not have permission for this survey."));
        }
        $oSurvey = Survey::model()->findByPk($surveyid);

        /* Export a quickly done csv file */
        header("Content-Disposition: attachment; filename=quotas-survey" . $surveyid . ".csv");
        header("Content-type: text/comma-separated-values; charset=UTF-8");
        echo gT("Quota name") . "," . gT("Limit") . "," . gT("Completed") . "," . gT("Remaining") . "\r\n";
        if (!empty($oSurvey->quotas)) {
            foreach ($oSurvey->quotas as $oQuota) {
                $completed = $oQuota->completeCount;
                echo csvEscape($oQuota->name) . "," . $oQuota->qlimit . "," .
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
        if (!Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'create')) {
            throw new CHttpException(403, gT("You do not have permission for this survey."));
        }
        Yii::app()->loadHelper('admin.htmleditor');

        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $oSurvey->sid;
        $aData['thissurvey'] = getSurveyInfo($surveyid);
        $aData['langs'] = $oSurvey->allLanguages;
        $aData['baselang'] = $oSurvey->language;

        $aData['sidemenu']['state'] = false;

        $aData['subaction'] = gT("Survey quotas");
        $topbarData = TopbarConfiguration::getSurveyTopbarData($oSurvey->sid);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showSaveButton' => true
            ],
            true
        );
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title .
            " (" . gT("ID") . ":" . $surveyid . ")";
        //$aData['surveybar']['savebutton']['form'] = 'frmeditgroup';

        $oQuota = new Quota();
        $oQuota->sid = $oSurvey->primaryKey;
        $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);
        if (App()->getRequest()->getPost('Quota')) {
            $oQuota = $quotaService->saveNewQuota(App()->getRequest()->getPost('Quota'));
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
     * @return void
     */
    public function actionEditQuota()
    {

        $quotaId = sanitize_int(Yii::app()->request->getQuery('quota_id'));
        $oQuota = $this->getQuotaWithPermission($quotaId, 'update');
        $surveyid = $oQuota->sid;
        $oSurvey = Survey::model()->findByPk($surveyid);

        /* @var Quota $oQuota */
        $oQuota = Quota::model()->findByPk($quotaId);

        if (App()->getRequest()->getPost('Quota')) {
            $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);
            if ($quotaService->editQuota($oQuota, $_POST['Quota']) && !$oQuota->getErrors()) {
                Yii::app()->user->setFlash('success', gT("Quota saved"));
                $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
            } else {
                Yii::app()->user->setFlash('error', gT("Quota or quota languages could not be updated."));
            }
        }

        $aQuotaLanguageSettings = [];
        foreach ($oQuota->languagesettings as $languagesetting) {
            /* url is decoded before usage @see https://github.com/LimeSurvey/LimeSurvey/blob/8d8420a4efcf8e71c4fccbb6708648ace263ca80/application/views/admin/survey/editLocalSettings_view.php#L60 */
            $languagesetting['quotals_url'] = htmlspecialchars_decode((string) $languagesetting['quotals_url']);
            $aQuotaLanguageSettings[$languagesetting->quotals_language] = $languagesetting;
        }

        $aData['surveyid'] = $surveyid;
        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title .
            " (" . gT("ID") . ":" . $surveyid . ")";
        $aData['subaction'] = gT("Survey quotas");
        $topbarData = TopbarConfiguration::getSurveyTopbarData($oSurvey->sid);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            [
                'showSaveButton' => true
            ],
            true
        );

        Yii::app()->loadHelper('admin.htmleditor');
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
        $quotaId = Yii::app()->request->getPost('quota_id');
        $oQuota = $this->getQuotaWithPermission($quotaId, 'delete');
        $surveyid = $oQuota->sid;

        Quota::model()->deleteByPk($quotaId);
        QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $quotaId));
        QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $quotaId));

        Yii::app()->user->setFlash('success', sprintf(gT("Quota with ID %s was deleted"), $quotaId));

        $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
    }


    /**
     * @return void
     */
    public function actionNewAnswer()
    {

        $quotaId = Yii::app()->request->getParam('quota_id');
        $quota = $this->getQuotaWithPermission($quotaId, 'delete');
        $surveyid = $quota->sid;
        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData['surveyid'] = $surveyid;
        $sSubAction = Yii::app()->request->getParam('sSubaction', 'newanswer');

        $renderView = array();
        $quota = Quota::model()->findByPk($quotaId);
        $aData['oQuota'] = $quota;

        if (
            ($sSubAction == "newanswer" || ($sSubAction == "new_answer_two" && !isset($_POST['quota_qid']))) &&
            Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'create')
        ) {
            $result = $oSurvey->quotableQuestions;
            if (empty($result)) {
                $renderView = 'newanswererror_view';
            } else {
                $renderView = 'newanswer_view';
            }
        }

        $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);
        if (
            $sSubAction == "new_answer_two" && isset($_POST['quota_qid']) &&
            Permission::model()->hasSurveyPermission($surveyid, 'quotas', 'create')
        ) {
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

        $aData['subaction'] = gT("Survey quotas"); //title
        $topbarData = TopbarConfiguration::getSurveyTopbarData($oSurvey->sid);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );

        $this->aData = $aData;
        $this->render($renderView, $aData);
    }

    /**
     * @return void
     */
    public function actionInsertQuotaAnswer()
    {
        $quota_qid = Yii::app()->request->getPost('quota_qid');
        $quota_id = Yii::app()->request->getPost('quota_id');
        $quota_anscode = Yii::app()->request->getPost('quota_anscode');
        $oQuota = $this->getQuotaWithPermission($quota_id, 'update');
        $surveyid = $oQuota->sid;

        $oQuotaMembers = new QuotaMember('create'); // Trigger the 'create' rules
        $oQuotaMembers->sid = $surveyid;
        $oQuotaMembers->qid = $quota_qid;
        $oQuotaMembers->quota_id = $quota_id;
        $oQuotaMembers->code = $quota_anscode;
        if ($oQuotaMembers->save()) {
            if (App()->getRequest()->getPost('createanother')) {
                $this->redirect($this->createUrl(
                    'quotas/newAnswer',
                    [
                        'surveyid' => $surveyid,
                        'sSubAction' => 'newanswer',
                        'quota_id' => $quota_id
                    ]
                ));
            } else {
                $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
            }
        } else {
            // Save was not successful, redirect back
            $this->redirect($this->createUrl(
                'quotas/newAnswer',
                [
                    'surveyid' => $surveyid,
                    'sSubAction' => 'newanswer',
                    'quota_id' => $quota_id
                ]
            ));
        }
    }

    /**
     * @return void
     */
    public function actionDeleteAnswer()
    {
        $id = App()->request->getPost('quota_member_id');
        $quotaMember = QuotaMember::model()->findByPk($id);
        if (empty($quotaMember)) {
            throw new CHttpException(404, gT("Quota member not found."));
        }
        $oQuota = $this->getQuotaWithPermission($quotaMember->quota_id, 'delete');
        $surveyid = $oQuota->sid;
        $quotaMember->delete();
        $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
    }

    /**
     *
     */
    public function actionMassiveAction($action, $surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        $oSurvey = Survey::model()->findByPk($surveyid);
        $quotaService = new \LimeSurvey\Models\Services\Quotas($oSurvey);

        if ($quotaService->checkActionPermissions($action)) {
            $sItems = Yii::app()->request->getPost('sItems', '');
            $aQuotaIds = json_decode($sItems);
            $errors = $quotaService->multipleItemsAction(
                $aQuotaIds,
                $action,
                Yii::app()->request->getPost('QuotaLanguageSetting', [])
            );
            if (empty($errors)) {
                eT("OK!");
            } else {
                eT("Error!");
            }
        } else {
            /* 403 error ? */
            Yii::app()->user->setFlash('error', gT("Access denied."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Get a quota after check exist and permission using permission on survey
     * @param integer $quotaId
     * @param string $sPermission to check (on survey quotas)
     * throw Exception
     * @return \Quota
     */
    private function getQuotaWithPermission($quotaId, $sPermission = 'read')
    {
        $oQuota = Quota::model()->findByPk($quotaId);
        if (empty($oQuota)) {
            throw new CHttpException(404, gT("Quota not found."));
        }
        if (!Permission::model()->hasSurveyPermission($oQuota->sid, 'quotas', $sPermission)) {
            throw new CHttpException(403, gT("You do not have permission for this quota."));
        }
        return $oQuota;
    }
}
