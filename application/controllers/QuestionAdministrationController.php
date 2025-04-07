<?php

use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\Models\Services\Exception\{
    NotFoundException,
    PermissionDeniedException,
    QuestionHasConditionsException
};

/**
 * Class QuestionAdministrationController
 */
class QuestionAdministrationController extends LSBaseController
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
                'actions' => ['view'],
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

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        }

        return parent::beforeRender($view);
    }

    /**
     * Renders the main view for question editor.
     * Main view function prepares the necessary global js parts and renders the HTML for the question editor
     *
     * @param integer $surveyid          Survey ID
     * @param integer $gid               Group ID
     * @param integer $qid               Question ID
     * @param string  $landOnSideMenuTab Name of the side menu tab. Default behavior is to land on structure tab.
     *
     * @return void
     *
     * @throws CException
     */
    public function actionView($surveyid, $gid = null, $qid = null, $landOnSideMenuTab = 'structure')
    {
        SettingsUser::setUserSetting('last_question', $qid);
        $this->actionEdit($qid);
    }

    /**
     * Show form to create new question.
     *
     * @param int $surveyid
     * @return void
     */
    public function actionCreate($surveyid)
    {
        $surveyid = (int) $surveyid;

        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }

        $oSurvey = Survey::model()->findByPk($surveyid);
        if (empty($oSurvey)) {
            throw new Exception('Internal error: Found no survey with id ' . $surveyid);
        }

        $oQuestion = $this->getQuestionObject();
        $oQuestion->sid = $surveyid;

        $this->aData['showSaveAndNewGroupButton'] = true;
        $this->aData['showSaveAndNewQuestionButton'] = true;
        $this->aData['closeUrl'] = Yii::app()->createUrl(
            'questionAdministration/listquestions',
            [
                'surveyid' => $surveyid
            ]
        );

        $this->aData['tabOverviewEditor'] = 'overview';
        $this->renderFormAux($oQuestion);
    }

    /**
     * Show question edit form.
     *
     * @param int    $questionId        Question ID
     * @param string $tabOverviewEditor which tab should be used this can be 'overview' or 'editor'
     * @return void
     * @throws CHttpException
     */
    public function actionEdit(int $questionId, string $tabOverviewEditor = null)
    {
        $questionId = (int) $questionId;
        if (!in_array($tabOverviewEditor, ['overview', 'editor'], true)) {
            $tabOverviewEditor = null;
        }

        /** @var $question Question|null */
        $question = Question::model()->findByPk($questionId);
        if (empty($question)) {
            throw new CHttpException(404, gT("Invalid question id"));
        }

        if (!Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        // "Directly show edit mode" personal setting
        if (is_null($tabOverviewEditor)) {
            $tabOverviewEditor = SettingsUser::getUserSettingValue('noViewMode', App()->user->id) ? 'editor' : 'overview';
        }

        $this->aData['closeUrl'] = Yii::app()->createUrl(
            'questionAdministration/view/',
            [
                'surveyid' => $question->sid,
                'gid' => $question->gid,
                'qid' => $question->qid,
                'landOnSideMenuTab' => 'structure'
            ]
        );
        $this->aData['tabOverviewEditor'] = $tabOverviewEditor;
        $this->renderFormAux($question);
    }

    /**
     * Helper function to render form.
     * Used by create and edit actions.
     *
     * @param Question $question Question
     * @return void
     * @throws CException
     * @todo Move to service class
     */
    private function renderFormAux(Question $question)
    {
        Yii::app()->loadHelper("admin.htmleditor");
        Yii::app()->getClientScript()->registerPackage('ace');
        Yii::app()->getClientScript()->registerPackage('jquery-ace');
        Yii::app()->getClientScript()->registerScript(
            'editorfiletype',
            "editorfiletype ='javascript';",
            CClientScript::POS_HEAD
        );
        App()->getClientScript()->registerScriptFile(
            App()->getConfig('adminscripts') . 'questionEditor.js',
            CClientScript::POS_END
        );
        // TODO: No difference between true and false?
        PrepareEditorScript(false, $this);
        App()->session['FileManagerContext'] = "edit:survey:{$question->sid}";
        initKcfinder();

        $this->aData['surveyid'] = $question->sid;
        $this->aData['sid'] = $question->sid;
        $this->aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $this->aData['questionbar']['buttons']['view'] = true;
        $this->aData['sidemenu']['landOnSideMenuTab'] = 'structure';
        $this->aData['title_bar']['title'] =
            $question->survey->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $question->sid . ")";
        $this->aData['aQuestionTypeList'] = QuestionTheme::findAllQuestionMetaDataForSelector();
        $advancedSettings = $this->getAdvancedOptions($question->qid, $question->type, $question->question_theme_name);
        // Remove general settings from this array.
        unset($advancedSettings['Attribute']);

        // Add <input> with JSON as value, used by JavaScript.
        $jsVariablesHtml = $this->renderPartial(
            '/admin/survey/Question/_subQuestionsAndAnwsersJsVariables',
            [
                'qid'               => $question->qid,
                'anslangs'          => $question->survey->allLanguages,
                // TODO
                'assessmentvisible' => false,
                'scalecount'        => $question->questionType->answerscales
            ],
            true
        );

        $showScriptField = Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', 'update') &&
            SettingsUser::getUserSettingValue('showScriptEdit', App()->user->id, null, null, 1);

        // TODO: Problem with CSRF cookie when entering directly after login.
        $modalsHtml =  Yii::app()->twigRenderer->renderViewFromFile(
            '/application/views/questionAdministration/modals.twig',
            [],
            true
        );

        // Top Bar
        $this->aData['topBar']['name'] = 'questionTopbar_view';

        // Save Button
        $this->aData['showSaveButton'] = true;

        // Save and Close Button
        $this->aData['showSaveAndCloseButton'] = true;

        // Close Button
        $this->aData['showCloseButton'] = true;

        // Delete Button
        $this->aData['showDeleteButton'] = true;

        $this->aData['sid'] = $question->sid;
        $this->aData['gid'] = $question->gid;
        $this->aData['qid'] = $question->qid;

        $this->aData['hasdefaultvalues'] = (QuestionTheme::findQuestionMetaData($question->type)['settings'])->hasdefaultvalues;

        $generalSettings = $this->getGeneralOptions(
            $question->qid,
            $question->type,
            $question->gid,
            $question->question_theme_name
        );

        $selectormodeclass = $this->getSelectorModeClass();

        $viewData = [
            'oSurvey'                => $question->survey,
            'oQuestion'              => $question,
            'aQuestionTypeGroups'    => $this->getQuestionTypeGroups($this->aData['aQuestionTypeList']),
            'advancedSettings'       => $advancedSettings,
            'generalSettings'        => $generalSettings,
            'showScriptField'       => $showScriptField,
            'jsVariablesHtml'       => $jsVariablesHtml,
            'modalsHtml'            => $modalsHtml,
            'selectormodeclass'     => $selectormodeclass,
        ];

        $this->aData = array_merge($this->aData, $viewData);

        $this->render(
            'create',
            $viewData
        );
    }

    public function actionAjaxLoadExtraOptions($questionId)
    {
        $questionId = (int) $questionId;
        $question = Question::model()->findByPk($questionId);
        if (empty($question)) {
            throw new CHttpException(404, gT('Invalid question id'));
        }

        if (!Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', 'read')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        Yii::app()->loadHelper("admin.htmleditor");
        PrepareEditorScript(false, $this);
        App()->session['FileManagerContext'] = "edit:survey:{$question->sid}";
        initKcfinder();

        $this->renderPartial(
            'extraOptions',
            [
                'question' => $question,
                'survey' => $question->survey,
            ]
        );
    }

    /**
     * Load list questions view for a specified survey by $surveyid
     *
     * @param int $surveyid Goven Survey ID
     * @param string  $landOnSideMenuTab Name of the side menu tab (settings or structure). Default behavior is to land on settings tab.
     *
     * @return string
     * @access public
     * @todo   php warning (Missing return statement)
     */
    public function actionListQuestions($surveyid, $landOnSideMenuTab = 'settings')
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403, gT("No permission"));
        }
        $iSurveyID = sanitize_int($surveyid);
        if (!in_array($landOnSideMenuTab, ['settings', 'structure', ''])) {
            $landOnSideMenuTab = 'settings';
        }
        // Reinit LEMlang and LEMsid: ensure LEMlang are set to default lang, surveyid are set to this survey ID
        // Ensure Last GetLastPrettyPrintExpression get info from this sid and default lang
        LimeExpressionManager::SetEMLanguage(Survey::model()->findByPk($iSurveyID)->language);
        LimeExpressionManager::SetSurveyId($iSurveyID);
        LimeExpressionManager::StartProcessingPage(false, true);

        // Set number of page
        $pageSize = App()->request->getParam('pageSize', null);
        if ($pageSize != null) {
            App()->user->setState('pageSize', (int) $pageSize);
        }

        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $aData   = array();

        $aData['oSurvey']                               = $oSurvey;
        $aData['surveyid']                              = $iSurveyID;
        $aData['sid']                                   = $iSurveyID;
        $aData['sidemenu']['listquestions']             = true;
        $aData['sidemenu']['landOnSideMenuTab']         = $landOnSideMenuTab;
        $aData['surveybar']['returnbutton']['url']      = "/surveyAdministration/listsurveys";

        $aData["surveyHasGroup"]        = $oSurvey->groups;
        $aData['subaction']             = gT("Questions in this survey");
        $aData['title_bar']['title']    = $oSurvey->currentLanguageSettings->surveyls_title .
            " (" . gT("ID") . ":" . $iSurveyID . ")";

        // The DataProvider will be build from the Question model, search method
        $questionModel = new Question('search');
        // Global filter
        if (isset($_GET['Question'])) {
            $questionModel->setAttributes($_GET['Question'], false);
        }
        // Filter group
        if (isset($_GET['gid'])) {
            $questionModel->gid = $_GET['gid'];
        }
        // Set number of page
        if (isset($_GET['pageSize'])) {
            App()->user->setState('pageSize', (int) $_GET['pageSize']);
        }
        $aData['pageSize'] = App()->user->getState('pageSize', App()->params['defaultPageSize']);
        // We filter the current survey ID
        $questionModel->sid = $oSurvey->sid;
        $aData['questionModel'] = $questionModel;

        $aData['surveyid'] = $iSurveyID;
        $aData['surveybar'] = [];

        // for newly combined groups and reorder parts
        $diContainer = \LimeSurvey\DI::getContainer();
        $questionGroupService = $diContainer->get(
            LimeSurvey\Models\Services\QuestionGroupService::class
        );

        if (App()->request->getParam('pageSize', 0) > 0) {
            App()->user->setState('pageSize', (int)$pageSize);
        }
        $aData['groupModel'] = $questionGroupService->getGroupData(
            $aData['oSurvey'],
            App()->request->getParam('QuestionGroup', [])
        );
        $aData['aGroupsAndQuestions'] = $this->getReorderData($oSurvey);
        $aData['surveyActivated'] = $oSurvey->getIsActive();
        $this->aData = $aData;

         $aData['hasSurveyContentCreatePermission'] = Permission::model()->hasSurveyPermission(
             $iSurveyID,
             'surveycontent',
             'create'
         );


        $this->render("listquestions", $aData);
    }

    public function getReorderData($oSurvey)
    {
        $iSurveyID = $oSurvey->primaryKey;
        $baselang = $oSurvey->language;
        // cloned below content from surveyAdministrationController line#2550
        $groups = $oSurvey->groups;
        $groupData = [];
        $initializedReplacementFields = false;
        foreach ($groups as $iGID => $oGroup) {
            $groupData[$iGID]['gid'] = $oGroup->gid;
            $groupData[$iGID]['group_text'] = $oGroup->gid . ' ' . $oGroup->questiongroupl10ns[$baselang]->group_name;
            LimeExpressionManager::StartProcessingGroup($oGroup->gid, false, $iSurveyID);
            if (!$initializedReplacementFields) {
                templatereplace("{SITENAME}"); // Hack to ensure the EM sets values of LimeReplacementFields
                $initializedReplacementFields = true;
            }

            $qs = array();

            foreach ($oGroup->questions as $question) {
                $relevance = $question->relevance == '' ? 1 : $question->relevance;
                $questionText = sprintf(
                    '[{%s}] %s % s',
                    $relevance,
                    $question->title,
                    $question->questionl10ns[$baselang]->question
                );
                LimeExpressionManager::ProcessString($questionText, $question->qid);
                $questionData['question'] = viewHelper::stripTagsEM(LimeExpressionManager::GetLastPrettyPrintExpression());
                $questionData['gid'] = $oGroup->gid;
                $questionData['qid'] = $question->qid;
                $questionData['title'] = $question->title;
                $qs[] = $questionData;
            }
            $groupData[$iGID]['questions'] = $qs;
            LimeExpressionManager::FinishProcessingGroup();
        }

        return $groupData;
    }

    /****
     * *** A lot of getter function regarding functionalities and views.
     * *** All called via ajax
     ****/

    /**
     * Returns all languages in a specific survey as a JSON document
     *
     * @todo is this action still in use?? where in the frontend?
     *
     * @param int $iSurveyId
     *
     * @return void
     */
    public function actionGetPossibleLanguages($iSurveyId)
    {
        $iSurveyId = (int)$iSurveyId;
        $aLanguages = Survey::model()->findByPk($iSurveyId)->allLanguages;
        $this->renderJSON($aLanguages);
    }

    /**
     * Action called by the FE editor when a save is triggered.
     * This is called for both new question and update.
     *
     * @return void
     * @throws CException
     */
    public function actionSaveQuestionData()
    {
        $request = App()->request;
        $calledWithAjax = (int) $request->getPost('ajax');
        $sScenario = $request->getPost('scenario', '');
        $surveyId = (int) $request->getPost('sid');

        // Check the POST data is not truncated
        if (!$request->getPost('bFullPOST')) {
            $message = gT('The data received seems incomplete. This usually happens due to server limitations (PHP setting max_input_vars). Please contact your system administrator.');
            if ($calledWithAjax) {
                echo json_encode(['message' => $message]);
                Yii::app()->end();
            } else {
                $sRedirectUrl = $this->createUrl(
                    'questionAdministration/listQuestions',
                    ['surveyid' => $surveyId]
                );
                Yii::app()->setFlashMessage($message, 'error');
                $this->redirect($sRedirectUrl);
            }
        }

        $data = !empty($_POST) ? $_POST : [];

        $diContainer = \LimeSurvey\DI::getContainer();
        $questionAggregateService = $diContainer->get(
            QuestionAggregateService::class
        );

        $question = null;
        try {
            $question = $questionAggregateService->save(
                $surveyId,
                $data
            );

            $tabOverviewEditorValue = $request->getPost('tabOverviewEditor');
            // only those two values are valid
            if (
                !(
                    $tabOverviewEditorValue === 'overview'
                    || $tabOverviewEditorValue === 'editor'
                )
            ) {
                $tabOverviewEditorValue = 'overview';
            }

            if ($calledWithAjax) {
                echo json_encode(
                    ['message' => gT('Question saved')]
                );
                Yii::app()->end();
            } else {
                App()->setFlashMessage(
                    gT('Question saved'),
                    'success'
                );
                $landOnSideMenuTab = 'structure';
                if (empty($sScenario)) {
                    if (
                        App()->request
                            ->getPost('save-and-close', '')
                    ) {
                        $sScenario = 'save-and-close';
                    } elseif (
                        App()->request
                            ->getPost('saveandnew', '')
                    ) {
                        $sScenario = 'save-and-new';
                    } elseif (
                        App()->request
                            ->getPost('saveandnewquestion', '')
                    ) {
                        $sScenario = 'save-and-new-question';
                    }
                }
                switch ($sScenario) {
                    case 'save-and-new-question':
                        $sRedirectUrl = $this->createUrl(
                            // TODO: Double check
                            'questionAdministration/create/',
                            [
                                'surveyid' => $surveyId,
                                'gid' => $question->gid,
                            ]
                        );
                        break;
                    case 'save-and-new':
                        $sRedirectUrl = $this->createUrl(
                            'questionGroupsAdministration/add/',
                            [
                                'surveyid' => $surveyId,
                            ]
                        );
                        break;
                    case 'save-and-close':
                        $sRedirectUrl = $this->createUrl(
                            'questionGroupsAdministration/view/',
                            [
                                'surveyid' => $surveyId,
                                'gid' => $question->gid,
                                'landOnSideMenuTab' => $landOnSideMenuTab,
                                'mode' => 'overview'
                            ]
                        );
                        break;
                    default:
                        $sRedirectUrl = $this->createUrl(
                            'questionAdministration/edit/',
                            [
                                'questionId' => $question->qid,
                                'landOnSideMenuTab' => $landOnSideMenuTab,
                                'tabOverviewEditor' => $tabOverviewEditorValue
                            ]
                        );
                }
                $this->redirect($sRedirectUrl);
            }
        } catch (PermissionDeniedException $e) {
            Yii::app()->user->setFlash('error', gT('Access denied'));
            $this->redirect(Yii::app()->request->urlReferrer);
        } catch (\Exception $e) {
            // Determine the proper redirect URL
            if (empty($question)) {
                $redirectUrl = $this->createUrl(
                    'surveyAdministration/view/',
                    ["surveyid" => $surveyId]
                );
            } else {
                $tabOverviewEditorValue = $request->getPost('tabOverviewEditor');
                if (
                    $tabOverviewEditorValue !== 'overview'
                    && $tabOverviewEditorValue !== 'editor'
                ) {
                    $tabOverviewEditorValue = 'overview';
                }
                $redirectUrl = $this->createUrl(
                    'questionAdministration/edit/',
                    [
                        'questionId' => $question->qid,
                        'landOnSideMenuTab' => 'structure',
                        'tabOverviewEditor' => $tabOverviewEditorValue,
                    ]
                );
            }

            // If we are already dealing with a friendly exception
            // (may include detailed errors),
            // just set the redirect URL and re-throw.
            if ($e instanceof LSUserException) {
                throw $e->setRedirectUrl($redirectUrl);
            }

            throw new LSUserException(
                500,
                $e->getMessage(),
                0,
                $redirectUrl
            );
        }
    }

    /**
     * @todo document me
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @param boolean $returnArray
     * @param string $questionThemeName
     *
     * @return void|array
     * @throws CException
     */
    public function actionGetGeneralOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $gid = null,
        $returnArray = false,  //todo see were this ajaxrequest is done and take out the parameter there and here
        $questionThemeName = null
    ) {
        $aGeneralOptionsArray = $this->getGeneralOptions($iQuestionId, $sQuestionType, $gid, $questionThemeName);

        $this->renderJSON($aGeneralOptionsArray);
    }


    /**
     * Collect initial question data
     * This either creates a temporary question object, or calls a question object from the database
     *
     * @param int $iQuestionId
     * @param int $gid
     * @param string $type
     *
     * @return void
     * @throws CException
     */
    public function actionGetQuestionData($iQuestionId = null, $gid = null, $type = null)
    {
        $iQuestionId = (int)$iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId, $type, $gid);

        $aQuestionInformationObject = $this->getCompiledQuestionData($oQuestion);
        $surveyInfo = $this->getCompiledSurveyInfo($oQuestion);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, App()->session['adminlang']);
        $aSurveyLanguages = $oQuestion->survey->getAllLanguages();
        array_walk(
            $aSurveyLanguages,
            function ($lngString) use (&$aLanguages, $aAllLanguages) {
                $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
            }
        );

        $this->renderJSON(
            array_merge(
                $aQuestionInformationObject,
                [
                    'surveyInfo'   => $surveyInfo,
                    'languages'    => $aLanguages,
                    'mainLanguage' => $oQuestion->survey->language,
                ]
            )
        );
    }

    /**
     * Called via Ajax.
     *
     * @param int $surveyid
     * @param int $gid
     * @param string $codes
     * @param int $scale_id
     * @param int $position
     * @param string $assessmentvisible
     * @return void
     * @todo Permission check hard when both sid and gid are given.
     */
    public function actionGetSubquestionRowForAllLanguages($surveyid, $gid, $codes, $scale_id, $position = 0, $assessmentvisible = '')
    {
        $oSurvey = Survey::model()->findByPk($surveyid);
        if (empty($oSurvey)) {
            throw new CHttpException(404, gT("Invalid survey ID"));
        }
        if (!Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'update')) {
            throw new CHttpException(403, gT("No permission"));
        }
        $html  = [];
        $first = true;
        $qid   = App()->getRequest()->getParam('subqid') ?? 'new' . rand(0, 99999);
        foreach ($oSurvey->allLanguages as $language) {
            $html[$language] = $this->getSubquestionRow(
                $oSurvey->sid,
                $gid,
                $qid,
                $codes,
                $language,
                $first,
                $scale_id,
                $position,
                $assessmentvisible
            );
            $first = false;
        }
        header('Content-Type: application/json');
        echo json_encode($html);
    }

    /**
     * AJAX Method to QuickAdd multiple Rows AJAX-based
     * @todo Permission
     * @todo Should be GET, not POST
     * @return void
     */
    public function actionGetSubquestionRowQuickAdd($surveyid, $gid)
    {
        $qid               = '-QUIDPLACEHOLDER-';
        $request           = Yii::app()->request;
        $codes             = $request->getPost('codes');
        $language          = $request->getPost('language');
        $first             = $request->getPost('first');
        $scale_id          = $request->getPost('scale_id');
        $type              = $request->getPost('type');
        $position          = $request->getPost('position');
        $assessmentvisible = $request->getPost('assessmentvisible');
        echo $this->getSubquestionRow($surveyid, $gid, $qid, $codes, $language, $first, $scale_id, $position, $assessmentvisible);
    }

    /**
     * @todo Permission
     * @todo Should be GET, not POST
     * @return void
     */
    public function actionGetAnswerOptionRowQuickAdd($surveyid, $gid)
    {
        $qid               = '-QUIDPLACEHOLDER-';
        $request           = Yii::app()->request;
        $codes             = $request->getPost('codes');
        $language          = $request->getPost('language');
        $first             = $request->getPost('first');
        $scale_id          = $request->getPost('scale_id');
        $type              = $request->getPost('type');
        $position          = $request->getPost('position');
        $assessmentvisible = $request->getPost('assessmentvisible');
        echo $this->getAnswerOptionRow($surveyid, $gid, $qid, $codes, $language, $first, $scale_id, $position, $assessmentvisible);
    }

    /**
     * @return void
     */
    public function actionGetAnswerOptionRowForAllLanguages($surveyid, $gid, $codes, $scale_id, $position = 0, $assessmentvisible = '')
    {
        $oSurvey = Survey::model()->findByPk($surveyid);
        if (empty($oSurvey)) {
            throw new CHttpException(404, gT("Invalid survey ID"));
        }
        if (!Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'update')) {
            throw new CHttpException(403, gT("No permission"));
        }
        $html  = [];
        $first = true;
        $qid   = App()->getRequest()->getParam('subqid') ?? 'new' . rand(0, 99999);
        foreach ($oSurvey->allLanguages as $language) {
            $html[$language] = $this->getAnswerOptionRow(
                $oSurvey->sid,
                $gid,
                $qid,
                $codes,
                $language,
                $first,
                $scale_id,
                $position,
                $assessmentvisible
            );
            $first = false;
        }
        header('Content-Type: application/json');
        echo json_encode($html);
    }

    /**
     * It returns a empty subquestion row.
     * Used when user clicks "Add new row" in question editor.
     *
     * @todo Document.
     * @todo Too many arguments.
     * @param string $codes All previous codes (used to calculate the next code)
     * @return string
     */
    private function getSubquestionRow($surveyid, $gid, $qid, $codes, $language, $first, $scale_id, $position, $assessmentvisible = '')
    {
        // index.php/admin/questions/sa/getSubquestionRow/position/1/scale_id/1/surveyid/691948/gid/76/qid/1611/language/en/first/true

        // TODO: calcul correct value
        $oldCode = false;

        // TODO: Fix question type 'A'. Needed?
        $oQuestion = $this->getQuestionObject($qid, 'A', $gid);
        $oSubquestion = $oQuestion->getEmptySubquestion();
        $oSubquestion->qid = $qid;  // Set qid as new12345 random id.

        //Capture "true" and "false" as strings
        if (is_string($first)) {
            $first = $first == "false" ? false : true;
        }

        $stringCodes = json_decode($codes, true);
        list($oSubquestion->title, $newPosition) = $this->calculateNextCode($stringCodes);

        $activated = false; // You can't add ne subquestion when survey is active
        Yii::app()->loadHelper('admin/htmleditor'); // Prepare the editor helper for the view

        $view = 'subquestionRow.twig';
        $aData = array(
            'position'  => $position,
            'scale_id'  => $scale_id,
            'activated' => $activated,
            'first'     => $first,
            'surveyid'  => $surveyid,
            'gid'       => $gid,
            'qid'       => $qid,
            'language'  => $language,
            'question'  => '',
            'relevance' => '1',
            'oldCode'   => $oldCode,
            'subquestion'  => $oSubquestion
        );

        $html = '<!-- Inserted Row -->';
        $html .= App()->twigRenderer->renderPartial('/questionAdministration/' . $view, $aData);
        $html .= '<!-- end of Inserted Row -->';
        return $html;
    }

    /**
     * @todo docs
     * @return string
     */
    private function getAnswerOptionRow($surveyid, $gid, $qid, $codes, $language, $first, $scale_id, $position, $assessmentvisible = '')
    {
        $oldCode = false;

        // @todo Fix question type 'A'. Needed?
        $oQuestion = $this->getQuestionObject($qid, 'A', $gid);
        $answerOption = $oQuestion->getEmptyAnswerOption();
        $answerOption->aid = $qid;

        //Capture "true" and "false" as strings
        if (is_string($first)) {
            $first = $first == "false" ? false : true;
        }

        $oSurvey = Survey::model()->findByPk($surveyid);
        $stringCodes = json_decode((string) $codes, true);
        list($answerOption->code, $newPosition) = $this->calculateNextCode($stringCodes);

        $activated = false; // You can't add ne subquestion when survey is active
        Yii::app()->loadHelper('admin/htmleditor'); // Prepare the editor helper for the view

        $view = 'answerOptionRow.twig';
        $aData = array(
            'assessmentvisible' => Assessment::isAssessmentActive($surveyid),
            'assessment_value'  => '',
            'answerOption'      => $answerOption,
            'answerOptionl10n'  => $answerOption->answerl10ns[$language],
            'sortorder'         => $newPosition,
            'position'          => $newPosition,
            'scale_id'          => $scale_id,
            'activated'         => $activated,
            'first'             => $first,
            'surveyid'          => $surveyid,
            'sid'               => $surveyid,
            'gid'               => $gid,
            'qid'               => $qid,
            'language'          => $language,
            'question'          => $oQuestion,
            'relevance'         => '1',
            'oldCode'           => $oldCode,
        );
        $html = '<!-- Inserted Row -->';
        $html .= App()->twigRenderer->renderPartial('/questionAdministration/' . $view, $aData);
        $html .= '<!-- end of Inserted Row -->';
        return $html;
    }

    /**
     * Calculate the next subquestion code based on previous codes.
     *
     * @param array $stringCodes
     * @return array
     */
    private function calculateNextCode(array $stringCodes)
    {
        if (empty($stringCodes)) {
            return ['A1', 0];
        }
        // We get the numerical part of each code and we store them in Arrays
        // One array is to store the pure numerical values (so we can search in it for the greates value, and increment it)
        // Another array is to store the string values (so we keep all the prefixed "0")
        $numCodes = array();
        foreach ($stringCodes as $key => $stringCode) {
            // This will loop into the code, from the last character to the first letter
            $numericSuffix = '';
            $n = 1;
            $numeric = true;
            while ($numeric === true && $n <= strlen((string) $stringCode)) {
                $currentCharacter = (string) substr((string) $stringCode, -$n, 1); // get the current character

                if (ctype_digit($currentCharacter)) {
                    // check if it's numerical
                    $numericSuffix = $currentCharacter . $numericSuffix; // store it in a string
                    $n = $n + 1;
                } else {
                    $numeric = false; // At first non numeric character found, the loop is stoped
                }
            }
            $numCodesWithZero[$key] = (string) $numericSuffix; // In string type, we can have   : "0001"
            $numCodes[$key]         = (int) $numericSuffix; // In int type, we can only have : "1"
        }

        // Let's get the greatest code
        $greatestNumCode          = max($numCodes); // greatest code
        $key                      = array_keys($numCodes, max($numCodes)); // its key (same key in all tables)
        $greatesNumCodeWithZeros  = (isset($numCodesWithZero)) ? $numCodesWithZero[$key[0]] : ''; // its value with prefixed 0 (like : 001)
        $stringCodeOfGreatestCode = $stringCodes[$key[0]]; // its original submitted  string (like: SQ001)

        // We get the string part of it: it's the original string code, without the greates code with its 0 :
        // like  substr ("SQ001", (strlen(SQ001)) - strlen(001) ) ==> "SQ"
        $stringPartOfNewCode    = (string) substr((string) $stringCodeOfGreatestCode, 0, (strlen((string) $stringCodeOfGreatestCode) - strlen($greatesNumCodeWithZeros)));

        // We increment by one the greatest code
        $numericalPartOfNewCode = $greatestNumCode + 1;

        // We get the list of 0 : (using $numericalPartOfNewCode will remove the excedent 0 ; SQ009 will be followed by SQ010 )
        $listOfZero = (string) substr($greatesNumCodeWithZeros, 0, (strlen($greatesNumCodeWithZeros) - strlen($numericalPartOfNewCode)));

        // When no more zero are available we want to be sure that the last 9 unit will not left
        // (like in SQ01 => SQ99 ; should become SQ100, not SQ9100)
        $listOfZero = $listOfZero == "9" ? '' : $listOfZero;

        // We finaly build the new code
        return [$stringPartOfNewCode . $listOfZero . $numericalPartOfNewCode, $numericalPartOfNewCode];
    }

    /**
     * Collect the permissions available for a specific question
     *
     * @param $iQuestionId
     *
     * @return void
     * @throws CException
     */
    public function actionGetQuestionPermissions($iQuestionId = null)
    {
        $iQuestionId = (int)$iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId);

        $aPermissions = [
            "read"         => Permission::model()->hasSurveyPermission($oQuestion->sid, 'surveycontent', 'read'),
            "update"       => Permission::model()->hasSurveyPermission($oQuestion->sid, 'surveycontent', 'update'),
            "editorpreset" => App()->session['htmleditormode'],
            "script"       =>
            Permission::model()->hasSurveyPermission($oQuestion->sid, 'surveycontent', 'update')
                && SettingsUser::getUserSetting('showScriptEdit', App()->user->id, null, null, 1),
        ];

        $this->renderJSON($aPermissions);
    }

    /**
     * Returns a json document containing the question types
     *
     * @return void
     */
    public function actionGetQuestionTypeList()
    {
        $this->renderJSON(QuestionType::modelsAttributes());
    }

    /**
     * @todo document me.
     * @todo is this used in frontend somewherer? can't find it
     *
     * @param string $sQuestionType
     * @return void
     */
    public function actionGetQuestionTypeInformation($sQuestionType)
    {
        $aTypeInformations = QuestionType::modelsAttributes();
        $aQuestionTypeInformation = $aTypeInformations[$sQuestionType];

        $this->renderJSON($aQuestionTypeInformation);
    }

    /**
     * Renders the top bar definition for questions as JSON document
     *
     * @param int $qid
     * @return false|null|string|string[]
     * @throws CException
     */
    public function actionGetQuestionTopbar($qid = null)
    {
        $oQuestion = $this->getQuestionObject($qid);
        $sid = $oQuestion->sid;
        $gid = $oQuestion->gid;
        $qid = $oQuestion->qid;
        $questionTypes = QuestionType::modelsAttributes();
        // TODO: Rename Variable for better readability.
        $qrrow = $oQuestion->attributes;
        $ownsSaveButton = true;
        $ownsImportButton = true;

        $hasCopyPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'create');
        $hasUpdatePermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update');
        $hasExportPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'export');
        $hasDeletePermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'delete');
        $hasReadPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read');

        return $this->renderPartial(
            'question_topbar',
            [
                'oSurvey'             => $oQuestion->survey,
                'sid'                 => $sid,
                'hasCopyPermission'   => $hasCopyPermission,
                'hasUpdatePermission' => $hasUpdatePermission,
                'hasExportPermission' => $hasExportPermission,
                'hasDeletePermission' => $hasDeletePermission,
                'hasReadPermission'   => $hasReadPermission,
                'gid'                 => $gid,
                'qid'                 => $qid,
                'qrrow'               => $qrrow,
                'qtypes'              => $questionTypes,
                'ownsSaveButton'      => $ownsSaveButton,
                'ownsImportButton'    => $ownsImportButton,
            ],
            false,
            false
        );
    }

    /**
     * Display import view for Question
     *
     * @param int $surveyid
     * @param int|null $groupid
     */
    public function actionImportView($surveyid, $groupid = null)
    {
        $iSurveyID = (int)$surveyid;
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'import')) {
            App()->session['flashmessage'] = gT("We are sorry but you don't have permissions to do this.");
            $this->redirect(['questionAdministration/listquestions/surveyid/' . $iSurveyID]);
        }
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData = [];
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['questiongroups'] = true;

        $aData['sid'] = $iSurveyID;
        $aData['surveyid'] = $iSurveyID; // todo duplication needed for survey_common_action
        $aData['gid'] = $groupid;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            'partial/topbarBtns/importQuestionTopbarRight_view',
            [],
            true
        );

        $this->aData = $aData;
        $this->render(
            'importQuestion',
            [
                'gid' => $aData['gid'],
                'sid' => $aData['sid']
            ]
        );
    }

    /**
     * Import the Question
     */
    public function actionImport()
    {
        $iSurveyID = (int) App()->request->getPost('sid', 0);
        $gid = (int) App()->request->getPost('gid', 0);

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'import')) {
            App()->session['flashmessage'] = gT("We are sorry but you don't have permissions to do this.");
            /* Same redirect than importView */
            $this->redirect(['questionAdministration/listquestions/surveyid/' . $iSurveyID]);
        }

        $jumptoquestion = (bool)App()->request->getPost('jumptoquestion', 1);

        $oSurvey = Survey::model()->findByPk($iSurveyID);

        $aData = [];
        $aData['display']['menu_bars']['surveysummary'] = 'viewquestion';
        $aData['display']['menu_bars']['gid_action'] = 'viewgroup';

        $sFullFilepath = App()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(20);
        $fatalerror = '';

        // Check file size and redirect on error
        $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
        $uploadValidator->redirectOnError('the_file', \Yii::app()->createUrl('questionAdministration/importView', array('surveyid' => $iSurveyID)));

        $sExtension = pathinfo((string) $_FILES['the_file']['name'], PATHINFO_EXTENSION);
        if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
            $fatalerror = gT(
                "An error occurred uploading your file."
                    . " This may be caused by incorrect permissions for the application /tmp folder."
            ) . '<br>';
        }

        // validate that we have a SID and GID
        if (!$iSurveyID) {
            $fatalerror .= gT("No SID (Survey) has been provided. Cannot import question.");
        }

        if (!$gid) {
            $fatalerror .= gT("No GID (Group) has been provided. Cannot import question");
        }

        if ($fatalerror != '') {
            unlink($sFullFilepath);
            App()->setFlashMessage($fatalerror, 'error');
            $this->redirect(['questionAdministration/importView', 'surveyid' => $iSurveyID]);
            return;
        }

        // load import_helper and import the file
        App()->loadHelper('admin/import');
        $aImportResults = [];
        if (strtolower($sExtension) === 'lsq') {
            $aImportResults = XMLImportQuestion(
                $sFullFilepath,
                $iSurveyID,
                $gid,
                [
                    'autorename'      => App()->request->getPost('autorename') == '1',
                    'translinkfields' => App()->request->getPost('autorename') == '1'
                ]
            );
        } else {
            App()->setFlashMessage(gT('Unknown file extension'), 'error');
            $this->redirect(['questionAdministration/importView', 'surveyid' => $iSurveyID]);
            return;
        }

        fixLanguageConsistency($iSurveyID);

        if (isset($aImportResults['fatalerror'])) {
            App()->setFlashMessage($aImportResults['fatalerror'], 'error');
            $this->redirect(['questionAdministration/importView', 'surveyid' => $iSurveyID]);
            return;
        }

        // If there are warnings, we don't jump to the question.
        // We need to show the warnings to the user, and they may be too important
        // and/or too many to be shown in a flash message.
        if (!empty($aImportResults['importwarnings'])) {
            $jumptoquestion = false;
        }

        unlink($sFullFilepath);

        $aData['aImportResults'] = $aImportResults;
        $aData['sid'] = $iSurveyID;
        $aData['surveyid'] = $iSurveyID; // todo needed in function beforeRender in this controller
        $aData['gid'] = $gid;
        $aData['sExtension'] = $sExtension;

        if ($jumptoquestion) {
            App()->setFlashMessage(gT("Question imported successfully"), 'success');
            $this->redirect(
                App()->createUrl(
                    'questionAdministration/view/',
                    [
                        'surveyid' => $iSurveyID,
                        'gid'      => $gid,
                        'qid'      => $aImportResults['newqid']
                    ]
                )
            );
            return;
        }

        $aData['sidemenu']['state'] = false; // todo ignored by sidebar.vue
        $aData['sidemenu']['landOnSideMenuTab'] = 'structure';
        $aData['title_bar']['title'] = $oSurvey->defaultlanguage->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";

        $this->aData = $aData;
        $this->render(
            'import',
            [
                'aImportResults' => $aData['aImportResults'],
                'sExtension'     => $aData['sExtension'],
                'sid'            => $aData['sid'],
                'gid'            => $aData['gid']
            ]
        );
    }

    /**
     * Load edit default values of a question screen
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function actionEditdefaultvalues($surveyid, $gid, $qid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            App()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->request->urlReferrer);
        }
        $iSurveyID = (int)$surveyid;
        $gid = (int)$gid;
        $qid = (int)$qid;
        $oQuestion = Question::model()->findByAttributes(['qid' => $qid, 'gid' => $gid,]);
        // $aQuestionTypeMetadata = QuestionType::modelsAttributes();  this is old!
        // TODO: $questionMetaData should be $questionThemeSettings
        $questionMetaData = QuestionTheme::findQuestionMetaData($oQuestion->type)['settings'];
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        $oDefaultValues = self::getDefaultValues($iSurveyID, $gid, $qid);

        $aData = [
            'oQuestion'    => $oQuestion,
            'qid'          => $qid,
            'sid'          => $iSurveyID,
            'surveyid'     => $iSurveyID, // todo needed in beforeRender
            'langopts'     => $oDefaultValues,
            'questionrow'  => $oQuestion->attributes,
            'gid'          => $gid,
            'questionMetaData' => $questionMetaData
            //'qtproperties' => $aQuestionTypeMetadata,
        ];
        $aData['oSurvey'] = $oSurvey;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['questiongroupbar']['savebutton']['form'] = 'frmeditgroup';
        $this->createUrl(
            "questionAdministration/view",
            ["surveyid" => $iSurveyID, "gid" => $gid, "qid" => $qid]
        );
        $aData['questiongroupbar']['closebutton']['url'] = $this->createUrl(
            "questionAdministration/view",
            ["surveyid" => $iSurveyID, "gid" => $gid, "qid" => $qid]
        );
        $aData['questiongroupbar']['saveandclosebutton']['form'] = 'frmeditgroup';
        $aData['display']['menu_bars']['surveysummary'] = 'editdefaultvalues';
        $aData['display']['menu_bars']['qid_action'] = 'editdefaultvalues';
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = $gid ?? false;
        $aData['sidemenu']['explorer']['qid'] = $qid ?? false;
        $aData['sidemenu']['landOnSideMenuTab'] = 'structure';

        $aData['showSaveButton'] = true;
        $aData['showSaveAndCloseButton'] = true;
        $aData['showWhiteCloseButton'] = true;
        $aData['closeUrl'] = Yii::app()->createUrl(
            'questionAdministration/view/',
            [
                'surveyid' => $oQuestion->sid,
                'gid' => $oQuestion->gid,
                'qid' => $oQuestion->qid,
                'landOnSideMenuTab' => 'structure'
            ]
        );
        $aData['hasUpdatePermission'] = Permission::model()->hasSurveyPermission(
            $iSurveyID,
            'surveycontent',
            'update'
        ) ? '' : 'disabled="disabled" readonly="readonly"';

        $topbarData = TopbarConfiguration::getQuestionTopbarData($iSurveyID);
        $topbarData = array_merge($topbarData, $aData);
        $aData['topbar']['middleButtons'] = $this->renderPartial(
            'partial/topbarBtns/editQuestionTopbarLeft_view',
            $topbarData,
            true
        );
        $aData['topbar']['rightButtons'] = $this->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            $topbarData,
            true
        );

        $this->aData = $aData;
        $this->render('editdefaultvalues', $aData);
    }

    /**
     * Delete multiple questions.
     * Called by ajax from question list.
     * Permission check is done by questions::delete()
     *
     * @return void
     * @throws CException
     */
    public function actionDeleteMultiple()
    {
        $aQids = json_decode(Yii::app()->request->getPost('sItems', ''));
        $aResults = [];

        foreach ($aQids as $iQid) {
            $oQuestion = Question::model()->with('questionl10ns')->findByPk($iQid);
            $oSurvey = Survey::model()->findByPk($oQuestion->sid);
            $sBaseLanguage = $oSurvey->language;

            if (is_object($oQuestion)) {
                $aResults[$iQid]['title'] = viewHelper::flatEllipsizeText(
                    $oQuestion->questionl10ns[$sBaseLanguage]->question,
                    true,
                    0
                );
                $result = $this->actionDelete($iQid, true);
                $aResults[$iQid]['result'] = $result['status'];
            }
        }

        $this->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            ['aResults' => $aResults, 'successLabel' => gT('Deleted')]
        );
    }

    /**
     * Function responsible for deleting a question.
     *
     * @access public
     * @param int $qid
     * @param bool $massAction
     * @param string $redirectTo 'questionlist' or 'groupoverview' or empty
     * @throws CDbException
     * @throws CHttpException
     */
    public function actionDelete($qid = null, $massAction = false, $redirectTo = null)
    {
        if (!Yii::app()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT('Invalid action'));
        }
        if (is_null($qid)) {
            $qid = Yii::app()->getRequest()->getPost('qid');
        }

        // @todo: request should specify the survey ID of the question to be deleted
        // - survey ID is verified before deletion
        $oQuestion = Question::model()->findByPk($qid);
        $surveyid = $oQuestion->sid;

        if (empty($redirectTo)) {
            $redirectTo = Yii::app()->getRequest()->getPost('redirectTo', 'questionlist');
        }
        if ($redirectTo == 'groupoverview') {
            $redirect = Yii::app()->createUrl(
                'questionGroupsAdministration/view/',
                [
                    'surveyid' => $surveyid,
                    'gid' => $oQuestion->gid,
                    'landOnSideMenuTab' => 'structure'
                ]
            );
        } else {
            $redirect = Yii::app()->createUrl(
                'questionAdministration/listQuestions/',
                [
                    'surveyid' => $surveyid,
                    'landOnSideMenuTab' => 'settings'
                ]
            );
        }

        $diContainer = \LimeSurvey\DI::getContainer();
        $questionAggregateService = $diContainer->get(
            QuestionAggregateService::class
        );

        try {
            $questionAggregateService->delete($surveyid, $qid);
        } catch (NotFoundException $e) {
            throw new CHttpException(404, gT('Invalid question id'));
        } catch (QuestionHasConditionsException $e) {
            $message = gT(
                'Question could not be deleted. '
                . 'There are conditions for other questions that rely '
                . 'on this question. '
                . 'You cannot delete this question until those conditions '
                . 'are removed.'
            );
            Yii::app()->setFlashMessage($message, 'error');
            $this->redirect($redirect);
        } catch (PermissionDeniedException $e) {
            throw new CHttpException(
                403,
                gT('You are not authorized to delete questions.')
            );
        }

        $message = gT(
            'Question was successfully deleted.'
        );

        if ($massAction) {
            return [
                'message' => $message,
                'status'  => true
            ];
        }
        if (Yii::app()->request->isAjaxRequest) {
            $this->renderJSON(
                [
                    'status'   => true,
                    'message'  => $message,
                    'redirect' => $redirect
                ]
            );
        }
        Yii::app()->session['flashmessage'] = $message;
        $this->redirect($redirect);
    }

    /**
     * Change the question group/order position of multiple questions
     *
     * @throws CException
     */
    public function actionSetMultipleQuestionGroup()
    {
        $aQids = json_decode(Yii::app()->request->getPost('sItems', '')); // List of question ids to update
        // New Group ID  (can be same group for a simple position change)
        $iGid = Yii::app()->request->getPost('group_gid');
        $iQuestionOrder = Yii::app()->request->getPost('questionposition'); // Wanted position

        $oQuestionGroup = QuestionGroup::model()->find('gid=:gid', [':gid' => $iGid]); // The New Group object
        $oSurvey = $oQuestionGroup->survey; // The Survey associated with this group

        if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'update')) {
            // If survey is active it should not be possible to update
            if ($oSurvey->active == 'N') {
                if ($iQuestionOrder == "") {
                    // If asked "at the end"
                    $iQuestionOrder = (getMaxQuestionOrder($oQuestionGroup->gid));
                }
                self::changeMultipleQuestionPositionAndGroup($aQids, $iQuestionOrder, $oQuestionGroup);
            }
        }
    }

    /**
     * Change the Questions mandatory state
     */
    public function actionChangeMultipleQuestionMandatoryState()
    {
        $aQids = json_decode(Yii::app()->request->getPost('sItems', '')); // List of question ids to update
        $iSid = (int)Yii::app()->request->getPost('sid');
        $sMandatory = Yii::app()->request->getPost('mandatory', 'N');

        if (Permission::model()->hasSurveyPermission($iSid, 'surveycontent', 'update')) {
            self::setMultipleQuestionMandatoryState($aQids, $sMandatory, $iSid);
        }
    }

    /**
     * Change the "other" option for applicable question types
     */
    public function actionChangeMultipleQuestionOtherState()
    {
        $aQids = json_decode(Yii::app()->request->getPost('sItems', '')); // List of question ids to update
        $iSid = (int)Yii::app()->request->getPost('sid');
        $sOther = (Yii::app()->request->getPost('other') === '1') ? 'Y' : 'N';

        if (Permission::model()->hasSurveyPermission($iSid, 'surveycontent', 'update')) {
            self::setMultipleQuestionOtherState($aQids, $sOther, $iSid);
        }
    }

    /**
     * Change attributes for multiple questions
     * ajax request (this is a massive action for questionlists view)
     *
     */
    public function actionChangeMultipleQuestionAttributes()
    {
        $aQidsAndLang        = json_decode((string) $_POST['sItems']); // List of question ids to update
        $iSid                = Yii::app()->request->getPost('sid'); // The survey (for permission check)
        $aAttributesToUpdate = json_decode((string) $_POST['aAttributesToUpdate']); // The list of attributes to updates
        // TODO 1591979134468: this should be get from the question model
        $aValidQuestionTypes = str_split((string) $_POST['aValidQuestionTypes']); //The valid question types for those attributes

        // Calling th model
        QuestionAttribute::model()->setMultiple($iSid, $aQidsAndLang, $aAttributesToUpdate, $aValidQuestionTypes);
    }

    /**
     * Loads the possible Positions where a Question could be inserted to
     *
     * @param int $gid
     * @param string $classes
     * @return CWidget|mixed|void
     * @throws Exception
     */
    public function actionAjaxLoadPositionWidget($gid, $classes = '')
    {
        $oQuestionGroup = QuestionGroup::model()->find('gid=:gid', [':gid' => $gid]);
        if (
            is_a($oQuestionGroup, 'QuestionGroup') &&
            Permission::model()->hasSurveyPermission($oQuestionGroup->sid, 'surveycontent', 'read')
        ) {
            $aOptions = [
                'display'           => 'form_group',
                'oQuestionGroup'    => $oQuestionGroup,

            ];

            // TODO: Better solution: Hard-code allowed CSS classes.
            if ($classes != '' && $this->isValidCSSClass($classes)) {
                $aOptions['classes'] = $classes;
            }

            return App()->getController()->widget(
                'ext.admin.survey.question.PositionWidget.PositionWidget',
                $aOptions
            );
        }
        return;
    }

    /**
     * render selected items for massive action widget
     * @throws CException
     */

    public function actionRenderItemsSelected()
    {
        $aQids = json_decode(Yii::app()->request->getPost('$oCheckedItems', ''));
        $aResults     = [];
        $tableLabels  = [gT('Question ID'), gT('Question title'), gT('Status')];

        foreach ($aQids as $sQid) {
            $iQid        = (int)$sQid;
            $oQuestion      = Question::model()->with('questionl10ns')->findByPk($iQid);
            $oSurvey        = Survey::model()->findByPk($oQuestion->sid);
            $sBaseLanguage  = $oSurvey->language;

            if (is_object($oQuestion)) {
                $aResults[$iQid]['title'] = substr(
                    viewHelper::flatEllipsizeText(
                        $oQuestion->questionl10ns[$sBaseLanguage]->question,
                        true,
                        0
                    ),
                    0,
                    100
                );
                $aResults[$iQid]['result'] = 'selected';
            }
        }

        $this->renderPartial(
            'ext.admin.grid.MassiveActionsWidget.views._selected_items',
            [
                'aResults'     =>  $aResults,
                'successLabel' =>  gT('Selected'),
                'tableLabels'  =>  $tableLabels
            ]
        );
    }

    /**
     * Get HTML for general settings.
     * Called with Ajax after question type is selected.
     *
     * @param int $surveyId
     * @param string $questionType One-char string
     * @param string $questionTheme the question theme
     * @param int $questionId Null or 0 if new question is being created.
     * @return void
     */
    public function actionGetGeneralSettingsHTML(int $surveyId, string $questionType, string $questionTheme = null, $questionId = null)
    {
        if (empty($questionType)) {
            throw new CHttpException(405, 'Internal error: No question type');
        }
        // TODO: Difference between create and update permissions?
        if (!Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'update')) {
            throw new CHttpException(403, gT('No permission'));
        }
        // NB: This works even when $questionId is null (get default question values).
        $question = $this->getQuestionObject($questionId, $questionType, null, $questionTheme);
        // NB: Only check permission when there is a question.
        if (!empty($question)) {
            // NB: Could happen if user manipulates request.
            if (!Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', 'update')) {
                throw new CHttpException(403, gT('No permission'));
            }
        }
        $generalSettings = $this->getGeneralOptions(
            $question->qid,
            $questionType,
            $question->gid,
            $questionTheme
        );

        $questionThemeObject = QuestionTheme::model()->find('name=:name', array(':name' => $questionTheme));
        $this->renderPartial("generalSettings", [
            'generalSettings' => $generalSettings,
            'oSurvey' => Survey::model()->findByPk($surveyId),
            'question' => $question,
            'aQuestionTypeGroups' => $this->getQuestionTypeGroups(QuestionTheme::findAllQuestionMetaDataForSelector()),
            'questionTheme' => $questionThemeObject,
            'selectormodeclass' => $this->getSelectorModeClass(),
        ]);
    }

    /**
     * Copies a question
     *
     * @return void
     */
    public function actionCopyQuestion()
    {
        $aData = [];
        //load helpers
        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('admin.htmleditor');

        //get params from request
        $surveyId = (int)Yii::app()->request->getParam('surveyId');
        $questionGroupId = (int)Yii::app()->request->getParam('questionGroupId');
        $questionIdToCopy = (int)Yii::app()->request->getParam('questionId');

        //permission check ...
        if (!Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'create')) {
            Yii::app()->user->setFlash('error', gT("Access denied! You don't have permission to copy a question"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $oQuestion = Question::model()->findByAttributes([
            'sid' => $surveyId,
            'gid' => $questionGroupId,
            'qid' => $questionIdToCopy
        ]);
        if ($oQuestion === null) {
            Yii::app()->user->setFlash('error', gT("Question does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $oSurvey = Survey::model()->findByPk($surveyId);
        $oQuestionGroup = QuestionGroup::model()->find('gid=:gid', array(':gid' => $questionGroupId));
        $aData['surveyid'] = $surveyId; //this is important to load the correct layout (see beforeRender)

        // Array elements for frontend (topbar etc.)
        $aData['sidemenu']['landOnSideMenuTab'] = 'structure';
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $surveyId . ")";
        $aData['closeUrl'] = Yii::app()->createUrl(
            'questionAdministration/view/',
            [
                'surveyid' => $oQuestion->sid,
                'gid' => $oQuestion->gid,
                'qid' => $oQuestion->qid,
                'landOnSideMenuTab' => 'structure'
            ]
        );

        $aData['topbar']['rightButtons'] = $this->renderPartial(
            'partial/topbarBtns/copyQuestionTopbarRight_view',
            [
                'closeUrl' => $aData['closeUrl']
            ],
            true
        );

        $aData['oSurvey'] = $oSurvey;
        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['oQuestion'] = $oQuestion;

        //save the copy ...savecopy (submitbtn pressed ...)
        $savePressed = Yii::app()->request->getParam('savecopy');
        if (isset($savePressed) && $savePressed !== null) {
            $newTitle = Yii::app()->request->getParam('question')['title'];

            $newQuestionL10n = Yii::app()->request->getParam('questionI10N');
            $copyQuestionTextValues = [];
            if (!empty($newQuestionL10n)) {
                foreach ($newQuestionL10n as $lang => $texts) {
                    $questionText = $texts['question'] ?? '';
                    $questionHelp = $texts['help'] ?? '';
                    $copyQuestionTextValues[$lang] = new \LimeSurvey\Datavalueobjects\CopyQuestionTextValues($questionText, $questionHelp);
                }
            }

            $copyQuestionValues = new \LimeSurvey\Datavalueobjects\CopyQuestionValues();
            $copyQuestionValues->setOSurvey($oSurvey);
            $copyQuestionValues->setQuestionCode($newTitle);
            $copyQuestionValues->setQuestionGroupId((int)Yii::app()->request->getParam('gid'));
            $copyQuestionValues->setQuestiontoCopy($oQuestion);
            if (!empty($copyQuestionTextValues)) {
                $copyQuestionValues->setQuestionL10nData($copyQuestionTextValues);
            }
            $questionPosition = Yii::app()->request->getParam('questionposition');
            if ($questionPosition === '') { //this means "at the end"
                $questionPosition = -1; //integer indicator for "end"
            }
            //first ensure that all questions for the group have a question_order>0 and possibly set to this state
            Question::setQuestionOrderForGroup($questionGroupId);
            switch ((int)$questionPosition) {
                case -1: //at the end
                    $newQuestionPosition = Question::getHighestQuestionOrderNumberInGroup($questionGroupId) + 1;
                    break;
                case 0: //at beginning
                    //set all existing order numbers to +1, and the copied question to order number 1
                    Question::increaseAllOrderNumbersForGroup($questionGroupId);
                    $newQuestionPosition = 1;
                    break;
                default: //all other cases means after question X (the value coming from frontend is already correct)
                    Question::increaseAllOrderNumbersForGroup($questionGroupId, $questionPosition);
                    $newQuestionPosition = $questionPosition;
            }
            $copyQuestionValues->setQuestionPositionInGroup($newQuestionPosition);

            $copyQuestionService = new \LimeSurvey\Models\Services\CopyQuestion($copyQuestionValues);
            $copyOptions['copySubquestions'] = (int)Yii::app()->request->getParam('copysubquestions') === 1;
            $copyOptions['copyAnswerOptions'] = (int)Yii::app()->request->getParam('copyanswers') === 1;
            $copyOptions['copyDefaultAnswers'] = (int)Yii::app()->request->getParam('copydefaultanswers') === 1;
            $copyOptions['copySettings'] = (int)Yii::app()->request->getParam('copyattributes') === 1;
            if ($copyQuestionService->copyQuestion($copyOptions)) {
                App()->user->setFlash('success', gT("Saved copied question"));
                $newQuestion = $copyQuestionService->getNewCopiedQuestion();
                $this->redirect(
                    $this->createUrl(
                        'questionAdministration/view/',
                        array(
                            'surveyid' => $surveyId,
                            'gid' => $newQuestion->gid,
                            'qid' => $newQuestion->qid
                        )
                    )
                );
            } else {
                App()->user->setFlash('error', gT("Could not save copied question"));
            }
        }

        Yii::app()->getClientScript()->registerScript(
            'editorfiletype',
            "editorfiletype ='javascript';",
            CClientScript::POS_HEAD
        );
        App()->getClientScript()->registerScriptFile(
            App()->getConfig('adminscripts') . 'questionEditor.js',
            CClientScript::POS_END
        );
        PrepareEditorScript(true, $this);
        App()->session['FileManagerContext'] = "edit:survey:{$surveyId}";
        initKcfinder();
        // Add <input> with JSON as value, used by JavaScript.
        $aData['jsVariablesHtml'] = $this->renderPartial(
            '/admin/survey/Question/_subQuestionsAndAnwsersJsVariables',
            [
                'qid'               => $oQuestion->qid,
                'anslangs'          => $oQuestion->survey->allLanguages,
                // TODO
                'assessmentvisible' => false,
                'scalecount'        => $oQuestion->questionType->answerscales
            ],
            true
        );
        $this->aData = $aData;
        $this->render('copyQuestionForm', $aData);
    }

    /**
     * Get HTML for advanced settings.
     * Called with Ajax after question type is selected.
     *
     * @param int $surveyId
     * @param string $questionType One-char string
     * @param string $questionTheme
     * @param int $questionId Null or 0 if new question is being created.
     * @return void
     */
    public function actionGetAdvancedSettingsHTML(int $surveyId, string $questionType, string $questionTheme = null, $questionId = null)
    {
        if (empty($questionType)) {
            throw new CHttpException(405, 'Internal error: No question type');
        }
        // @todo Difference between create and update permissions?
        if (!Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'update')) {
            throw new CHttpException(403, gT('No permission'));
        }
        Yii::app()->loadHelper("admin.htmleditor");
        // NB: This works even when $questionId is null (get default question values).
        $question = $this->getQuestionObject($questionId, $questionType, null, $questionTheme);
        if ($questionId) {
            // NB: Could happen if user manipulates request.
            if (!Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', 'update')) {
                throw new CHttpException(403, gT('No permission'));
            }
        }
        $advancedSettings = $this->getAdvancedOptions(
            $question->qid,
            $questionType,
            $questionTheme
        );
        $this->renderPartial(
            "advancedSettings",
            [
                'advancedSettings'  => $advancedSettings,
                'question'         => $question,
                'oSurvey'           => $question->survey,
            ]
        );
    }

    /**
     * Get HTML for extra options (subquestions/answers).
     * Called with Ajax after question type is selected.
     *
     * @param int $surveyId
     * @param string $questionType One-char string
     * @param int $questionId Null or 0 if new question is being created.
     * @return void
     */
    public function actionGetExtraOptionsHTML(int $surveyId, string $questionType, $questionId = null)
    {
        if (empty($questionType)) {
            throw new CHttpException(405, 'Internal error: No question type');
        }
        // @todo Difference between create and update permissions?
        if (!Permission::model()->hasSurveyPermission($surveyId, 'surveycontent', 'update')) {
            throw new CHttpException(403, gT('No permission'));
        }
        Yii::app()->loadHelper("admin.htmleditor");
        // NB: This works even when $questionId is null (get default question values).
        $question = $this->getQuestionObject($questionId, $questionType);
        if ($questionId) {
            // NB: Could happen if user manipulates request.
            if (!Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', 'update')) {
                throw new CHttpException(403, gT('No permission'));
            }
        }
        $this->renderPartial(
            "extraOptions",
            [
                'question'         => $question,
                'survey'           => $question->survey,
            ]
        );
    }

    /**
     * This function prepares the data for label set details
     *
     * @param int $lid
     * @return void
     */
    public function actionGetLabelsetDetails($lid)
    {
        $labelSet = LabelSet::model()->find('lid=:lid', array(':lid' => $lid));

        $result = [];
        $languages = [];

        if ($labelSet !== null) {
            $usedLanguages = explode(' ', (string) $labelSet->languages);

            foreach ($usedLanguages as $sLanguage) {
                $result[$sLanguage] = array_map(
                    function ($attribute) {
                        return \viewHelper::flatten($attribute);
                    },
                    $labelSet->attributes
                );
                foreach ($labelSet->labels as $oLabel) {
                    $result[$sLanguage]['labels'][] = $oLabel->getTranslated($sLanguage);
                };
                $languages[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
            };
        }

        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success'   => count($result) > 0,
                    'results'   => $result,
                    'languages' => $languages
                ],
            ),
            false,
            false
        );
    }

    /**
     * This function prepares the data for labelset
     *
     * @param int $sid
     * @param int $match
     * @return void
     */
    public function actionGetLabelsetPicker($sid, $match = 0, $language = null)
    {
        $criteria = new CDbCriteria();
        if ($match == 1 && !empty($language)) {
            $criteria->addCondition('languages LIKE :language');
            $criteria->params = [':language' => '%' . $language . '%'];
        }

        $labelSets = LabelSet::model()->findAll($criteria);
        // Create languagespecific array
        $result = [];
        foreach ($labelSets as $labelSet) {
            $result[] = array_map(
                function ($attribute) {
                    return \viewHelper::flatten($attribute);
                },
                $labelSet->attributes
            );
        }

        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success'   => count($result) > 0,
                    'labelsets' => $result
                ],
            ),
            false,
            false
        );
    }

    /**
     * Check if label set can be replaced without problems
     *
     * @param int $lid
     * @param array $languages
     * @param boolean $checkAssessments
     * @return void
     */
    public function actionCheckLabel($lid, $languages, $checkAssessments)
    {
        $labelSet = LabelSet::model()->find('lid=:lid', array(':lid' => $lid));
        $label = Label::model()->count('lid=:lid AND assessment_value<>0', array(':lid' => $lid));
        $labelSetLangauges = explode(' ', (string) $labelSet->languages);
        $errorMessages = [];
        if ($checkAssessments && $label) {
            $errorMessages[] = gT('The existing label set has assessment values assigned.') . '<strong>' . gT('If you replace the label set the existing asssessment values will be lost.') . '</strong>';
        }
        if (count(array_diff($labelSetLangauges, $languages))) {
            $errorMessages[] = gT('The existing label set has different/more languages.') . '<strong>' . gT('If you replace the label set these translations will be lost.') . '</strong>';
        }
        if (count($errorMessages)) {
            foreach ($errorMessages as $errorMessage) {
                echo  $errorMessage . '<br>';
            }
            eT('Do you really want to continue?');
        } else {
            eT('You are about to replace an existing label set with the current answer options.');
            echo '<br>';
            eT('Continue?');
        }
    }

    /** @todo The following functions should be moved to model or a service class ++++++++++++++++++++++++++ */


    /**
     * Returns true if $class is a valid CSS class (alphanumeric + '-' and '_')
     *
     * @param string $class
     * @return bool
     */
    protected function isValidCSSClass($class)
    {
        $class = str_replace(['-', '_'], '', $class);
        return ctype_alnum($class);
    }

    /**
     * Set the other state for selected Questions
     *
     * @param array $aQids All question id's affected
     * @param string $sOther the "other" value 'Y' or 'N'
     * @param int $iSid survey ID
     */
    public static function setMultipleQuestionOtherState($aQids, $sOther, $iSid)
    {
        foreach ($aQids as $sQid) {
            $iQid = (int)$sQid;
            $oQuestion = Question::model()->findByPk(["qid" => $iQid], 'sid=:sid', [':sid' => $iSid]);
            // Only set the other state for question types that have this attribute (and no parent_qid)
            if ($oQuestion->getAllowOther()) {
                $oQuestion->other = $sOther;
                $oQuestion->save();
            }
        }
    }

    /**
     * Set the mandatory state for selected Questions
     *
     * @param array $aQids All question id's affected
     * @param string $sMandatory The mandatory va
     * @param int $iSid survey ID
     */
    public static function setMultipleQuestionMandatoryState($aQids, $sMandatory, $iSid)
    {
        foreach ($aQids as $sQid) {
            $iQid = (int)$sQid;
            $oQuestion = Question::model()->findByPk(["qid" => $iQid], 'sid=:sid', [':sid' => $iSid]);
            // These are the questions types that have no mandatory property - so ignore them
            if ($oQuestion->type != Question::QT_X_TEXT_DISPLAY && $oQuestion->type != Question::QT_VERTICAL_FILE_UPLOAD) {
                $oQuestion->mandatory = $sMandatory;
                $oQuestion->save();
            }
        }
    }

    /**
     * Change the question group/order position of multiple questions
     *
     * @param array $aQids all question id's affected
     * @param int $iQuestionOrder the desired position
     * @param QuestionGroup $oQuestionGroup the desired QuestionGroup
     * @throws CException
     */
    public static function changeMultipleQuestionPositionAndGroup($aQids, $iQuestionOrder, $oQuestionGroup)
    {
        $oTransaction = Yii::app()->db->beginTransaction();
        try {
            // Now, we push each question to the new question group
            // And update positions
            foreach ($aQids as $sQid) {
                // Question basic infos
                $iQid = (int)$sQid;
                $oQuestion = Question::model()->findByAttributes(['qid' => $iQid]); // Question object
                $oldGid = $oQuestion->gid; // The current GID of the question
                $oldOrder = $oQuestion->question_order; // Its current order

                // First, we update all the positions of the questions in the current group of the question
                // If they were after the question, we must decrease by one their position
                Question::model()->updateCounters(
                    ['question_order' => -1],
                    [
                        'condition' => 'gid=:gid AND question_order>=:order',
                        'params'    => [':gid' => $oldGid, ':order' => $oldOrder]
                    ]
                );

                // Then, we must update all the position of the question in the new group of the question
                // If they will be after the question, we must increase their position
                Question::model()->updateCounters(
                    ['question_order' => 1],
                    [
                        'condition' => 'gid=:gid AND question_order>=:order',
                        'params'    => [':gid' => $oQuestionGroup->gid, ':order' => $iQuestionOrder]
                    ]
                );

                // Then we move all the questions with the request QID (same question in different langagues)
                // to the new group, with the righ postion
                Question::model()->updateAll(
                    ['question_order' => $iQuestionOrder, 'gid' => $oQuestionGroup->gid],
                    'qid=:qid',
                    [':qid' => $iQid]
                );
                // Then we update its subquestions
                Question::model()->updateAll(
                    ['gid' => $oQuestionGroup->gid],
                    'parent_qid=:parent_qid',
                    [':parent_qid' => $iQid]
                );

                $iQuestionOrder++;
            }
            $oTransaction->commit();
        } catch (Exception $e) {
            $oTransaction->rollback();
        }
    }

    /**
     * Gets default value(s) for a question or subquestion from table defaultvalue_l10ns
     *
     * @param int $iSurveyID
     * @param int $gid
     * @param int $qid
     * @return array Array with defaultValues
     */
    public static function getDefaultValues(int $iSurveyID, int $gid, int $qid)
    {
        $aDefaultValues = [];
        $oQuestion = Question::model()->findByAttributes(['qid' => $qid, 'gid' => $gid,]);
        $aQuestionAttributes = $oQuestion->attributes;
        $aQuestionTypeMetadata = QuestionType::modelsAttributes();
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        foreach ($oSurvey->allLanguages as $language) {
            $aDefaultValues[$language] = [];
            $aDefaultValues[$language][$aQuestionAttributes['type']] = [];

            // If there are answerscales
            if ($aQuestionTypeMetadata[$aQuestionAttributes['type']]['answerscales'] > 0) {
                for ($scale_id = 0; $scale_id < $aQuestionTypeMetadata[$aQuestionAttributes['type']]['answerscales']; $scale_id++) {
                    $aDefaultValues[$language][$aQuestionAttributes['type']][$scale_id] = [];

                    $defaultvalue = DefaultValue::model()->with('defaultvaluel10ns')->find(
                        'specialtype = :specialtype AND qid = :qid AND scale_id = :scale_id AND defaultvaluel10ns.language =:language',
                        [
                            ':specialtype' => '',
                            ':qid'         => $qid,
                            ':scale_id'    => $scale_id,
                            ':language'    => $language,
                        ]
                    );
                    $defaultvalue = !empty($defaultvalue->defaultvaluel10ns) && array_key_exists(
                        $language,
                        $defaultvalue->defaultvaluel10ns
                    ) ? $defaultvalue->defaultvaluel10ns[$language]->defaultvalue : null;
                    $aDefaultValues[$language][$aQuestionAttributes['type']][$scale_id]['defaultvalue'] = $defaultvalue;

                    $answerresult = Answer::model()->with('answerl10ns')->findAll(
                        'qid = :qid AND answerl10ns.language = :language',
                        [
                            ':qid'      => $qid,
                            ':language' => $language
                        ]
                    );
                    $aDefaultValues[$language][$aQuestionAttributes['type']][$scale_id]['answers'] = $answerresult;

                    if ($aQuestionAttributes['other'] === 'Y') {
                        $defaultvalue = DefaultValue::model()->with('defaultvaluel10ns')->find(
                            'specialtype = :specialtype AND qid = :qid AND scale_id = :scale_id AND defaultvaluel10ns.language =:language',
                            [
                                ':specialtype' => 'other',
                                ':qid'         => $qid,
                                ':scale_id'    => $scale_id,
                                ':language'    => $language,
                            ]
                        );
                        $defaultvalue = !empty($defaultvalue->defaultvaluel10ns) && array_key_exists(
                            $language,
                            $defaultvalue->defaultvaluel10ns
                        ) ? $defaultvalue->defaultvaluel10ns[$language]->defaultvalue : null;
                        $aDefaultValues[$language][$aQuestionAttributes['type']]['Ydefaultvalue'] = $defaultvalue;
                    }
                }
            }

            // If there are subquestions and no answerscales
            if (
                $aQuestionTypeMetadata[$aQuestionAttributes['type']]['answerscales'] == 0 &&
                $aQuestionTypeMetadata[$aQuestionAttributes['type']]['subquestions'] > 0
            ) {
                for ($scale_id = 0; $scale_id < $aQuestionTypeMetadata[$aQuestionAttributes['type']]['subquestions']; $scale_id++) {
                    $aDefaultValues[$language][$aQuestionAttributes['type']][$scale_id] = [];

                    $sqresult = Question::model()
                        ->with('questionl10ns')
                        ->findAll(
                            'sid = :sid AND gid = :gid AND parent_qid = :parent_qid AND scale_id = :scale_id AND questionl10ns.language =:language',
                            [
                                ':sid'        => $iSurveyID,
                                ':gid'        => $gid,
                                ':parent_qid' => $qid,
                                ':scale_id'   => 0,
                                ':language'   => $language
                            ]
                        );

                    $aDefaultValues[$language][$aQuestionAttributes['type']][$scale_id]['sqresult'] = [];

                    $options = [];
                    if ($aQuestionAttributes['type'] == Question::QT_M_MULTIPLE_CHOICE || $aQuestionAttributes['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                        $options = ['' => gT('(No default value)'), 'Y' => gT('Checked')];
                    }

                    foreach ($sqresult as $aSubquestion) {
                        $defaultvalue = DefaultValue::model()
                            ->with('defaultvaluel10ns')
                            ->find(
                                'specialtype = :specialtype AND qid = :qid AND sqid = :sqid AND scale_id = :scale_id AND defaultvaluel10ns.language =:language',
                                [
                                    ':specialtype' => '',
                                    ':qid'         => $qid,
                                    ':sqid'        => $aSubquestion['qid'],
                                    ':scale_id'    => $scale_id,
                                    ':language'    => $language
                                ]
                            );
                        $defaultvalue = !empty($defaultvalue->defaultvaluel10ns) && array_key_exists(
                            $language,
                            $defaultvalue->defaultvaluel10ns
                        ) ? $defaultvalue->defaultvaluel10ns[$language]->defaultvalue : null;

                        $question = $aSubquestion->questionl10ns[$language]->question;
                        $aSubquestion = $aSubquestion->attributes;
                        $aSubquestion['question'] = $question;
                        $aSubquestion['defaultvalue'] = $defaultvalue;
                        $aSubquestion['options'] = $options;

                        $aDefaultValues[$language][$aQuestionAttributes['type']][$scale_id]['sqresult'][] = $aSubquestion;
                    }
                }
            }
            if (
                $aQuestionTypeMetadata[$aQuestionAttributes['type']]['answerscales'] == 0 &&
                $aQuestionTypeMetadata[$aQuestionAttributes['type']]['subquestions'] == 0
            ) {
                $defaultvalue = DefaultValue::model()
                    ->with('defaultvaluel10ns')
                    ->find(
                        'specialtype = :specialtype AND qid = :qid AND scale_id = :scale_id AND defaultvaluel10ns.language =:language',
                        [
                            ':specialtype' => '',
                            ':qid'         => $qid,
                            ':scale_id'    => 0,
                            ':language'    => $language,
                        ]
                    );
                $aDefaultValues[$language][$aQuestionAttributes['type']][0] = !empty($defaultvalue->defaultvaluel10ns) && array_key_exists(
                    $language,
                    $defaultvalue->defaultvaluel10ns
                ) ? $defaultvalue->defaultvaluel10ns[$language]->defaultvalue : null;
            }
        }

        return $aDefaultValues;
    }

    /**
     * Creates a question object
     * This is either an instance of the placeholder model QuestionCreate for new questions,
     * or of Question for already existing ones
     *
     * todo: this should be moved to model ...
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @return Question
     * @throws CException
     */
    private function getQuestionObject($iQuestionId = null, $sQuestionType = null, $gid = null, $questionThemeName = null)
    {
        //todo: this should be done in the action directly
        $iSurveyId = App()->request->getParam('sid') ??
            App()->request->getParam('surveyid') ??
            App()->request->getParam('surveyId');
        /** @var Question|null */
        $oQuestion = Question::model()->findByPk($iQuestionId);

        if (empty($oQuestion)) {
            $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType, $questionThemeName);
        }

        if ($sQuestionType != null) {
            $oQuestion->type = $sQuestionType;
        }

        if ($questionThemeName != null) {
            $oQuestion->question_theme_name = $questionThemeName;
        }

        if ($gid != null) {
            $oQuestion->gid = $gid;
        }

        return $oQuestion;
    }

    /**
     * @todo document me
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @param string $questionThemeName
     *
     * @return void|array
     * @throws CException
     */
    private function getGeneralOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $gid = null,
        $questionThemeName = null
    ) {
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType, $gid, $questionThemeName);
        $result = $oQuestion
            ->getDataSetObject()
            ->getGeneralSettingsArray($oQuestion->qid, $sQuestionType, null, $questionThemeName);
        return $result;
    }

    /**
     * @todo document me.
     * @todo move this function somewhere else, this should not be part of controller ... (e.g. model)
     *
     * @param Question $oQuestion
     * @return array
     */
    private function getCompiledQuestionData(&$oQuestion)
    {
        LimeExpressionManager::StartProcessingPage(false, true);
        $aQuestionDefinition = array_merge($oQuestion->attributes, ['typeInformation' => $oQuestion->questionType]);
        $oQuestionGroup = QuestionGroup::model()->findByPk($oQuestion->gid);
        $aQuestionGroupDefinition = array_merge($oQuestionGroup->attributes, $oQuestionGroup->questiongroupl10ns);

        $aScaledSubquestions = $oQuestion->getOrderedSubQuestions();
        foreach ($aScaledSubquestions as $scaleId => $aSubquestions) {
            $aScaledSubquestions[$scaleId] = array_map(
                function ($oSubQuestion) {
                    return array_merge($oSubQuestion->attributes, $oSubQuestion->questionl10ns);
                },
                $aSubquestions
            );
        }

        $aScaledAnswerOptions = $oQuestion->getOrderedAnswers();
        foreach ($aScaledAnswerOptions as $scaleId => $aAnswerOptions) {
            $aScaledAnswerOptions[$scaleId] = array_map(
                function ($oAnswerOption) {
                    return array_merge($oAnswerOption->attributes, $oAnswerOption->answerl10ns);
                },
                $aAnswerOptions
            );
        }
        $aReplacementData = [];
        $questioni10N = [];
        foreach ($oQuestion->questionl10ns as $lng => $oQuestionI10N) {
            $questioni10N[$lng] = $oQuestionI10N->attributes;

            templatereplace(
                $oQuestionI10N->question,
                [],
                $aReplacementData,
                'Unspecified',
                false,
                $oQuestion->qid
            );

            $questioni10N[$lng]['question_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );

            templatereplace($oQuestionI10N->help, [], $aReplacementData, 'Unspecified', false, $oQuestion->qid);
            $questioni10N[$lng]['help_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );
        }
        LimeExpressionManager::FinishProcessingPage();
        return [
            'question'      => $aQuestionDefinition,
            'questiongroup' => $aQuestionGroupDefinition,
            'i10n'          => $questioni10N,
            'subquestions'  => $aScaledSubquestions,
            'answerOptions' => $aScaledAnswerOptions,
        ];
    }

    /**
     * It returns a preformatted array of advanced settings.
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param string $sQuestionTheme
     * @return array
     * @throws CException
     * @throws Exception
     */
    private function getAdvancedOptions($iQuestionId = null, $sQuestionType = null, $sQuestionTheme = null)
    {
        //here we get a Question object (also if question is new --> QuestionCreate)
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType, null, $sQuestionTheme);

        // Get the advanced settings array
        $advancedSettings = $oQuestion->getAdvancedSettingsWithValues();

        // Group the array in categories
        $questionAttributeHelper = new LimeSurvey\Models\Services\QuestionAttributeHelper();
        $advancedSettings = $questionAttributeHelper->groupAttributesByCategory($advancedSettings);

        // This category is "general setting".
        unset($advancedSettings['Attribute']);

        return $advancedSettings;
    }

    /**
     *
     * todo: this should be moved to model, not a controller function ...
     *
     * @param $oQuestion
     * @return array
     */
    private function getCompiledSurveyInfo($oQuestion)
    {
        $oSurvey = $oQuestion->survey;
        $aQuestionTitles = $oCommand = Yii::app()->db->createCommand()
            ->select('title')
            ->from('{{questions}}')
            ->where('sid=:sid and parent_qid=0')
            ->queryColumn([':sid' => $oSurvey->sid]);
        $isActive = $oSurvey->isActive;
        $questionCount = safecount($aQuestionTitles);
        $groupCount = safecount($oSurvey->groups);

        return [
            "aQuestionTitles" => $aQuestionTitles,
            "isActive"        => $isActive,
            "questionCount"   => $questionCount,
            "groupCount"      => $groupCount,
        ];
    }

    /**
     * Copies the default value(s) set for a question
     *
     * @param Question $oQuestion
     * @param integer $oldQid
     *
     * @return boolean
     * @throws CHttpException
     * @deprecated Functionality moved to CopyQuestion service.
     */
    private function copyDefaultAnswers($oQuestion, $oldQid)
    {
        if (empty($oldQid)) {
            return false;
        }

        $oOldDefaultValues = DefaultValue::model()->with('defaultvaluel10ns')->findAllByAttributes(['qid' => $oldQid]);

        $setApplied['defaultValues'] = array_reduce(
            $oOldDefaultValues,
            function ($collector, $oDefaultValue) use ($oQuestion) {
                $oNewDefaultValue = new DefaultValue();
                $oNewDefaultValue->setAttributes($oDefaultValue->attributes, false);
                $oNewDefaultValue->dvid = null;
                $oNewDefaultValue->qid = $oQuestion->qid;

                if (!$oNewDefaultValue->save()) {
                    throw new CHttpException(
                        500,
                        "Could not save default values. ERRORS:"
                            . print_r($oQuestion->getErrors(), true)
                    );
                }

                foreach ($oDefaultValue->defaultvaluel10ns as $oDefaultValueL10n) {
                    $oNewDefaultValueL10n = new DefaultValueL10n();
                    $oNewDefaultValueL10n->setAttributes($oDefaultValueL10n->attributes, false);
                    $oNewDefaultValueL10n->id = null;
                    $oNewDefaultValueL10n->dvid = $oNewDefaultValue->dvid;
                    if (!$oNewDefaultValueL10n->save()) {
                        throw new CHttpException(
                            500,
                            "Could not save default value I10Ns. ERRORS:"
                                . print_r($oQuestion->getErrors(), true)
                        );
                    }
                }

                return true;
            },
            true
        );
        return true;
    }

    /**
     * @param QuestionTheme[] $questionThemes Question theme List
     * @return array
     * @todo Move to PreviewModalWidget?
     */
    private function getQuestionTypeGroups(array $questionThemes)
    {
        $aQuestionTypeGroups = [];

        uasort($questionThemes, "questionTitleSort");
        foreach ($questionThemes as $questionTheme) {
            $htmlReadyGroup = str_replace(' ', '_', strtolower((string) $questionTheme->group));
            if (!isset($aQuestionTypeGroups[$htmlReadyGroup])) {
                $aQuestionTypeGroups[$htmlReadyGroup] = array(
                    'questionGroupName' => $questionTheme->group
                );
            }
            $imageName = $questionTheme->question_type;
            if ($imageName == ":") {
                $imageName = "COLON";
            } elseif ($imageName == "|") {
                $imageName = "PIPE";
            } elseif ($imageName == "*") {
                $imageName = "EQUATION";
            }
            $questionThemeData = [];
            $questionThemeData['title'] = $questionTheme->title;
            $questionThemeData['name'] = $questionTheme->name;
            $questionThemeData['type'] = $questionTheme->question_type;
            $questionThemeData['detailpage'] = '
                <div class="col-12 currentImageContainer">
                <img src="' . $questionTheme->image_path . '" />
                </div>';
            if ($imageName == 'S') {
                $questionThemeData['detailpage'] = '
                    <div class="col-12 currentImageContainer">
                    <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
                    <img src="' . App()->getConfig('imageurl') . '/screenshots/' . $imageName . '2.png" />
                    </div>';
            }
            $aQuestionTypeGroups[$htmlReadyGroup]['questionTypes'][] = $questionThemeData;
        }
        return $aQuestionTypeGroups;
    }

    /**
     * Checks given answer code is unique.
     * @param string $code
     * @return bool
     */
    public function actionCheckAnswerCodeIsUnique(string $code): bool
    {
        $answer = Answer::model()->getAnswerFromCode($code);
        if ($answer->code !== $code || $answer === null) {
            $isValid = true;
        } else {
            $isValid = false;
        }
        return $isValid;
    }

    /**
     * Checks if given Sub Question Code is unique.
     * @param string $code
     * @return string
     */
    public function actionCheckSubQuestionCodeIsUnique(string $code): string
    {
        return '';
    }

    /**
     * @deprecated in 5.3.17
     * replaced by better name actionValidateQuestionTitle
     */
    public function actionCheckQuestionCodeUniqueness($sid, int $qid, string $code)
    {
        $this->actionCheckQuestionValidateTitle($sid, $qid, $code);
    }

    /**
     * Checks if given Question Code is unique.
     * Echo 'true' if code is unique, otherwise 'false'.
     *
     * @param int $sid Survey id
     * @param int $qid Question id
     * @param string $code Question code (title in db)
     * @return void
     */
    public function actionCheckQuestionValidateTitle($sid, int $qid, string $code)
    {
        $sid = (int) $sid;
        $qid = (int) $qid;
        if (!Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'create')) {
            throw new CHttpException(403, gT('No permission'));
        }

        $survey = Survey::model()->findByPk($sid);
        if (empty($survey)) {
            throw new CHttpException(404, gT("Invalid survey ID"));
        }
        if ($qid) {
            $oQuestion = Question::model()->findByAttributes(['qid' => $qid, 'sid' => $sid]);
            if (empty($oQuestion)) {
                throw new CHttpException(404, gT("Invalid question id"));
            }
            if (!empty($oQuestion->parent_qid)) {
                throw new CHttpException(400, gT("Invalid question id"));
            }
            if ($oQuestion->sid != $sid) {
                throw new CHttpException(400, gT("Invalid question id"));
            }
        } else {
            $oQuestion = $this->getQuestionObject();
            $oQuestion->parent_qid = 0; // Unsure needed it, but we need it's a parent_qid=0
        }
        $oQuestion->title = $code;
        header('Content-Type: application/json');
        if (!$oQuestion->validate(['title'])) {
            echo json_encode(['message' => $oQuestion->getError('title')]);
        } else {
            echo json_encode(['message' => null]);
        }
        Yii::app()->end();
    }

    /**
     * Get HTML for question summary.
     * Called with Ajax after question is saved.
     *
     * @param int $questionId
     * @return void
     */
    public function actionGetSummaryHTML(int $questionId)
    {
        $question = Question::model()->findByPk($questionId);
        if (empty($question)) {
            throw new CHttpException(404, gT("Invalid question id"));
        }
        if (!Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', 'read')) {
            throw new CHttpException(403, gT('No permission'));
        }

        // Use the question's theme if it exists, or a dummy theme if it doesn't
        /** @var QuestionTheme */
        $questionTheme = !empty($question->questionTheme) ? $question->questionTheme : QuestionTheme::getDummyInstance($question->type);

        /** @var array<string,array<mixed>> */
        $advancedSettings = $this->getAdvancedOptions($question->qid, $question->type, $question->question_theme_name);
        // Remove general settings from this array.
        unset($advancedSettings['Attribute']);

        $this->renderPartial(
            "questionSummary",
            [
                'survey' => $question->survey,
                'question' => $question,
                'questionTheme' => $questionTheme,
                'advancedSettings' => $advancedSettings,
                'overviewVisibility' => false,  // Hidden by default
            ]
        );
    }

    /**
     * Returns the selector mode class as string
     * @return string
     */
    private function getSelectorModeClass()
    {
        if (App()->session['questionselectormode'] !== 'default') {
            $selectorModeClass = App()->session['questionselectormode'];
        } else {
            $selectorModeClass = App()->getConfig('defaultquestionselectormode');
        }

        return $selectorModeClass;
    }
}
