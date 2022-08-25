<?php

class QuestionGroupsAdministrationController extends LSBaseController
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
        return array(
            array(
                'allow',
                'actions' => array(),
                'users' => array('*'), //everybody
            ),
            array(
                'allow',
                'actions' => array(
                    'view',
                    'delete',
                    'add',
                    'getQuestionGroupTopBar',
                    'getQuestionsForGroup',
                    'import',
                    'importView',
                    'loadQuestionGroup',
                    'saveQuestionGroupData',
                    'updateOrder'
                ),
                'users' => array('@'), //only login users
            ),
            array('deny'), //always deny all actions not mentioned above
        );
    }

    /**
     * This part comes from renderWrappedTemplate
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        // Set topbar type if not already set
        if (!isset($this->aData['topBar']) || !isset($this->aData['topBar']['type'])) {
            $this->aData['topBar']['type'] = 'group';
        }
        if (empty($this->aData['topBar']['showCloseButton'])) {
            $this->aData['topBar']['showCloseButton'] = false;
        }

        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        }

        // Used in question editor (pjax).
        App()->getClientScript()->registerPackage('ace');
        App()->getClientScript()->registerPackage('jquery-ace');

        return parent::beforeRender($view);
    }

    /**
     * Renders the html for the question group view.
     *
     * @param int $surveyid    survey id is important here for new questiongroups without groupid
     * @param int $gid
     * @param string $landOnSideMenuTab
     * @param string $mode  either 'overview' or 'auto'. The 'overview' mode ignores the 'noViewMode' user setting
     *
     * * @return void
     */
    public function actionView(int $surveyid, int $gid, $landOnSideMenuTab = 'structure', $mode = 'auto')
    {
        if ($mode != 'overview' && SettingsUser::getUserSettingValue('noViewMode', App()->user->id)) {
            $this->redirect(
                Yii::app()->createUrl(
                    'questionGroupsAdministration/edit/',
                    [
                        'surveyid' => $surveyid,
                        'gid' => $gid,
                        'landOnSideMenuTab' => 'structure'
                    ]
                )
            );
        }

        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
                App()->user->setFlash('error', gT("Access denied"));
                $this->redirect(App()->request->urlReferrer);
        }

        //check if group with the gid exists
        $questionGroup = QuestionGroup::model()->findByPk($gid);
        if ($questionGroup === null) { //group does not exists ...
            App()->user->setFlash('error', gT("Question group does not exists"));
            $this->redirect(App()->request->urlReferrer);
        }

        $aData = array();
        $aData['surveyid'] = $iSurveyID = $surveyid;
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData['oSurvey'] = $survey;
        $aData['gid'] = $gid;
        $baselang = $survey->language;
        if ($gid !== null) {
            $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        }
        $aData['condarray'] = $condarray;

        $oQuestionGroup = $this->getQuestionGroupObject($iSurveyID, $gid);
        $grow           = $oQuestionGroup->attributes;
        $grow['group_name'] = $oQuestionGroup->questiongroupl10ns[$baselang]->group_name ?? '';
        $grow['description'] = $oQuestionGroup->questiongroupl10ns[$baselang]->description ?? '';

        $grow = array_map('flattenText', $grow);

        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['grow'] = $grow;

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $iSurveyID . ")";

        $aData['topBar']['name'] = 'baseTopbar_view';
        $aData['topBar']['leftSideView'] = 'groupTopbarLeft_view';
        $aData['topBar']['rightSideView'] = 'groupTopbarRight_view';

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $oQuestionGroup->questiongroupl10ns[$baselang]->group_name ?? '';
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;

        $this->aData = $aData;

        $this->render('group_view', $this->aData);
    }

    /**
     * Renders the html for the question group edit.
     *
     * @param int $surveyid    survey id is important here if group does not exist
     * @param int $gid
     * @param string $landOnSideMenuTab
     *
     * * @return void
     */
    public function actionEdit(int $surveyid, $gid, $landOnSideMenuTab = 'structure')
    {
        //check permission for edit groups ...
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
                App()->user->setFlash('error', gT("Access denied"));
                $this->redirect(App()->request->urlReferrer);
        }

        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData = array();

        Yii::app()->session['FileManagerContext'] = "edit:group:{$surveyid}";

        Yii::app()->loadHelper('admin/htmleditor');
        Yii::app()->loadHelper('surveytranslator');

        $sBaseLanguage = $oSurvey->language;

        //todo: this action should not be used for new groups, use actionAdd instead
        //DO NOT accept any other values for gid
        if ($gid === null || $gid === '') {
            //this means new group
            $gid = null;
        } else {
            $gid = (int)($gid);
        }
        $oQuestionGroup = $this->getQuestionGroupObject($surveyid, $gid);
        $aData['oQuestionGroup'] = $oQuestionGroup;

        $aAdditionalLanguages = $oSurvey->additionalLanguages;
        $aLanguages = array_merge(array($sBaseLanguage), $aAdditionalLanguages);

        /**
         *  TODO: check integrity of the group languages?
         *
         *  In LS3, group languages are checked here to make sure they match the survey languages:
         *  If language exists in group but not in survey, remove from group.
         *  If language exists in survey but not in group, create based on survey's base language.
         *
         *  Reference: https://github.com/LimeSurvey/LimeSurvey/blob/85cc864e2624b5c9c6daecce3c75af3c8701a237/application/controllers/admin/questiongroups.php#L349
         *
         *  It doesn't seem necessary here. And, if it's needed, it probably better in the Model.
         *
         */

         // Load question group data for each language
        foreach ($aLanguages as $sLanguage) {
            if (isset($oQuestionGroup->questiongroupl10ns[$sLanguage])) {
                $aGroupData = $oQuestionGroup->questiongroupl10ns[$sLanguage];
                $aData['aGroupData'][$sLanguage] = $aGroupData->attributes;
                $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
                if ($sLanguage == $sBaseLanguage) {
                    $aTabTitles[$sLanguage] .= ' (' . gT("Base language") . ')';
                }
            }
        }

        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['tabtitles'] = $aTabTitles;
        $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'editgroup';
        $aData['oSurvey'] = $oSurvey;
        if ($gid !== null) {
            $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        }
        $aData['condarray'] = $condarray;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $surveyid . ")";

        // White Close Button
        $aData['showWhiteCloseButton'] = true;
        $aData['closeUrl'] = $this->createUrl(
            'questionGroupsAdministration/view',
            [
                'surveyid' => $surveyid,
                'gid' => $oQuestionGroup->gid,
                'landOnSideMenuTab' => $landOnSideMenuTab,
                'mode' => 'overview',
            ]
        );

        $aData['topBar']['name'] = 'baseTopbar_view';
        $aData['topBar']['leftSideView'] = 'editGroupTopbarLeft_view';
        $aData['topBar']['rightSideView'] = 'editGroupTopbarRight_view';

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $oQuestionGroup->questiongroupl10ns[$sBaseLanguage]->group_name ?? '';
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;


        $this->aData = $aData;

        $this->render('editGroup_view', $this->aData);
    }

    /**
     * Render view to add new question group.
     *
     * @param int $surveyid
     * @param string $landOnSideMenuTab
     *
     * @return void
     */
    public function actionAdd(int $surveyid, string $landOnSideMenuTab = 'structure')
    {
        //check permission to create groups ...
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
                App()->user->setFlash('error', gT("Access denied"));
                $this->redirect(App()->request->urlReferrer);
        }

        $oSurvey = Survey::model()->findByPk($surveyid);
        $aData = array();

        Yii::app()->session['FileManagerContext'] = "create:group:{$surveyid}";

        Yii::app()->loadHelper('admin/htmleditor');
        Yii::app()->loadHelper('surveytranslator');

        $sBaseLanguage = $oSurvey->language;
        $aSurveyLanguages = $oSurvey->additionalLanguages;
        $aSurveyLanguages[] = $sBaseLanguage;
        $aSurveyLanguages = array_reverse($aSurveyLanguages);

        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'questiongroup.js');

        $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
        $aData['surveyid'] = $surveyid;
        $aData['grplangs'] = $aSurveyLanguages;
        $aData['baselang'] = $sBaseLanguage;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $surveyid . ")";

        $aData['topBar']['name'] = 'baseTopbar_view';
        $aData['topBar']['leftSideView'] = 'addGroupTopbarLeft_view';
        $aData['topBar']['rightSideView'] = 'addGroupTopbarRight_view';
        $aData['backUrl'] = $this->createUrl(
            'questionGroupsAdministration/listquestiongroups',
            [
                'surveyid' => $surveyid
            ]
        );
        ;
        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;


        $this->aData = $aData;

        $this->render('addGroup_view', $this->aData);
    }

    /**
     * Load list question groups view for a specified by $iSurveyID
     *
     * (this action comes from old surveyadmin controller ...)
     *
     * @param int $surveyid The survey ID
     *
     * @return void
     *
     * @access public
     */
    public function actionListquestiongroups($surveyid)
    {
        $iSurveyID = sanitize_int($surveyid);

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }

        $survey = Survey::model()->findByPk($iSurveyID);

        // Reinit LEMlang and LEMsid: ensure LEMlang are set to default lang, surveyid are set to this survey id
        // Ensure Last GetLastPrettyPrintExpression get info from this sid and default lang
        LimeExpressionManager::SetEMLanguage(Survey::model()->findByPk($iSurveyID)->language);
        LimeExpressionManager::SetSurveyId($iSurveyID);
        LimeExpressionManager::StartProcessingPage(false, true);

        $aData = array();

        $aData['surveyid']                                   = $iSurveyID;
        $aData['sid'] = $iSurveyID; // important for renderfunctions ...
        //not needed anymore, was just important for function renderListQuestionGroups in Layouthelper
       // $aData['display']['menu_bars']['listquestiongroups'] = true;
        $aData['sidemenu']['questiongroups']                 = true;
        $aData['sidemenu']['listquestiongroups']             = true;
        $aData['title_bar']['title']                         =
            $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['subaction']                                  = gT("Question groups in this survey");

        $baselang = $survey->language;
        $model    = new QuestionGroup('search');

        if (isset($_GET['QuestionGroup'])) {
            $model->attributes = $_GET['QuestionGroup'];
        }

        if (isset($_GET['pageSize'])) {
            Yii::app()->user->setState('pageSize', (int) $_GET['pageSize']);
        }

        $model['sid']      = $iSurveyID;
        $model['language'] = $baselang;
       // $aData['model']    = $model; --> no need here ...

        $aData['topBar']['name'] = 'baseTopbar_view';
        $aData['topBar']['leftSideView'] = 'listquestiongroupsTopbarLeft_view';

        $this->aData = $aData;
        $this->render('listquestiongroups', [
            'model' => $model,
            'surveyid' => $iSurveyID,
            'surveybar' => [],
            'oSurvey'   => $survey,
        ]);
    }

    /**
     * Function responsible to import a question group.
     *
     * @access public
     * @return void
     */
    public function actionImport()
    {
        $action = $_POST['action'];
        $iSurveyID = $surveyid = $aData['surveyid'] = (int) $_POST['sid'];
        $survey = Survey::model()->findByPk($iSurveyID);

        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'import')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(array('questionGroupsAdministration/listquestiongroups/surveyid/' . $surveyid));
        }

        if ($action == 'importgroup') {
            $importgroup = "\n";
            $importgroup .= "\n";

            $sFullFilepath = App()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
            $sExtension = $aPathInfo['extension'];

            if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
                $fatalerror = sprintf(
                    gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."),
                    getMaximumFileUploadSize() / 1024 / 1024
                )
                    . '<br>';
            } elseif (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
                $fatalerror = gT(
                    "An error occurred uploading your file.
                     This may be caused by incorrect permissions for the application /tmp folder."
                );
            }

            // validate that we have a SID
            if (!returnGlobal('sid')) { //todo: use Yii::getParam ...
                $fatalerror .= gT("No SID (Survey) has been provided. Cannot import question.");
            }

            if (isset($fatalerror)) {
                @unlink($sFullFilepath);
                App()->user->setFlash('error', $fatalerror);
                $this->redirect(array('questionGroupsAdministration/importview/surveyid/' . $surveyid));
            }

            App()->loadHelper('admin/import');

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            if (strtolower($sExtension) == 'lsg') {
                $aImportResults = XMLImportGroup(
                    $sFullFilepath,
                    $iSurveyID,
                    (App()->request->getPost('translinksfields') == '1')
                );
            } else {
                App()->user->setFlash('error', gT("Unknown file extension"));
                $this->redirect(array('questionGroupsAdministration/importview/surveyid/' . $surveyid));
            }
            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
            fixLanguageConsistency($iSurveyID);

            if (isset($aImportResults['fatalerror'])) {
                unlink($sFullFilepath);
                App()->user->setFlash('error', $aImportResults['fatalerror']);
                $this->redirect(array('questionGroupsAdministration/importview/surveyid/' . $surveyid));
            }

            unlink($sFullFilepath);

            $aData['display'] = $importgroup;
            $aData['surveyid'] = $iSurveyID;
            $aData['sid'] = $aData['surveyid']; //frontend needs this to render topbar in getAjaxMenuArray
            $aData['aImportResults'] = $aImportResults;
            $aData['sExtension'] = $sExtension;
            $aData['sidemenu']['state'] = false;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title
                . " (" . gT("ID") . ":" . $iSurveyID . ")";

            $this->aData = $aData;
            $this->render('import_view', [
                'aImportResults' => $this->aData['aImportResults'],
                'sExtension' => $this->aData['sExtension'],
                'surveyid' => $this->aData['surveyid']
            ]);
        }
    }

    /**
     * Import a question group. If user has no permission for that, it redirects to
     * list of questionGroupsAdministration
     *
     * @param integer $surveyid
     *
     * @return void
     */
    public function actionImportView(int $surveyid, $landOnSideMenuTab = 'structure')
    {
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($iSurveyID);

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'import')) {
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['questiongroups'] = true;
            $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;

            /*$aData['surveybar']['closebutton']['url'] = 'questionGroupsAdministration/listquestiongroups/surveyid/'.$surveyid; // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveybar']['savebutton']['text'] = gT('Import');*/
            $aData['surveyid'] = $surveyid;
            /*$aData['sid'] = $surveyid;
            $aData['topBar']['sid'] = $iSurveyID;
            $aData['topBar']['showSaveButton'] = true;*/
            $aData['topBar']['name'] = 'baseTopbar_view';
            $aData['topBar']['rightSideView'] = 'importGroupTopbarRight_view';


            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title
                . " (" . gT("ID") . ":" . $iSurveyID . ")";

            $this->aData = $aData;
            $this->render('importGroup_view', $aData);
        } else {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(array('questionGroupsAdministration/listquestiongroups/surveyid/' . $surveyid));
        }
    }

    /**
     * Action to delete a question group.
     * Could be an ajaxRequest OR a redirect to list question groups
     *
     * @access public
     *
     * @param boolean $asJson    Value of to Render as JSON
     *
     * @return void
     * @throws CHttpException if not authorized or invalid question group
     */
    public function actionDelete(bool $asJson = false)
    {
        if (!App()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }

        $iGroupId = App()->getRequest()->getPost('gid');
        if ($iGroupId === null) {
            throw new CHttpException(401, gT("Invalid question group id"));
        }
        $iGroupId = sanitize_int($iGroupId);

        $oQuestionGroup = QuestionGroup::model()->find("gid = :gid", array(":gid" => $iGroupId));
        /* Test the surveyid from question, not from submitted value */
        $iSurveyId = $oQuestionGroup->sid;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'delete')) {
            throw new CHttpException(403, gT("You are not authorized to delete questions."));
        }

        LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyId);

        $iGroupsDeleted = QuestionGroup::deleteWithDependency($iGroupId, $iSurveyId);

        //this is only important for massaction ... (do we have massaction for survey groups?)
        if ($asJson !== false) {
            $success = $iGroupsDeleted > 0;
            $this->renderJSON(
                [
                    'success' => $success,
                    'deletedGroups' => $iGroupsDeleted,
                    'message' => ($success ? gT('The question group was deleted.') : gT('Group could not be deleted')),
                    'redirect' => $this->createUrl(
                        'questionGroupsAdministration/listquestiongroups/',
                        ['surveyid' => $iSurveyId]
                    )
                ]
            );
            return;
        }

        if ($iGroupsDeleted > 0) {
            QuestionGroup::model()->updateGroupOrder($iSurveyId);
            App()->setFlashMessage(gT('The question group was deleted.'));
        } else {
            App()->setFlashMessage(gT('Group could not be deleted'), 'error');
        }

        LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyId);
        $survey = Survey::model()->findByPk($iSurveyId);
        // Make sure we have the latest groups data
        $survey->refresh();
        $landOnSideMenuTab = Yii::app()->request->getPost('landOnSideMenuTab');
        if ($landOnSideMenuTab == 'structure' && !empty($survey->groups)) {
            $this->redirect(
                Yii::app()->createUrl(
                    'questionGroupsAdministration/view/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $survey->groups[0]->gid,
                        'landOnSideMenuTab' => 'structure'
                    ]
                )
            );
        } else {
            $this->redirect(array('questionGroupsAdministration/listquestiongroups/surveyid/' . $iSurveyId));
        }
    }

    /**
     * Ajax request
     *
     * Returns the data for a question group. If question group
     * does not exists a new question group will be returned (not saved)
     *
     * todo: is this function still in use?
     *
     * @param int $surveyid
     * @param null $iQuestionGroupId
     */
    public function actionLoadQuestionGroup($surveyid, $iQuestionGroupId = null)
    {
        $surveyid = sanitize_int($surveyid);
        //permission check
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            $this->renderJSON([
                'success' => false,
                'message' => 'No permission'
            ]);
        }

        $oQuestionGroup = QuestionGroup::model()->findByPk($iQuestionGroupId);
        $oSurvey = Survey::model()->findByPk($surveyid);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, App()->session['adminlang']);
        $aSurveyLanguages = $oSurvey->getAllLanguages();

        array_walk($aSurveyLanguages, function ($lngString) use (&$aLanguages, $aAllLanguages) {
            $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
        });

        if ($oQuestionGroup == null) {
            $oQuestionGroup = new QuestionGroup();
            $oQuestionGroup->sid = $oSurvey->sid;
            $i10N = [];
            array_walk($aSurveyLanguages, function ($sLanguage) use (&$i10N) {
                $i10N[$sLanguage] = [
                    'language' => $sLanguage,
                    'group_name' => '',
                    'group_name_expression' => '',
                    'description' => '',
                    'description_expression' => '',
                ];
            });
        } else {
            $i10N = [];
            foreach ($oQuestionGroup->questiongroupl10ns as $lng => $oQuestionGroupi10n) {
                $i10N[$lng] = $oQuestionGroupi10n->attributes;

                templatereplace(
                    $oQuestionGroupi10n->group_name,
                    array(),
                    $aReplacementData,
                    'Unspecified',
                    false,
                    null
                );
                $i10N[$lng]['group_name_expression'] = viewHelper::stripTagsEM(
                    LimeExpressionManager::GetLastPrettyPrintExpression()
                );

                templatereplace(
                    $oQuestionGroupi10n->description,
                    array(),
                    $aReplacementData,
                    'Unspecified',
                    false,
                    null
                );
                $i10N[$lng]['description_expression'] = viewHelper::stripTagsEM(
                    LimeExpressionManager::GetLastPrettyPrintExpression()
                );
            }
        }

        $aPermissions = [
            "read" => Permission::model()->hasSurveyPermission($oSurvey->sid, 'survey', 'read'),
            "update" => Permission::model()->hasSurveyPermission($oSurvey->sid, 'survey', 'update'),
            "editorpreset" => App()->session['htmleditormode'],
        ];

        $aQuestionGroup = $oQuestionGroup->attributes;
        LimeExpressionManager::ProcessString('{' . $aQuestionGroup['grelevance'] . '}');
        $aQuestionGroup['grelevance_expression'] = viewHelper::stripTagsEM(
            LimeExpressionManager::GetLastPrettyPrintExpression()
        );
        $this->renderJSON([
            'questionGroup' => $aQuestionGroup,
            'permissions' => $aPermissions,
            'questonGroupI10N' => $i10N,
            'languages' => $aLanguages
        ]);
    }

    /**
     * Ajax request
     *
     * Returns all questions that belong to the group.
     *
     * @param $iQuestionGroupId integer ID of question group
     *
     * @return void
     */
    public function actionGetQuestionsForGroup($iQuestionGroupId)
    {
        $iQuestionGroupId = (int) $iQuestionGroupId;
        $oQuestionGroup = QuestionGroup::model()->findByPk($iQuestionGroupId);
        if ($oQuestionGroup == null || (!Permission::model()->hasSurveyPermission($oQuestionGroup->sid, 'surveycontent', 'read'))) {
            $this->renderJSON([]);
        }
        $aQuestions = [];
        $aAllQuestions = $oQuestionGroup->questions;
        array_walk($aAllQuestions, function ($oQuestion) use (&$aQuestions) {
            $aQuestions[$oQuestion->qid] = array_merge($oQuestion->attributes, $oQuestion->questionl10ns);
        });

        $this->renderJSON(['questions' => $aQuestions]);
    }

    /**
     * Ajax request
     *
     * Creates and updates question groups
     *
     * @param integer $sid ID of survey
     *
     * @throws CException
     *
     * @return void
     *
     */
    public function actionSaveQuestionGroupData(int $sid)
    {
        $questionGroup = App()->request->getPost('questionGroup', []);
        $questionGroupI10N = App()->request->getPost('questionGroupI10N', []);
        $sScenario = App()->request->getPost('scenario', '');
        $iSurveyId = (int) $sid;

        $oQuestionGroup = isset($questionGroup['gid']) ? QuestionGroup::model()->findByPk($questionGroup['gid']) : null;

        //permission check ...
        if ($oQuestionGroup == null) {
            if (!Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'create')) {
                App()->user->setFlash('error', gT("Access denied"));
                $this->redirect(App()->request->urlReferrer);
            }
        } elseif (!Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }

        if ($oQuestionGroup == null) {
            $isNewGroup = true;
            $oQuestionGroup = $this->newQuestionGroup($iSurveyId, $questionGroup);
        } else {
            $oQuestionGroup = $this->editQuestionGroup($oQuestionGroup, $questionGroup);
        }

        $landOnSideMenuTab = 'structure';
        if (empty($sScenario)) {
            if (App()->request->getPost('close-after-save', '')) {
                $sScenario = 'save-and-close';
            } elseif (App()->request->getPost('saveandnew', '')) {
                $sScenario = 'save-and-new';
            } elseif (App()->request->getPost('saveandnewquestion', '')) {
                $sScenario = 'save-and-new-question';
            } elseif (!empty($isNewGroup)) {
                $sScenario = 'save-and-close';
            }
        }
        switch ($sScenario) {
            case 'save-and-new-question':
                $sRedirectUrl = $this->createUrl(
                    // TODO: Double check
                    'questionAdministration/create/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $oQuestionGroup->gid,
                    ]
                );
                break;
            case 'save-and-new':
                $sRedirectUrl = $this->createUrl(
                    'questionGroupsAdministration/add/',
                    [
                        'surveyid' => $iSurveyId,
                    ]
                );
                break;
            case 'save-and-close':
                $sRedirectUrl = $this->createUrl(
                    'questionGroupsAdministration/view/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $oQuestionGroup->gid,
                        'landOnSideMenuTab' => $landOnSideMenuTab,
                        'mode' => 'overview',
                    ]
                );
                break;
            default:
                $sRedirectUrl = $this->createUrl(
                    'questionGroupsAdministration/edit/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $oQuestionGroup->gid,
                        'landOnSideMenuTab' => $landOnSideMenuTab
                    ]
                );
        }

        $this->applyI10N($oQuestionGroup, $questionGroupI10N);

        $aQuestionGroup = $oQuestionGroup->attributes;
        LimeExpressionManager::ProcessString('{' . $aQuestionGroup['grelevance'] . '}');
        $aQuestionGroup['grelevance_expression'] = viewHelper::stripTagsEM(
            LimeExpressionManager::GetLastPrettyPrintExpression()
        );

        /*$this->renderJSON(
            [
                'success' => $success,
                'message' => gT('Question group successfully stored'),
                'questionGroupId' => $oQuestionGroup->gid,
                'questiongroupData' => $aQuestionGroup,
                'redirect' => $sRedirectUrl,
                'transfer' => [$questionGroup, $questionGroupI10N],
            ]
        );
        App()->close();*/
        Yii::app()->setFlashMessage(gT("Question group successfully stored"));

        $this->redirect($sRedirectUrl);
    }

    /**
     * Reorder the questiongroups based on the new order in the adminsidepanel
     *
     * @param integer $surveyid
     *
     * @return false|null|string|string[]
     * @throws CException
     */
    public function actionUpdateOrder(int $surveyid)
    {
        $oSurvey = Survey::model()->findByPk($surveyid);
        $success = true;
        $grouparray  = [];

        //permission check
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'message' => gT("You can't reorder in an active survey"),
                        'DEBUG' => ['POST' => $_POST, 'grouparray' => $grouparray]
                    ],
                ),
                false,
                false
            );
        }

        if (!$oSurvey->isActive) {
            $grouparray = App()->request->getPost('grouparray', []);
            if (!empty($grouparray)) {
                foreach ($grouparray as $aQuestiongroup) {
                    //first set up the ordering for questiongroups
                    $oQuestiongroups = QuestionGroup::model()->findAll(
                        "gid=:gid AND sid=:sid",
                        [':gid' => $aQuestiongroup['gid'], ':sid' => $surveyid]
                    );
                    array_map(
                        function ($oQuestiongroup) use ($aQuestiongroup, $success) {
                            $oQuestiongroup->group_order = $aQuestiongroup['group_order'];
                            // TODO: unused variable $success
                            $success = $success && $oQuestiongroup->save();
                        },
                        $oQuestiongroups
                    );

                    $aQuestiongroup['questions'] = isset($aQuestiongroup['questions'])
                        ? $aQuestiongroup['questions']
                        : [];

                    foreach ($aQuestiongroup['questions'] as $aQuestion) {
                        $aQuestions = Question::model()->findAll(
                            "qid=:qid AND sid=:sid",
                            [':qid' => $aQuestion['qid'], ':sid' => $surveyid]
                        );
                        array_walk(
                            $aQuestions,
                            function ($oQuestion) use ($aQuestion, $success) {
                                $oQuestion->question_order = $aQuestion['question_order'];
                                $oQuestion->gid = $aQuestion['gid'];
                                if (safecount($oQuestion->subquestions) > 0) {
                                    $aSubquestions = $oQuestion->subquestions;
                                    array_walk(
                                        $aSubquestions,
                                        function ($oSubQuestion) use ($aQuestion, $success) {
                                            $oSubQuestion->gid = $aQuestion['gid'];
                                            $success = $success && $oSubQuestion->save(true);
                                        }
                                    );
                                }
                                $success = $success && $oQuestion->save(true);
                            }
                        );
                    }
                }
            }

            QuestionGroup::model()->cleanOrder($surveyid);

            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => $success,
                        'DEBUG' => ['POST' => $_POST, 'grouparray' => $grouparray]
                    ],
                ),
                false,
                false
            );
        }
        return $this->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success' => false,
                    'message' => gT("You can't reorder in an active survey"),
                    'DEBUG' => ['POST' => $_POST, 'grouparray' => $grouparray]
                ],
            ),
            false,
            false
        );
    }

    /**
     * Ajax request to get the question group topbar as json (see view question_group_topbar)
     *
     * @param int $sid ID of survey
     * @param null |int $gid ID of group
     *
     * @return mixed
     * @throws CException
     */
    public function actionGetQuestionGroupTopBar(int $sid, $gid = null)
    {
        //permission ??
        if (!Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }

        $oSurvey = Survey::model()->findByPk($sid);
        $oQuestionGroup = null;
        if ($gid) {
            $oQuestionGroup = QuestionGroup::model()->findByPk($gid);
            $sumcount  = safecount($oQuestionGroup->questions);
        } else {
            $gid = 0;
            $sumcount = 0;
        }

        $activated = $oSurvey->active;
        $languagelist = $oSurvey->allLanguages;
        $ownsSaveButton = true;
        $ownsSaveAndCloseButton = true;

        return $this->renderPartial(
            'question_group_topbar',
            array(
                'oSurvey' => $oSurvey,
                'oQuestionGroup' => $oQuestionGroup,
                'sid'     => $oSurvey->sid,
                'gid'     => $gid,
                'sumcount4' => $sumcount,
                'languagelist' => $languagelist,
                'activated' => $activated,
                'ownsSaveButton'         => $ownsSaveButton,
                'ownsSaveAndCloseButton' => $ownsSaveAndCloseButton,
            ),
            false,
            false
        );
    }

    /** ++++++++++++  the following functions should be moved to model or a service class ++++++++++++++++++++++++++ */

    /**
     * Returns the QuestionGroup (existing one or new created one)
     *
     * @param int $iSurveyId
     * @param int | null $iQuestionGroupId ID of group
     *
     * @return QuestionGroup
     */
    private function getQuestionGroupObject($iSurveyId, $iQuestionGroupId = null)
    {
        $oQuestionGroup =  QuestionGroup::model()->findByPk($iQuestionGroupId);
        if ($oQuestionGroup == null) {
            $oQuestionGroup = new QuestionGroup();
            $oQuestionGroup->sid = $iSurveyId;
        }

        return $oQuestionGroup;
    }

    /**
     * Method to store and filter questionData for a new question
     *
     * @param int $iSurveyId
     * @param array $aQuestionGroupData
     *
     * @return QuestionGroup
     * @throws CException
     */
    private function newQuestionGroup($iSurveyId, $aQuestionGroupData = null)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyId);

        $aQuestionGroupData = array_merge([
            'sid' => $iSurveyId,
        ], $aQuestionGroupData);
        unset($aQuestionGroupData['gid']);

        $oQuestionGroup = new QuestionGroup();
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);

        if ($oQuestionGroup == null) {
            throw new CException("Object creation failed, input array malformed or invalid");
        }
        // Always add at the end
        $oQuestionGroup->group_order = safecount($oSurvey->groups) + 1;
        $saved = $oQuestionGroup->save();
        if ($saved == false) {
            throw new CException(
                "Object creation failed, couldn't save.\n ERRORS:"
                . print_r($oQuestionGroup->getErrors(), true)
            );
        }

        $i10N = [];
        foreach ($oSurvey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = new QuestionGroupL10n();
            $i10N[$sLanguage]->setAttributes([
                'gid' => $oQuestionGroup->gid,
                'language' => $sLanguage,
                'group_name' => '',
                'description' => '',
            ], false);
            $i10N[$sLanguage]->save();
        }

        return $oQuestionGroup;
    }

    /**
     * Method to store and filter questionGroupData for editing a questionGroup
     *
     * @param QuestionGroup $oQuestionGroup
     * @param array $aQuestionGroupData
     *
     * @return QuestionGroup
     *
     * @throws CException
     */
    private function editQuestionGroup(&$oQuestionGroup, $aQuestionGroupData)
    {
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);
        if ($oQuestionGroup == null) {
            throw new CException("Object update failed, input array malformed or invalid");
        }

        $saved = $oQuestionGroup->save();
        if ($saved == false) {
            throw new CException(
                "Object update failed, couldn't save. ERRORS:"
                . print_r($oQuestionGroup->getErrors(), true)
            );
        }
        return $oQuestionGroup;
    }

    /**
     * Stores questiongroup languages.
     *
     * @param QuestionGroup $oQuestionGroup
     * @param array $dataSet array with languages
     * @return bool true if ALL languages could be safed, false otherwise
     */
    private function applyI10N(&$oQuestionGroup, $dataSet)
    {
        $storeValid = true;

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = QuestionGroupL10n::model()->findByAttributes(
                ['gid' => $oQuestionGroup->gid,'language' => $sLanguage]
            );
            $i10N->setAttributes([
                'group_name' => $aI10NBlock['group_name'],
                'description' => $aI10NBlock['description'],
            ], false);
            $storeValid = $storeValid && $i10N->save();
        }

        return $storeValid;
    }
}
