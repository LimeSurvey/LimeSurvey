<?php

use LimeSurvey\Models\Services\{
    QuestionGroupService,
    Exception\NotFoundException
};

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
            if (!array_key_exists('oSurvey', $this->aData)) {
                $this->aData['oSurvey'] = Survey::model()->findByPk($this->aData['surveyid']);
            }
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
     * @param int $surveyid    survey ID is important here for new questiongroups without groupid
     * @param int $gid
     * @param string $landOnSideMenuTab
     * @param string $mode  either 'overview' or 'auto'. The 'overview' mode ignores the 'noViewMode' user setting
     *
     * * @return void
     */
    public function actionView(int $surveyid, int $gid, $landOnSideMenuTab = 'structure', $mode = 'auto')
    {
        if (!in_array($landOnSideMenuTab, ['settings', 'structure', ''])) {
            $landOnSideMenuTab = 'structure';
        }
        if ($mode != 'overview' && SettingsUser::getUserSettingValue('noViewMode', App()->user->id)) {
            $this->redirect(
                App()->createUrl(
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
        $aData = $this->setSurveyIdAndObject([], $surveyid);
        $aData['gid'] = $gid;
        $aData['condarray'] = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        //check if group with the gid exists
        try {
            $questionGroupService = $this->getQuestionGroupServiceClass();
            $aData['oQuestionGroup'] = $oQuestionGroup = $questionGroupService->getQuestionGroupObject($surveyid, $gid);
        } catch (NotFoundException $e) {
            App()->user->setFlash('error', gT("Question group does not exists"));
            $this->redirect(App()->request->urlReferrer);
        }
        $grow = $oQuestionGroup->attributes;
        $grow['group_name'] = $oQuestionGroup->questiongroupl10ns[$aData['oSurvey']->language]->group_name ?? '';
        $grow['description'] = $oQuestionGroup->questiongroupl10ns[$aData['oSurvey']->language]->description ?? '';
        $aData['grow'] = array_map('flattenText', $grow);
        $aData['title_bar']['title'] = $aData['oSurvey']->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $surveyid . ")";
        $topbarData = array_merge(
            TopbarConfiguration::getGroupTopbarData($surveyid),
            TopbarConfiguration::getSurveyTopbarData($surveyid),
            $aData
        );
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            'partial/topbarBtns/groupTopbarLeft_view',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            'partial/topbarBtns/groupTopbarRight_view',
            $topbarData,
            true
        );
        $aData = $this->setSidemenuData($aData, $oQuestionGroup, $landOnSideMenuTab);
        $this->aData = $aData;

        $this->render('group_view', $this->aData);
    }

    /**
     * Renders the html for the question group edit.
     *
     * @param int $surveyid    survey ID is important here if group does not exist
     * @param int $gid
     * @param string $landOnSideMenuTab
     *
     * * @return void
     */
    public function actionEdit(int $surveyid, $gid, $landOnSideMenuTab = 'structure')
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }
        $aData = $this->setSurveyIdAndObject([], $surveyid);
        App()->session['FileManagerContext'] = "edit:group:{$surveyid}";
        App()->loadHelper('admin/htmleditor');
        App()->loadHelper('surveytranslator');

        //todo: this action should not be used for new groups, use actionAdd instead
        $aData['gid'] =  $gid = ($gid === null || $gid === '') ? null : (int)$gid;
        $questionGroupService = $this->getQuestionGroupServiceClass();
        $aData['oQuestionGroup'] = $oQuestionGroup = $questionGroupService->getQuestionGroupObject($surveyid, $gid);
        $aData = $this->setLanguageData($aData);
        $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'editgroup';
        if ($gid !== null) {
            $aData['condarray'] = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        }
        $aData['title_bar']['title'] = $aData['oSurvey']->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $surveyid . ")";
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

        $topbarData = TopbarConfiguration::getGroupTopbarData($aData['oSurvey']->sid);
        $topbarData = array_merge($topbarData, $aData);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            'partial/topbarBtns/editGroupTopbarLeft_view',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            'partial/topbarBtns/editGroupTopbarRight_view',
            $topbarData,
            true
        );
        $aData = $this->setSideMenuData($aData, $oQuestionGroup, $landOnSideMenuTab);
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
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }

        $aData = $this->setSurveyIdAndObject([], $surveyid);

        App()->session['FileManagerContext'] = "create:group:{$surveyid}";
        App()->loadHelper('admin/htmleditor');
        App()->loadHelper('surveytranslator');

        $aSurveyLanguages = $aData['oSurvey']->additionalLanguages;
        $aSurveyLanguages[] = $aData['oSurvey']->language;
        $aSurveyLanguages = array_reverse($aSurveyLanguages);

        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'questiongroup.js');

        $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
        $aData['grplangs'] = $aSurveyLanguages;
        $aData['baselang'] = $aData['oSurvey']->language;

        $aData['title_bar']['title'] = $aData['oSurvey']->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $surveyid . ")";

        $aData['backUrl'] = $this->createUrl(
            'questionAdministration/listQuestions',
            [
                'surveyid' => $surveyid
            ]
        );
        $topbarData = TopbarConfiguration::getSurveyTopbarData($surveyid);
        $topbarData = array_merge($topbarData, $aData);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            'partial/topbarBtns/addGroupTopbarLeft_view',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            'partial/topbarBtns/addGroupTopbarRight_view',
            $topbarData,
            true
        );
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;

        $this->aData = $aData;

        $this->render('addGroup_view', $this->aData);
    }

    /**
     * Function responsible to import a question group.
     *
     * @access public
     * @return void
     */
    public function actionImport()
    {
        $action = App()->request->getPost('action', '');
        $aData = $this->setSurveyIdAndObject([], (int) App()->request->getPost('sid', null));
        if (
            !Permission::model()->hasSurveyPermission(
                $aData['surveyid'],
                'surveycontent',
                'import'
            )
        ) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(
                $this->createUrl(
                    'questionAdministration/listQuestions/',
                    ['surveyid' => $aData['surveyid'], 'activeTab' => 'groups']
                )
            );
        }

        if ($action == 'importgroup') {
            $questionGroupService = $this->getQuestionGroupServiceClass();
            $aImportResults = $questionGroupService->importQuestionGroup(
                $aData['surveyid'],
                App()->getConfig('tempdir'),
                App()->request->getPost('translinksfields', '')
            );

            if (isset($aImportResults['fatalerror'])) {
                App()->user->setFlash('error', $aImportResults['fatalerror']);
                $this->redirect(array('questionGroupsAdministration/importview/surveyid/' . $aData['surveyid']));
            }

            $aData['aImportResults'] = $aImportResults;
            $aData['sExtension'] = $aImportResults['extension'];
            $aData['sidemenu']['state'] = false;

            $aData['title_bar']['title'] = $aData['oSurvey']->currentLanguageSettings->surveyls_title
                . " (" . gT("ID") . ":" . $aData['surveyid'] . ")";

            $this->aData = $aData;
            $this->render('/questionAdministration/import', [
                'aImportResults' => $this->aData['aImportResults'],
                'sExtension' => $this->aData['sExtension'],
                'sid' => $this->aData['surveyid']
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
        $aData = $this->setSurveyIdAndObject([], sanitize_int($surveyid));

        if (Permission::model()->hasSurveyPermission($aData['surveyid'], 'surveycontent', 'import')) {
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['questiongroups'] = true;
            $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;

            $aData['topbar']['rightButtons'] = $this->renderPartial(
                'partial/topbarBtns/importGroupTopbarRight_view',
                [],
                true
            );

            $aData['title_bar']['title'] = $aData['oSurvey']->currentLanguageSettings->surveyls_title
                . " (" . gT("ID") . ":" . $aData['surveyid'] . ")";

            $this->aData = $aData;
            $this->render('importGroup_view', $aData);
        } else {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(
                $this->createUrl(
                    'questionAdministration/listQuestions/',
                    ['surveyid' => $aData['surveyid'], 'activeTab' => 'groups']
                )
            );
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
        $iSurveyId = sanitize_int(App()->getRequest()->getPost('surveyid', 0));
        $iGroupsDeleted = 0;

        if ($iGroupId === null) {
            throw new CHttpException(401, gT("Invalid question group id"));
        }
        $iGroupId = sanitize_int($iGroupId);
        $oQuestionGroup = QuestionGroup::model()->find("gid = :gid", array(":gid" => $iGroupId));
        if ($oQuestionGroup) {
            $iSurveyId = $oQuestionGroup->sid;
            $questionGroupService = $this->getQuestionGroupServiceClass();
            $iGroupsDeleted = $questionGroupService->deleteGroup(
                $iGroupId,
                $iSurveyId
            );
        }
        if ($asJson !== false) {
            $success = $iGroupsDeleted > 0;
            $this->renderJSON(
                [
                    'success' => $success,
                    'deletedGroups' => $iGroupsDeleted,
                    'message' => ($success ? gT('The question group was deleted.') : gT('Group could not be deleted')),
                    'redirect' => $this->createUrl(
                        'questionAdministration/listQuestions/',
                        ['surveyid' => $iSurveyId, 'activeTab' => 'groups']
                    )

                ]
            );
            return;
        }

        if ($iGroupsDeleted > 0) {
            App()->setFlashMessage(gT('The question group was deleted.'));
        } else {
            App()->setFlashMessage(gT('Group could not be deleted'), 'error');
        }

        $survey = Survey::model()->findByPk($iSurveyId);
        $landOnSideMenuTab = App()->request->getPost('landOnSideMenuTab');
        if ($landOnSideMenuTab == 'structure' && !empty($survey->groups)) {
            $this->redirect(
                App()->createUrl(
                    'questionGroupsAdministration/view/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $survey->groups[0]->gid,
                        'landOnSideMenuTab' => 'structure'
                    ]
                )
            );
        } else {
            $this->redirect(
                $this->createUrl(
                    'questionAdministration/listQuestions',
                    ['surveyid' => $iSurveyId, 'activeTab' => 'groups']
                )
            );
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
     * todo: is this function still in use?
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
        $questionGroupData = App()->request->getPost('questionGroup', []);
        $wholeQuestionGroupDataset = ['questionGroup'     => $questionGroupData,
                                      'questionGroupI10N' => App()->request->getPost('questionGroupI10N', [])
        ];
        $sScenario = App()->request->getPost('scenario', '');
        $iSurveyId = (int)$sid;

        $oQuestionGroup = isset($questionGroupData['gid']) ? QuestionGroup::model()->findByPk($questionGroupData['gid']) : null;

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

        $questionGroupService = $this->getQuestionGroupServiceClass();
        if ($oQuestionGroup == null) {
            $isNewGroup = true;
            $oQuestionGroup = $questionGroupService->createGroup($iSurveyId, $wholeQuestionGroupDataset);
        } else {
            $oQuestionGroup = $questionGroupService->updateGroup($iSurveyId, $oQuestionGroup->gid, $wholeQuestionGroupDataset);
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

        App()->setFlashMessage(gT("Question group successfully stored"));

        $this->redirect($sRedirectUrl);
    }

    /**
     * Reorder the questiongroups based on the new order in the adminsidepanel (structure tab).
     * @param integer $surveyid
     *
     * @return false|null|string|string[]
     * @throws CException
     */
    public function actionUpdateOrder(int $surveyid)
    {
        //permission check
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'message' => gT("Access denied"),
                        'DEBUG'   => ['POST' => $_POST, 'grouparray' => []]
                    ],
                ),
            );
        }
        $groupArray = App()->request->getPost('grouparray', []);
        $questionGroupService = $this->getQuestionGroupServiceClass();
        $aResult = $questionGroupService->reorderQuestionGroups($surveyid, $groupArray);
        if ($aResult['success']) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => true,
                        'DEBUG'   => ['POST' => $_POST, 'grouparray' => $groupArray]
                    ],
                ),
            );
        } else {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'message' => $aResult['message'],
                        'DEBUG'   => ['POST' => $_POST, 'grouparray' => $groupArray]
                    ],
                ),
            );
        }
    }

    /**
     * Returns the QuestionGroupService class which is created with dependency injection
     * @return QuestionGroupService
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function getQuestionGroupServiceClass()
    {
        $diContainer = \LimeSurvey\DI::getContainer();

        return $diContainer->get(
            LimeSurvey\Models\Services\QuestionGroupService::class
        );
    }

    /**
     * Sets survey ID and object into passed array
     * @param array $aData
     * @return array
     */
    private function setSurveyIdAndObject(array $aData, $surveyId)
    {
        $aData['surveyid'] = $aData['sid'] = $surveyId;
        $aData['oSurvey'] = Survey::model()->findByPk($surveyId);

        return $aData;
    }

    /**
     * Sets sidemenu parameters to aData array before returning it.
     * @param array $aData
     * @param QuestionGroup $questionGroup
     * @param string $landOnSideMenuTab
     * @return array
     */
    private function setSidemenuData(array $aData, QuestionGroup $questionGroup, string $landOnSideMenuTab)
    {
        $survey = $aData['oSurvey'];
        $baselang = $survey->language;
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $questionGroup->questiongroupl10ns[$baselang]->group_name ?? '';
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = $aData['gid'] ?? false;
        $aData['sidemenu']['explorer']['qid'] = false;
        $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;

        return $aData;
    }

    /**
     * Sets language related data of question group into passed array before returning it.
     * @param array $aData
     * @return array
     */
    private function setLanguageData(array $aData)
    {
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
        $additionalLanguages = $aData['oSurvey']->additionalLanguages;
        $languages = array_merge(array($aData['oSurvey']->language), $additionalLanguages);
        foreach ($languages as $sLanguage) {
            if (isset($aData['oQuestionGroup']->questiongroupl10ns[$sLanguage])) {
                $aGroupData = $aData['oQuestionGroup']->questiongroupl10ns[$sLanguage];
                $aData['aGroupData'][$sLanguage] = $aGroupData->attributes;
                $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
                if ($sLanguage == $aData['oSurvey']->language) {
                    $aTabTitles[$sLanguage] .= ' (' . gT("Base language") . ')';
                }
            }
        }
        $aData['tabtitles'] = $aTabTitles;

        return $aData;
    }
}
