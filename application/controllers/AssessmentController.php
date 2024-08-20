<?php

class AssessmentController extends LSBaseController
{
    /**
     * It's import to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['activate', 'delete', 'edit', 'index', 'insertUpdate'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }


    /**
     * This part comes from renderWrappedTemplate
     *
     * @param string $view View
     *
     * @return bool
     */
    protected function beforeRender($view)
    {
        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = $this->aData['oSurvey'] ?? Survey::model()->findByPk($this->aData['surveyid']);
            $this->layout = 'layout_questioneditor';
        }

        return parent::beforeRender($view);
    }

    /**
     * Renders th view for: show the list of assessments(if assessment is activated)
     *                      or the button to activate assessment mode
     *
     * @param int $surveyid the survey ID
     *
     */
    public function actionIndex($surveyid)
    {
        $iSurveyID = sanitize_int($surveyid);
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'read')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->redirect(array("admin/"));
        }

        $oSurvey =     Survey::model()->findByPk($iSurveyID);

        $this->setLanguagesBeforeAction($oSurvey);

        $aData = [];
        $aData['survey'] = $oSurvey;
        $aData['surveyid'] = $iSurveyID;

        Yii::app()->loadHelper('admin.htmleditor');

        $this->prepareDataArray($aData);

        //this part is from _renderWrapptemplate in old controller
        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $topbarData = TopbarConfiguration::getSurveyTopbarData($oSurvey->sid);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarLeft_view',
            $topbarData,
            true
        );

        $aData['gid'] = null; //important for rendering the sidebar ...(why?)
        Yii::app()->getClientScript()->registerScript(
            "AssessmentsVariables",
            "var strnogroup = '" . gT("There are no groups available.", "js") . "';",
            LSYii_ClientScript::POS_BEGIN
        );
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'assessments.js', LSYii_ClientScript::POS_BEGIN);

        PrepareEditorScript(true, $this);
        $this->aData = $aData;
        $this->render('assessments_view', $this->aData);
    }

    /**
     * Activates assessment mode for the survey.
     *
     * @param $surveyid
     */
    public function actionActivate($surveyid)
    {

        $iSurveyID = sanitize_int($surveyid);
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'create')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to activate assessment."), 'error');
            $this->redirect(array("admin/"));
        }
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $oSurvey->assessments = "Y"; //activate assessment

        if (!$oSurvey->save()) {
            Yii::app()->setFlashMessage(gT("Assessment could not be activated."), 'error');
        }

        $this->redirect($this->createUrl('/assessment/index', ['surveyid' => $surveyid]));
    }

    /**
     * Save btn of modal view. This could be update or insert.
     * Redirects to the correct action
     *
     * @param int $surveyid
     *
     */
    public function actionInsertUpdate($surveyid)
    {
        //the post param 'action' could have the values 'assessmentupdate' or 'assessmentadd'
        $actionInserUpdate = App()->request->getPost('action', 'assessmentadd');
        if ($actionInserUpdate === 'assessmentadd') {
            $this->add($surveyid);
        }
        if ($actionInserUpdate === 'assessmentupdate') {
            $this->update($surveyid);
        }

        //this should not happen, unknown action
        Yii::app()->setFlashMessage(gT("Unknown action for assessment."), 'error');
        $this->redirect($this->createUrl('/assessment/index', ['surveyid' => $surveyid]));
    }

    /**
     * Deletes an assessment.
     *
     * @param int $surveyid
     * @return void
     */
    public function actionDelete($surveyid)
    {
        $iSurveyID = sanitize_int($surveyid);
        Yii::import('application.helpers.admin.ajax_helper', true);

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'delete')) {
            \ls\ajax\AjaxHelper::outputError(gT("You have no permission to delete assessments"));
        }
        $assessmentId = (int) App()->request->getPost('id');
        // Must be deleteAll because there is one record for each language.
        $deletedAssessments = Assessment::model()->deleteAllByAttributes(array('id' => $assessmentId, 'sid' => $iSurveyID));
        if ($deletedAssessments > 0) {
            \ls\ajax\AjaxHelper::outputSuccess(gT('Assessment rule deleted.'));
        } else {
            \ls\ajax\AjaxHelper::outputError(gT('Could not delete assessment rule.'));
        }
    }

    /**
     * Insert the assessment with multilanguages
     *
     * @param $surveyid
     */
    private function add($surveyid)
    {
        $iSurveyID = sanitize_int($surveyid);
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'create')) {
            $bFirst = true;
            $iAssessmentID = -1;
            $languages = $oSurvey->additionalLanguages;
            $surveyLanguage = $oSurvey->language;
            array_unshift($languages, $surveyLanguage);
            // There is one record for each language, so we either insert them all or none, to avoid inconsistencies.
            $transaction = Yii::app()->db->beginTransaction();
            $error = false;
            try {
                foreach ($languages as $sLanguage) {
                    $aData = $this->getAssessmentPostData($iSurveyID, $sLanguage);
                    // If not processing the first language, use the ID from the first one.
                    if ($bFirst === false) {
                        $aData['id'] = $iAssessmentID;
                    }
                    $assessment = Assessment::model()->insertRecords($aData);

                    if ($assessment->hasErrors()) {
                        $error = true;
                        break;
                    }

                    // If it's the first language, keep the ID for the next ones.
                    if ($bFirst === true) {
                        $bFirst = false;
                        $iAssessmentID = $assessment->id;
                    }
                }
            } catch (Exception $ex) {
                $error = true;
            }
            if (empty($error)) {
                $transaction->commit();
                Yii::app()->setFlashMessage(gT("Assessment rule successfully added."));
            } else {
                $transaction->rollback();
                Yii::app()->setFlashMessage(gT("Could not add the assessment rule."), 'error');
                // TODO: Show error details to the user?
            }
        } else {
            Yii::app()->setFlashMessage(gT("You have no permission to create assessments"), 'error');
        }
        $this->redirect($this->createUrl('/assessment/index', ['surveyid' => $surveyid]));
    }

    /**
     * Updates an assessment. Receives input from POST
     *
     * @param int $iSurveyID
     * @return void
     */
    private function update($iSurveyID)
    {
        $iSurveyID = sanitize_int($iSurveyID);
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'update') && App()->request->getPost('id', null) != null) {
            $aid = App()->request->getPost('id', null);
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            $languages = $oSurvey->additionalLanguages;
            $surveyLanguage = $oSurvey->language;
            array_unshift($languages, $surveyLanguage);
            // There is one record for each language, so we either update them all or none, to avoid inconsistencies.
            $transaction = Yii::app()->db->beginTransaction();
            $error = false;
            try {
                foreach ($languages as $language) {
                    $aData = $this->getAssessmentPostData($iSurveyID, $language);
                    $updated = Assessment::model()->updateAssessment($aid, $iSurveyID, $language, $aData);
                    if (!$updated) {
                        $error = true;
                        break;
                    }
                }
            } catch (Exception $ex) {
                $error = true;
            }
            if (empty($error)) {
                $transaction->commit();
                Yii::app()->setFlashMessage(gT("Assessment rule successfully updated."));
            } else {
                $transaction->rollback();
                Yii::app()->setFlashMessage(gT("Could not update the assessment rule."), 'error');
                // TODO: Show error details to the user?
            }
        } else {
            Yii::app()->setFlashMessage(gT("You have no permission to update assessments"), 'error');
        }
        $this->redirect($this->createUrl('/assessment/index', ['surveyid' => $iSurveyID]));
    }

    /**
     * Feed JSON to modal.
     *
     * Gets the data for the assessment from db and gives it back to the modal view to show the values.
     *
     * @param int $surveyid
     * @return void
     */
    public function actionEdit($surveyid)
    {
        $iAsessementId = App()->request->getParam('id');
        $oAssessments = Assessment::model()->findAll("id=:id", [':id' => $iAsessementId]);
        if ($oAssessments !== null && Permission::model()->hasSurveyPermission($surveyid, 'assessments', 'update')) {
            $aData = [];
            $aData['editData'] = $oAssessments[0]->attributes;
            foreach ($oAssessments as $oAssessment) {
                $aData['models'][] = $oAssessment;
                $aData['editData']['name_' . $oAssessment->language] = $oAssessment->name;
                $aData['editData']['assessmentmessage_' . $oAssessment->language] = $oAssessment->message;
            }
            $action = 'assessmentedit';
            $aData['action'] = $action;

            $this->renderPartial('/admin/super/_renderJson', ['data' => $aData]);
        }
    }


    /**     *******************************  FOLLOWING FUNCTIONS ARE NO ACTIONS                *********************  */

    /**
     * @param array $aData
     * @param boolean $collectEdit
     * @return array
     */
    private function prepareDataArray(&$aData, $collectEdit = false)
    {
        $iSurveyID = $aData['surveyid'];
        $oSurvey = $aData['survey'];

        $aHeadings = array(gT("Scope"), gT("Question group"), gT("Minimum"), gT("Maximum"));
        $aData['headings'] = $aHeadings;
        $oAssessments = Assessment::model();
        $oAssessments->sid = $iSurveyID;

        $aData['groups'] = $this->collectGroupData($oSurvey, $aData);
        $this->setSearchParams($oAssessments);
        $aData['model'] = $oAssessments;
        if (isset($_POST['pageSize'])) {
            Yii::app()->user->setState('pageSize', Yii::app()->request->getParam('pageSize'));
        }
        $aData['actiontitle'] = gT("Add");
        $aData['actionvalue'] = "assessmentadd";
        $aData['editId'] = '';

        if ($collectEdit === true) {
            $aData = $this->collectEditData($aData);
        }

        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['assessments'] = $oAssessments;
        $aData['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $aData['baselang'] = $oSurvey->language;
        $aData['subaction'] = gT("Assessments");
        $aData['groupId'] = App()->request->getPost('gid', '');
        return $aData;
    }

    /**
     * return the groups of the current survey
     *
     *
     * @param Survey $oSurvey
     * @param array $aData
     * @return array $aGroups groupnames in array
     */
    private function collectGroupData($oSurvey, &$aData = array())
    {
        $aGroups = [];
        $db = Yii::app()->db;
        $quotedQGL10ns = $db->quoteTableName('questiongroupl10ns');
        $quotedLanguage = $db->quoteColumnName('language');

        $groups = QuestionGroup::model()->with(
            [
                'questiongroupl10ns' => [
                    'condition' => $quotedQGL10ns . '.' . $quotedLanguage . ' = :language',
                    'params' => array(':language' => $oSurvey->language)
                ]
            ]
        )->findAllByAttributes(array('sid' => $oSurvey->sid));
        foreach ($groups as $group) {
            $groupId = $group->attributes['gid'];
            $groupName = $group->getGroupNameI10N($oSurvey->language);
            $aGroups[$groupId] = $groupName;
        }
        return $aGroups;
    }

    /**
     * Set search params from Yii grid view.
     *
     * @param Assessment $oAssessments
     * @return void
     */
    private function setSearchParams(Assessment $oAssessments)
    {
        /*
        ["Assessment"]=>
            array(5) {
            ["scope"]=>
                string(1) "T"
                ["name"]=>
                string(0) ""
                ["minimum"]=>
                string(0) ""
                ["maximum"]=>
                string(0) ""
                ["message"]=>
                string(0) ""
            }
         */
        if (isset($_POST['Assessment']['scope'])) {
            $oAssessments->scope = $_POST['Assessment']['scope'];
        }

        if (isset($_POST['Assessment']['name'])) {
            $oAssessments->name = $_POST['Assessment']['name'];
        }

        if (isset($_POST['Assessment']['minimum'])) {
            $oAssessments->minimum = $_POST['Assessment']['minimum'];
        }

        if (isset($_POST['Assessment']['maximum'])) {
            $oAssessments->maximum = $_POST['Assessment']['maximum'];
        }

        if (isset($_POST['Assessment']['message'])) {
            $oAssessments->message = $_POST['Assessment']['message'];
        }
    }

    /**
     * @param array $aData
     * @return array
     */
    private function collectEditData(array $aData)
    {
        $oAssessment = Assessment::model()->find("id=:id", array(':id' => App()->request->getParam('id')));
        if (!$oAssessment) {
            throw new CHttpException(500);
        }
        // 404 ?

        $editData = $oAssessment->attributes;
        $aData['actiontitle'] = gT("Edit");
        $aData['actionvalue'] = "assessmentupdate";
        $aData['editId'] = $editData['id'];
        $aData['editdata'] = $editData;

        return $aData;
    }

    /**
     * @param int $iSurveyID
     * @param string $language
     * @return array
     */
    private function getAssessmentPostData($iSurveyID, $language)
    {
        if (!isset($_POST['gid'])) {
            $_POST['gid'] = 0;
        }

        return array(
            'sid' => $iSurveyID,
            'scope' => sanitize_paranoid_string(App()->request->getPost('scope')),
            'gid' => App()->request->getPost('gid'),
            'minimum' => (int) App()->request->getPost('minimum', 0),
            'maximum' => (int) App()->request->getPost('maximum', 0),
            'name' => App()->request->getPost('name_' . $language),
            'language' => $language,
            'message' => App()->request->getPost('assessmentmessage_' . $language)
        );
    }

    /**
     * Set languages config for assessment
     *
     * @param Survey $oSurvey
     */
    private function setLanguagesBeforeAction($oSurvey)
    {
        $languages = $oSurvey->additionalLanguages;
        $surveyLanguage = $oSurvey->language;

        Yii::app()->session['FileManagerContext'] = "edit:assessments:{$oSurvey->sid}"; //todo: do we nee this ??

        array_unshift($languages, $surveyLanguage); // makes an array with ALL the languages supported by the survey -> $assessmentlangs

        Yii::app()->setConfig("baselang", $surveyLanguage);
        Yii::app()->setConfig("assessmentlangs", $languages);
    }
}
