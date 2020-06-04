<?php


class QuestionGroupsController extends LSBaseController
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
                'actions' => array('view'),
                'users' => array('@'), //only login users
            ),
            array('deny'), //always deny all actions not mentioned above
        );
    }

    /**
     * This part comes from _renderWrappedTemplate
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        $this->aData['topBar']['type'] = 'group';
        $this->aData['topBar']['showSaveButton'] = true;

        if (isset($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        }

        return parent::beforeRender($view);
    }

    /**
     * @param int $surveyid
     * @param int $gid
     * @param string $landOnSideMenuTab
     *
     * * @return void
     */
    public function actionView($surveyid, $gid, $landOnSideMenuTab = 'structure')
    {
        $aData = array();
        $aData['surveyid'] = $iSurveyID = $surveyid;
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData['gid'] = $gid;
        $baselang = $survey->language;
        if ($gid!==null) {
            $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        }
        $aData['condarray'] = $condarray ?? [];

        App()->getClientScript()->registerPackage('ace');
        App()->getClientScript()->registerPackage('questiongroupeditor');

        $oQuestionGroup = $this->getQuestionGroupObject($iSurveyID, $gid);
        $grow           = $oQuestionGroup->attributes;

        $grow = array_map('flattenText', $grow);

        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['grow'] = $grow;

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title
            . " (".gT("ID").":".$iSurveyID.")";
        $aData['questiongroupbar']['buttons']['view'] = true;

        $aData['questiongroupbar']['buttonspreview'] = true;
        $aData['questiongroupbar']['savebutton']['form'] = true;
        $aData['questiongroupbar']['saveandclosebutton']['form'] = true;
        if (sanitize_paranoid_string(App()->request->getParam('sa') == 'add')) {
            $aData['questiongroupbar']['importbutton'] = true;
        }

        ///////////
        // sidemenu
        // TODO: Code duplication (Line 611 - 614) side menu state
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $oQuestionGroup->questiongroupl10ns[$baselang]->group_name ?? '';
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;

        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'gid' => $gid,
            'startInEditView' => SettingsUser::getUserSettingValue('noViewMode', App()->user->id) == '1',
            'connectorBaseUrl' => $this->createUrl(
                'admin/questiongroups',
                ['sid' => $iSurveyID, 'sa' => '']
            ),
            'openQuestionUrl' => $this->createUrl(
                'questionEditor/view/',
                ['surveyid'=>$iSurveyID, 'gid'=>$gid, 'qid' => '']
            ),
            'createQuestionUrl' => $this->createUrl(
                "questionEditor/view/",
                ["surveyid" =>  $surveyid, 'gid' => $gid]
            ),
            'i10N' => [
                'Question group' => gT('Question group'),
                'Group overview' => gT('Group overview'),
                'Question list' => gT('Question list'),
                'Create new question group' => gT('Create new question group'),
                'Question group overview' => gT('Question group overview'),
                'Question group editor' => gT('Question group editor'),
                'General Settings' => gT("General Settings"),
                'Group summary' => gT('Group summary'),
                'Random Group' => gT('Random Group'),
                'Title' => gT('Title'),
                'Condition' => gT('Condition'),
                'Description' => gT('Description'),
                'Quick actions' => gT('Quick actions'),
                'Subquestions' => gT('Subquestions'),
                'Answeroptions' => gT('Answer options'),
                'Question type' => gT('Question type'),
                'Default answer' => gT('Default answer'),
                'Create question' => gT('Create question'),
                'Order' => gT('Order'),
                'Question code' => gT('Code'),
                'Question' => gT('Question'),
                'QuestionType' => gT('Question type'),
                'Mandatory' => gT('Mandatory'),
                'Encrypted' => gT('Encrypted'),
                'Actions' => gT('Actions'),
            ]
        ];

        $this->aData = $aData;

        $this->render('group_view', [
            'jsData' => $this->aData['jsData'],
            'gid' => $this->aData['gid']
        ]);
    }

    /**
     * Render view to add new question group.
     * Redirects to the action view
     *
     * @param int $surveyid
     */
    public function add($surveyid)
    {
        $this->actionView($surveyid, null, 'structure');
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
            //todo: action listquestiongroups could go into this controller ??
            $this->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid));
        }

        if ($action == 'importgroup') {
            $importgroup = "\n";
            $importgroup .= "\n";

            $sFullFilepath = App()->getConfig('tempdir').DIRECTORY_SEPARATOR.randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
            $sExtension = $aPathInfo['extension'];

            if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
                $fatalerror = sprintf(
                        gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."),
                        getMaximumFileUploadSize()
                        / 1024
                        / 1024
                    )
                    .'<br>';
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
                $this->redirect(array('questionGroups/importview/surveyid/'.$surveyid));
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
                $this->redirect(array('questionGroups/importview/surveyid/'.$surveyid));
            }
            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
            fixLanguageConsistency($iSurveyID);

            if (isset($aImportResults['fatalerror'])) {
                unlink($sFullFilepath);
                App()->user->setFlash('error', $aImportResults['fatalerror']);
                $this->redirect(array('questionGroups/importview/surveyid/'.$surveyid));
            }

            unlink($sFullFilepath);

            $aData['display'] = $importgroup;
            $aData['surveyid'] = $iSurveyID;
            $aData['aImportResults'] = $aImportResults;
            $aData['sExtension'] = $sExtension;
            $aData['sidemenu']['state'] = false;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title
                ." (".gT("ID").":".$iSurveyID.")";

            $this->aData = $aData;
            $this->render('import_view', [
                'aImportResults' => $this->aData['aImportResults'],
                'sExtension' => $this->aData['sExtension'],
                'surveyid' => $this->aData['surveyid']
            ]);
        }
    }

    /**
     * Import a question group. If user has no permission for that, it redirects to#
     * list of questionGroups
     *
     * @param integer $surveyid
     *
     * @return void
     */
    public function actionImportView($surveyid)
    {
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($iSurveyID);

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'import')) {
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['questiongroups'] = true;

            $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/listquestiongroups/surveyid/'.$surveyid; // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveybar']['savebutton']['text'] = gt('Import');
            $aData['surveyid'] = $surveyid;
            $aData['topBar']['sid'] = $iSurveyID;
            $aData['topBar']['showSaveButton'] = true;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title
                ." (".gT("ID").":".$iSurveyID.")";

            $this->aData = $aData;
            $this->render('importGroup_view', [
                'surveyid' => $this->aData['surveyid']
            ]);
        } else {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid));
        }
    }

    /**
     * Insert the new group to the database
     *
     * @access public
     * @param int $surveyid
     * @return void
     */
    public function actionInsert($surveyid)
    {
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            App()->loadHelper('surveytranslator');

            $oGroup = new QuestionGroup;
            $oGroup->sid = $surveyid;
            $oGroup->group_order = getMaxGroupOrder($surveyid);

            $oGroup->randomization_group = App()->request->getPost('randomization_group');
            $oGroup->grelevance = App()->request->getPost('grelevance');
            if ($oGroup->save()) {
                $newGroupID = $oGroup->gid;
            } else {
                App()->setFlashMessage(CHtml::errorSummary($oGroup), 'error');
                $this->redirect(array("questionGroups/add/surveyid/$surveyid"));
            }
            $sSurveyLanguages = Survey::model()->findByPk($surveyid)->getAllLanguages();
            foreach ($sSurveyLanguages as $sLanguage) {
                $oGroupLS = new QuestionGroupL10n;
                $oGroupLS->gid = $newGroupID;
                $oGroupLS->group_name = App()->request->getPost('group_name_'.$sLanguage, "");
                $oGroupLS->description = App()->request->getPost('description_'.$sLanguage, "");
                $oGroupLS->language = $sLanguage;
                $oGroupLS->save();
            }
            App()->setFlashMessage(gT("New question group was saved."));
            App()->setFlashMessage(
                sprintf(
                    gT('You can now %sadd a question%s in this group.'),
                    '<a href="'
                    .App()->createUrl("admin/questions/sa/newquestion/surveyid/$surveyid/gid/$newGroupID")
                    .'">',
                    '</a>'
                ),
                'info'
            );
            if (App()->request->getPost('close-after-save') === 'true') {
                $this->redirect(
                    array("questionGroups/view/surveyid/$surveyid/gid/$newGroupID")
                );
            } elseif (App()->request->getPost('saveandnew', '') !== '') {
                $this->redirect(array("questionGroups/add/surveyid/$surveyid"));
            } elseif (App()->request->getPost('saveandnewquestion', '') !== '') {
                $this->redirect(
                    array("admin/questions/sa/newquestion/",
                        'surveyid' => $surveyid, 'gid' => $newGroupID)
                );
            } else {
                // After save, go to edit
                $this->redirect(
                    array("questionGroups/edit/surveyid/$surveyid/gid/$newGroupID")
                );
            }
        } else {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }
    }

    /**
     * Action to delete a question group.
     * Could be an ajaxRequest OR a redirect to list question groups
     *
     * @access public
     *
     * @param integer $iSurveyId ID of survey
     * @param integer $iGroupId  ID of group
     * @param boolean $asJson    Value of to Render as JSON
     *
     * @return void
     * @throws CHttpException if not authorized or invalid question group
     */
    public function actionDelete($iSurveyId = null, $iGroupId = null, $asJson = false)
    {
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'delete')) {
            throw new CHttpException(403, gT("You are not authorized to delete questions."));
        }
        if (is_null($iGroupId)) {
            $iGroupId = App()->getRequest()->getPost('gid');
        }
        $oQuestionGroup = QuestionGroup::model()->find("gid = :gid", array(":gid"=>$iGroupId));
        if (empty($oQuestionGroup)) {
            throw new CHttpException(401, gT("Invalid question group id"));
        }
        /* Test the surveyid from question, not from submitted value */
        $iSurveyId = $oQuestionGroup->sid;

        if (!App()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }

        LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyId);

        $iGroupId = sanitize_int($iGroupId);
        $iGroupsDeleted = QuestionGroup::deleteWithDependency($iGroupId, $iSurveyId);

        if ($asJson !== false) {
            $success = $iGroupsDeleted > 0;
            $this->renderJSON(
                [
                    'success' => $success,
                    'deletedGroups' => $iGroupsDeleted,
                    'message' => ($success ?gT('The question group was deleted.') : gT('Group could not be deleted')),
                    'redirect' => $this->createUrl(
                        'admin/survey/sa/listquestiongroups/',
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
        $this->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$iSurveyId));
    }


    /**
     * AjaxRequest
     *
     * @param $surveyid
     * @param null $iQuestionGroupId
     */
    public function actionLoadQuestionGroup($surveyid, $iQuestionGroupId = null)
    {
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
        if ($oQuestionGroup == null) {
            $this->renderJSON([]);
            return;
        }
        $aQuestions = [];
        $aAllQuestions = $oQuestionGroup->questions;
        array_walk($aAllQuestions, function ($oQuestion) use (&$aQuestions) {
            $aQuestions[$oQuestion->qid] = array_merge($oQuestion->attributes, $oQuestion->questionl10ns);
        });

        $this->renderJSON($aQuestions);
    }

    /**
     * @todo document me.
     *
     * @param integer $sid ID of survey
     *
     */
    public function actionSaveQuestionGroupData($sid)
    {
        $questionGroup = App()->request->getPost('questionGroup', []);
        $questionGroupI10N = App()->request->getPost('questionGroupI10N', []);
        $iSurveyId = (int) $sid;

        $oQuestionGroup = QuestionGroup::model()->findByPk($questionGroup['gid']);
        if ($oQuestionGroup == null) {
            $oQuestionGroup = $this->_newQuestionGroup($questionGroup);
        } else {
            $oQuestionGroup = $this->_editQuestionGroup($oQuestionGroup, $questionGroup);
        }

        $landOnSideMenuTab = 'structure';
        $sRedirectUrl = $this->getController()->createUrl(
            'admin/questiongroups/sa/view/',
            [
                'surveyid' => $iSurveyId,
                'gid' => $oQuestionGroup->gid,
                'landOnSideMenuTab' => $landOnSideMenuTab]
        );

        $success = $this->_applyI10N($oQuestionGroup, $questionGroupI10N);

        $aQuestionGroup = $oQuestionGroup->attributes;
        LimeExpressionManager::ProcessString('{' . $aQuestionGroup['grelevance'] . '}');
        $aQuestionGroup['grelevance_expression'] = viewHelper::stripTagsEM(
            LimeExpressionManager::GetLastPrettyPrintExpression()
        );

        $this->renderJSON(
            [
                'success' => $success,
                'message' => gT('Question group successfully stored'),
                'questionGroupId' => $oQuestionGroup->gid,
                'questiongroupData' => $aQuestionGroup,
                'redirect' => $sRedirectUrl,
                'transfer' => [$questionGroup, $questionGroupI10N],
            ]
        );
        App()->close();
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
       // $iSurveyId = App()->request->getParam('sid') ?? App()->request->getParam('surveyid');
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
        $oQuestionGroup->group_order = safecount($oSurvey->groups)+1;
        $saved = $oQuestionGroup->save();
        if ($saved == false) {
            throw new CException(
                "Object creation failed, couldn't save.\n ERRORS:"
                .print_r($oQuestionGroup->getErrors(), true)
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

}
