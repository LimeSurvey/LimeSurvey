<?php

use LimeSurvey\Models\Services\CopySurveyResources;
use LimeSurvey\Models\Services\FilterImportedResources;

/**
 * Class SurveyAdministrationController
 */
class SurveyAdministrationController extends LSBaseController
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
                'users' => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => [
                    'view',
                    'delete',
                    'index',
                    'insert',
                    'listsurveys',
                    'newSurvey',
                    'regenerateQuestionCode',
                    'renderItemsSelected',
                    'applythemeoptions',
                    'changeMultipleSurveyGroup',
                    'changeMultipleTheme',
                    'deleteMultiple',
                    'fakebrowser',
                    'getAjaxMenuArray',
                    'getAjaxQuestionGroupArray',
                    'getCurrentEditorValues',
                    'getDataSecTextSettings',
                    'getDateFormatOptions',
                    ''
                ],
                'users' => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    /**
     * SurveyAdministrationController constructor.
     * @param $id
     * @param null $module
     * @throws CException
     */
    public function __construct($id, $module = null)
    {
        Yii::app()->request->updateNavigationStack();
        // Make sure viewHelper can be autoloaded
        Yii::import('application.helpers.viewHelper');
        parent::__construct($id, $module);
    }

    /**
     * This part comes from renderWrappedTemplate
     *
     * @param string $view
     * @return bool
     */
    protected function beforeRender($view)
    {
        if (!empty($this->aData['surveyid'])) {
            $this->aData['oSurvey'] = $this->aData['oSurvey'] ?? Survey::model()->findByPk($this->aData['surveyid']);

            // Needed to evaluate EM expressions in question summary
            // See bug #11845
            LimeExpressionManager::SetSurveyId($this->aData['surveyid']);
            LimeExpressionManager::StartProcessingPage(false, true);

            $this->layout = 'layout_questioneditor';
        } else {
            $this->layout = 'main';
        }

        // Used in question editor (pjax).
        App()->getClientScript()->registerPackage('ace');
        App()->getClientScript()->registerPackage('jquery-ace');

        return parent::beforeRender($view);
    }

    /**
     * Load complete view of survey properties and actions specified by $iSurveyID
     *
     * @return void
     *
     * @access public
     * @throws CException
     */
    public function actionView()
    {
        $iSurveyID = $this->getSurveyIdFromGetRequest();

        if (!Permission::model()->hasSurveyPermission((int)$iSurveyID, 'survey', 'read')) {
            Yii::app()->user->setFlash('error', gT("No permission or survey does not exist."));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $beforeSurveyAdminView = new PluginEvent('beforeSurveyAdminView');
        $beforeSurveyAdminView->set('surveyId', $iSurveyID);
        App()->getPluginManager()->dispatchEvent($beforeSurveyAdminView);

        // We load the panel packages for quick actions
        $iSurveyID = sanitize_int($iSurveyID);

        //todo: first check if survey is new and DO NOT try to access model attributes without isset-check ...


        $survey = Survey::model()->findByPk($iSurveyID);  //yii standard is overwritten here ...
        $baselang = $survey->language;

        $aData = array('aAdditionalLanguages' => $survey->additionalLanguages);

        // Reinit LEMlang and LEMsid: ensure LEMlang are set to default lang, surveyid are set to this survey id
        // Ensure Last GetLastPrettyPrintExpression get info from this sid and default lang
        LimeExpressionManager::SetEMLanguage($baselang);
        LimeExpressionManager::SetSurveyId($iSurveyID);
        LimeExpressionManager::StartProcessingPage(false, true);

        if (isset($survey->currentLanguageSettings) && isset($survey->currentLanguageSettings->surveyls_title)) {
            $aData['title_bar']['title'] =
                $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        } else {
            $aData['title_bar']['title'] = 'Unknown_language_title' . " (" . gT("ID") . ":" . $iSurveyID . ")";
        }
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $iSurveyID; //frontend need this to render topbar for the view

        //NOTE this is set because ONLY this leads to render the view surveySummary_view, no need to use in anymore
        // $aData['display']['surveysummary'] = true;

        // Last survey visited
        $userId = App()->user->getId();
        SettingGlobal::setSetting('last_survey_' . $userId, $iSurveyID);

        $aData['surveybar']['buttons']['view'] = true;
        $aData['surveybar']['returnbutton']['url'] = $this->createUrl("surveyAdministration/listsurveys");
        $aData['surveybar']['returnbutton']['text'] = gT('Return to survey list');
        $aData['sidemenu']["survey_menu"] = true;

        // We get the last question visited by user for this survey
        // TODO: getGlobalSetting() DEPRECATED
        $lastquestion = getGlobalSetting('last_question_' . $userId . '_' . $iSurveyID);

        // TODO: getGlobalSetting() DEPRECATED
        $lastquestiongroup = getGlobalSetting('last_question_' . $userId . '_' . $iSurveyID . '_gid');

        if ($lastquestion != null && $lastquestiongroup != null) {
            $aData['showLastQuestion'] = true;
            $iQid = $lastquestion;
            $iGid = $lastquestiongroup;
            $qrrow = Question::model()->findByAttributes(array('qid' => $iQid, 'gid' => $iGid, 'sid' => $iSurveyID));

            $aData['last_question_name'] = $qrrow['title'];
            if (!empty($qrrow->questionl10ns[$baselang]['question'])) {
                $aData['last_question_name'] .= ' : ' . $qrrow->questionl10ns[$baselang]['question'];
            }

            $aData['last_question_link'] =
                $this->createUrl("questionEditor/view/surveyid/$iSurveyID/gid/$iGid/qid/$iQid");
        } else {
            $aData['showLastQuestion'] = false;
        }
        $aData['templateapiversion'] = Template::model()->getTemplateConfiguration(null, $iSurveyID)->getApiVersion();

        $user = User::model()->findByPk(App()->session['loginID']);
        $aData['owner'] = $user->attributes;

        if ((empty($aData['display']['menu_bars']['surveysummary']) || !is_string($aData['display']['menu_bars']['surveysummary'])) && !empty($aData['gid'])) {
            $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
        }

        $this->surveysummary($aData);

        // Display 'Overview' in Green Bar
        $aData['subaction'] = gT('Overview');

        $this->aData = $aData;
        $this->render('sidebody', [
            'sideMenuOpen' => true
        ]);
    }

    /**
     * List Surveys.
     *
     * @return void
     */
    public function actionListsurveys()
    {
        Yii::app()->loadHelper('surveytranslator');
        $aData = array();
        $aData['issuperadmin'] = false;

        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $aData['issuperadmin'] = true;
        }
        $aData['model'] = new Survey('search');
        $aData['groupModel'] = new SurveysGroups('search');

        // Green Bar Page Title
        $aData['pageTitle'] = gT('Survey list');

        // Create Survey Button Url
        $aData['fullpagebar']['listSurveys']['buttons']['createSurvey']['url'] = $this->createUrl("surveyAdministration/newSurvey");
        $aData['fullpagebar']['listSurveys']['buttons']['createSurveyGroup']['url'] = $this->createUrl("admin/surveysgroups/sa/create");

        // Create Survey Groups Button
        $aData['fullpagebar']['listSurveys']['buttons']['createSurveyGroups'] = true;

        // Return Button
        $aData['fullpagebar']['returnbutton']['url'] = 'admin/index';
        $aData['fullpagebar']['returnbutton']['text'] = gT('Back');

        $this->aData = $aData;
        $this->render('listSurveys_view', $aData);
    }

    /**
     * Delete multiple survey
     *
     * @return void
     * @throws CException
     */
    public function actionDeleteMultiple()
    {
        $aSurveys = json_decode(Yii::app()->request->getPost('sItems'));
        $aResults = array();
        foreach ($aSurveys as $iSurveyID) {
            $iSurveyID = sanitize_int($iSurveyID);
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            $aResults[$iSurveyID]['title'] = $oSurvey->correct_relation_defaultlanguage->surveyls_title;
            if (Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete')) {
                $aResults[$iSurveyID]['result'] = Survey::model()->deleteSurvey($iSurveyID);
            } else {
                $aResults[$iSurveyID]['result'] = false;
                $aResults[$iSurveyID]['error'] = gT("User does not have valid permissions");
            }
        }
        $this->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array(
                'aResults' => $aResults,
                'successLabel' => gT('Deleted')
            )
        );
    }

    /**
     * Render selected items for massive action
     *
     * @return void
     */
    public function actionRenderItemsSelected()
    {
        $aSurveys = json_decode(Yii::app()->request->getPost('$oCheckedItems'));
        $aResults = [];
        $tableLabels = array(gT('Survey ID'), gT('Survey title'), gT('Status'));
        foreach ($aSurveys as $iSurveyID) {
            if (!is_numeric($iSurveyID)) {
                continue;
            }
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            $aResults[$iSurveyID]['title'] = $oSurvey->correct_relation_defaultlanguage->surveyls_title;
            $aResults[$iSurveyID]['result'] = 'selected';
        }

        $this->renderPartial(
            'ext.admin.grid.MassiveActionsWidget.views._selected_items',
            array(
                'aResults' => $aResults,
                'successLabel' => gT('Selected'),
                'tableLabels' => $tableLabels
            )
        );
    }

    /**
     * Regeerates the question code
     * Automatically renumbers the "question codes" so that they follow
     * a methodical numbering method.
     *
     * @return void
     *
     */
    public function actionRegenerateQuestionCodes()
    {
        $iSurveyID = $this->getSurveyIdFromGetRequest();
        $sSubAction = Yii::app()->request->getParam('subaction');

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyID));
        }
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if ($oSurvey->isActive) {
            Yii::app()->setFlashMessage(gT("You can't update question code for an active survey."), 'error');
            $this->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyID));
        }

        //check subaction
        if (!($sSubAction === 'straight' || $sSubAction === 'bygroup')) {
            Yii::app()->setFlashMessage(gT("Invalid parameters."), 'error');
            $this->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyID));
        }

        //Automatically renumbers the "question codes" so that they follow
        //a methodical numbering method
        $iQuestionNumber = 1;
        $iGroupNumber = 0;
        $iGroupSequence = 0;
        $oQuestions = Question::model()
            ->with(['group' => ['alias' => 'g'], 'questionl10ns'])
            ->findAll(
                array(
                    'select' => 't.qid,t.gid',
                    'condition' => "t.sid=:sid and questionl10ns.language=:language and parent_qid=0",
                    'order' => 'g.group_order, question_order',
                    'params' => array(':sid' => $iSurveyID, ':language' => $oSurvey->language)
                )
            );

        foreach ($oQuestions as $oQuestion) {
            if ($sSubAction == 'bygroup' && $iGroupNumber != $oQuestion->gid) {
                //If we're doing this by group, restart the numbering when the group number changes
                $iQuestionNumber = 1;
                $iGroupNumber = $oQuestion->gid;
                $iGroupSequence++;
            }
            $sNewTitle = (($sSubAction == 'bygroup') ? ('G' . $iGroupSequence) : '') . "Q" .
                str_pad($iQuestionNumber, 5, "0", STR_PAD_LEFT);
            Question::model()->updateAll(array('title' => $sNewTitle), 'qid=:qid', array(':qid' => $oQuestion->qid));
            $iQuestionNumber++;
            $iGroupNumber = $oQuestion->gid;
        }
        Yii::app()->setFlashMessage(gT("Question codes were successfully regenerated."));
        LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
        $this->redirect(array('surveyAdministration/view/surveyid/' . $iSurveyID));
    }

    /**
     * This function prepares the view for a new survey
     *
     * @return void
     */
    public function actionNewSurvey()
    {
        if (!Permission::model()->hasGlobalPermission('surveys', 'create')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $survey = new Survey();
        // set 'inherit' values to survey attributes
        $survey->setToInherit();

        App()->getClientScript()->registerPackage('jquery-json');
        App()->getClientScript()->registerPackage('bootstrap-switch');
        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('admin.htmleditor');

        $aData = $this->generalTabNewSurvey();
        $aData = array_merge($aData, $this->getGeneralTemplateData(0));
        $aData['esrow'] =  $this->fetchSurveyInfo('newsurvey');

        $aData['oSurvey'] = $survey;
        $aData['bShowAllOptions'] = true;
        $aData['bShowInherited'] = true;
        $oSurveyOptions = $survey;
        $oSurveyOptions->bShowRealOptionValues = false;
        $oSurveyOptions->setOptions();
        $aData['oSurveyOptions'] = $oSurveyOptions->oOptionLabels;

        $aData['optionsOnOff'] = array(
            'Y' => gT('On', 'unescaped'),
            'N' => gT('Off', 'unescaped'),
        );

        $aData['optionsAdmin'] = array(
            'default' => gT('Default', 'unescaped'),
            'owner' => gT('Current user', 'unescaped'),
            'custom' => gT('Custom', 'unescaped'),
        );

        $testLanguages = getLanguageDataRestricted(true, 'short');

        $aData['edittextdata']['listLanguagesCode'] = $testLanguages;
        $aData['edittextdata']['aSurveyGroupList'] = SurveysGroups::getSurveyGroupsList();

        $arrayed_data = array();
        $arrayed_data['oSurvey'] = $survey;
        $arrayed_data['data'] = $aData;
        $arrayed_data['title_bar']['title'] = gT('New survey');

        // Green Bar Page Title
        $aData['pageTitle'] = gT("Create, import, or copy survey");

        $aData['fullpagebar']['savebutton']['form'] = 'addnewsurvey';
        $aData['fullpagebar']['white_closebutton']['url'] = Yii::app()->createUrl('admin/index'); // Close button

        $this->aData = $aData;

        //this is not the layout
        $this->render('newSurvey_view', [
            'arrayed_data' => $arrayed_data
        ]);
    }

    /**
     * Saves the new survey after the creation screen is submitted
     *
     * @return string
     * @throws CException
     */
    public function actionInsert()
    {
        if (Permission::model()->hasGlobalPermission('surveys', 'create')) {
            $user = Yii::app()->user;

            // CHECK IF USER OWNS PREVIOUS SURVEYS BEGIN
            if ($user !== null) {
                $userid = (int) $user->getId();
                $ownsPreviousSurveys  = Survey::model()->findByAttributes(array('owner_id' => $userid));
                if ($ownsPreviousSurveys === null) {
                    $ownsPreviousSurveys = false;
                } else {
                    $ownsPreviousSurveys = true;
                }
            }
            // CHECK IF USER OWNS PREVIOUS SURVEYS END

            // Check if survey title was set
            $surveyTitle = Yii::app()->request->getPost('surveyls_title');
            $surveyTitle = trim($surveyTitle);
            if ($surveyTitle == '') {
                $alertError = gT("Survey could not be created because it did not have a title");

                return Yii::app()->getController()->renderPartial(
                    '/admin/super/_renderJson',
                    array(
                        'data' => array(
                            'alertData' => $alertError,
                            'missingField' => 'surveyls_title'
                        )
                    ),
                    false,
                    false
                );
            }

            Yii::app()->loadHelper("surveytranslator");

            $simpleSurveyValues = new \LimeSurvey\Datavalueobjects\SimpleSurveyValues();
            $baseLanguage = App()->request->getPost('language');
            if ($baseLanguage === null) {
                $baseLanguage = 'en'; //shoulb be const somewhere ... or get chosen language from user
            }
            $simpleSurveyValues->baseLanguage = $baseLanguage;
            $simpleSurveyValues->surveyGroupId = (int) App()->request->getPost('gsid', '1');
            $simpleSurveyValues->title = $surveyTitle;

            $administrator = Yii::app()->request->getPost('administrator');
            if ($administrator == 'custom') {
                $simpleSurveyValues->admin = Yii::app()->request->getPost('admin');
                $simpleSurveyValues->adminEmail = Yii::app()->request->getPost('adminemail');
            }
            $overrideAdministrator = ($administrator != 'owner');

            $surveyCreator = new \LimeSurvey\Models\Services\CreateSurvey(new Survey(), new SurveyLanguageSetting());
            $newSurvey = $surveyCreator->createSimple(
                $simpleSurveyValues,
                (int)Yii::app()->user->getId(),
                Permission::model(),
                $overrideAdministrator
            );
            if (!$newSurvey) {
                Yii::app()->setFlashMessage(gT("Survey could not be created."), 'error');
                $this->redirect(Yii::app()->request->urlReferrer);
            }

            $iNewSurveyid = $newSurvey->sid;
            $this->aData['surveyid'] = $newSurvey->sid; //import to render correct layout in before_render

            // This will force the generation of the entry for survey group
            TemplateConfiguration::checkAndcreateSurveyConfig($iNewSurveyid);

            $createSample = SettingsUser::getUserSettingValue('createsample');
            if ($createSample === null || $createSample === 'default') {
                $createSample = Yii::app()->getConfig('createsample');
            }

            // Figure out destination
            if ($createSample) {
                $iNewGroupID = $this->createSampleGroup($iNewSurveyid);
                $iNewQuestionID = $this->createSampleQuestion($iNewSurveyid, $iNewGroupID);

                Yii::app()->setFlashMessage(gT("Your new survey was created. 
                We also created a first question group and an example question for you."), 'info');
                $redirecturl = $this->getSurveyAndSidemenueDirectionURL(
                    $iNewSurveyid,
                    $iNewGroupID,
                    $iNewQuestionID,
                    'structure'
                );
            } elseif (!$ownsPreviousSurveys) {
                // SET create question and create question group as default view.
                $redirecturl = $this->createUrl(
                    'questionGroupsAdministration/add/',
                    ['surveyid' => $iNewSurveyid]
                );
            } else {
                $redirecturl = $this->createUrl(
                    'surveyAdministration/view/',
                    ['iSurveyID' => $iNewSurveyid]
                );
                Yii::app()->setFlashMessage(gT("Your new survey was created."), 'info');
            }

            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => array(
                        'redirecturl' => $redirecturl,
                    )
                ),
                false,
                false
            );
        }
        $this->redirect(Yii::app()->request->urlReferrer);
    }

    /**
     * todo: what is this? why do we need it?
     *(it'S just an js-script alert window rendert here ...)
     *
     * @return void
     * @throws CException
     */
    public function actionFakebrowser()
    {
        $this->renderPartial('/admin/survey/newSurveyBrowserMessage', array());
    }

    /**
     * Function responsible to import survey resources from a '.zip' file.
     *
     * @todo is this function used? the function editlocalsetting does not exists  (also not in old controller surveyadmin)
     *
     * @access public
     * @return void
     */
    public function actionImportsurveyresources()
    {
        $iSurveyID = Yii::app()->request->getPost('surveyid');
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        if (!empty($iSurveyID)) {
            if (Yii::app()->getConfig('demoMode')) {
                Yii::app()->user->setFlash('error', gT("Demo mode only: Uploading files is disabled in this system."));
                $this->redirect(array('surveyAdministration/editlocalsettings/surveyid/' . $iSurveyID));
            }

            // Create temporary directory
            // If dangerous content is unzipped
            // then no one will know the path
            Yii::import('application.helpers.common_helper', true);
            $extractdir = createRandomTempDir();
            $zipfilename = $_FILES['the_file']['tmp_name'];
            $basedestdir = Yii::app()->getConfig('uploaddir') . "/surveys";
            $destdir = $basedestdir . "/$iSurveyID/";

            Yii::app()->loadLibrary('admin.pclzip');
            $zip = new PclZip($zipfilename);

            if (!is_writeable($basedestdir)) {
                Yii::app()->user->setFlash('error', sprintf(
                    gT("Incorrect permissions in your %s folder."),
                    $basedestdir
                ));
                $this->redirect(array('surveyAdministration/editlocalsettings/surveyid/' . $iSurveyID));
            }


            if (!is_dir($destdir)) {
                mkdir($destdir);
            }

            $aImportedFilesInfo = array();
            $aErrorFilesInfo = array();

            if (is_file($zipfilename)) {
                if ($zip->extract($extractdir) <= 0) {
                    Yii::app()->user->setFlash(
                        'error',
                        gT("This file is not a valid ZIP file archive. Import failed. ") . $zip->errorInfo(true)
                    );
                    $this->redirect(array('surveyAdministration/editlocalsettings/surveyid/' . $iSurveyID));
                }
                // now read tempdir and copy authorized files only
                $folders = array('flash', 'files', 'images');

                $filteredImportedResources = new FilterImportedResources();

                foreach ($folders as $folder) {
                    list($_aImportedFilesInfo, $_aErrorFilesInfo) = $filteredImportedResources->filterImportedResources(
                        $extractdir . "/" . $folder,
                        $destdir . $folder
                    );
                    $aImportedFilesInfo = array_merge($aImportedFilesInfo, $_aImportedFilesInfo);
                    $aErrorFilesInfo = array_merge($aErrorFilesInfo, $_aErrorFilesInfo);
                }

                // Deletes the temp directory
                rmdirr($extractdir);

                // Delete the temporary file
                unlink($zipfilename);

                if (empty($aErrorFilesInfo) && empty($aImportedFilesInfo)) {
                    Yii::app()->user->setFlash(
                        'error',
                        gT("This ZIP archive contains no valid Resources files. Import failed.")
                    );
                    $this->redirect(array('surveyAdministration/rendersidemenulink/', 'surveyid' => $iSurveyID, 'subaction' => 'resources'));
                }
            } else {
                Yii::app()->setFlashMessage(
                    gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."),
                    'error'
                );
                $this->redirect(array('surveyAdministration/rendersidemenulink/', 'surveyid' => $iSurveyID, 'subaction' => 'resources'));
            }
            $aData = array(
                'aErrorFilesInfo' => $aErrorFilesInfo,
                'aImportedFilesInfo' => $aImportedFilesInfo,
                'surveyid' => $iSurveyID
            );
            $aData['display']['menu_bars']['surveysummary'] = true;
            $survey = Survey::model()->findByPk($iSurveyID);
            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";

            $this->aData = $aData;
            $this->render('importSurveyResources_view', $this->aData);
        }
    }

    /**
     * Function to call current Editor Values by Ajax
     *
     * @param integer $sid Given Survey ID
     *
     * is still used in sidemenu Text elemnts (see vue.js ajaxcall)
     *
     * @return JSON
     * @throws CException
     */
    public function actionGetCurrentEditorValues($sid)
    {
        //Permission check
        if (!Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read')) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'message' => gT("No permission"),
                        'debug' => null
                    ]
                ),
                false,
                false
            );
        }

        $iSurveyId = (int)$sid;
        $oSurvey = Survey::model()->findByPk($iSurveyId);

        $updatePermission = $oSurvey == null
            ? Permission::model()->hasGlobalPermission('surveys', 'create')
            : Permission::model()->hasSurveyPermission($iSurveyId, 'surveylocale', 'update');

        $aLanguages = [];
        $aReturner = [
            "surveyTitle" => [],
            "welcome" => [],
            "description" => [],
            "endText" => [],
            "endUrl" => [],
            "endUrlDescription" => [],
            "dateFormat" => [],
            "decimalDivider" => [],
            "permissions" => [
                "update" => $updatePermission,
                "editorpreset" => Yii::app()->session['htmleditormode'],
            ]
        ];

        if ($oSurvey == null) {
            $defaultLanguage = App()->getConfig('defaultlang');
            $aLanguageDetails = getLanguageDetails($defaultLanguage);
            $aLanguages = [$defaultLanguage => getLanguageCodefromLanguage($defaultLanguage)];
            $aReturner["surveyTitle"][$defaultLanguage] = "";
            $aReturner["welcome"][$defaultLanguage] = "";
            $aReturner["description"][$defaultLanguage] = "";
            $aReturner["endText"][$defaultLanguage] = "";
            $aReturner["endUrl"][$defaultLanguage] = "";
            $aReturner["endUrlDescription"][$defaultLanguage] = "";
            $aReturner["dateFormat"][$defaultLanguage] = $aLanguageDetails['dateformat'];
            $aReturner["decimalDivider"][$defaultLanguage] = $aLanguageDetails['radixpoint'];

            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                [
                    'data' => [
                        "textdata" => $aReturner,
                        "languages" => $aLanguages
                    ]
                ],
                false,
                false
            );
        }

        foreach ($oSurvey->allLanguages as $sLanguage) {
            $aLanguages[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
            $aReturner["surveyTitle"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_title;
            $aReturner["welcome"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_welcometext;
            $aReturner["description"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_description;
            $aReturner["endText"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_endtext;
            $aReturner["endUrl"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_url;
            $aReturner["endUrlDescription"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_urldescription;
            $aReturner["dateFormat"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_dateformat;
            $aReturner["decimalDivider"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_numberformat;
        }

        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            [
                'data' => [
                    "textdata" => $aReturner,
                    "languages" => $aLanguages
                ]
            ],
            false,
            false
        );
    }

    /**
     * Massive action in ListSurveysWidget ...
     * Ajax request
     *
     * @return void
     * @throws CException
     */
    public function actionChangeMultipleTheme()
    {
        $sSurveys = $_POST['sItems'];
        $aSIDs = json_decode($sSurveys);
        $aResults = array();

        $sTemplate = App()->request->getPost('theme');

        foreach ($aSIDs as $iSurveyID) {
            $aResults = $this->changeTemplate($iSurveyID, $sTemplate, $aResults, true);
        }

        $this->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array('aResults' => $aResults, 'successLabel' => $sTemplate)
        );
    }

    /**
     * Change survey group for multiple survey at once.
     * Called from survey list massive actions
     *
     * @return void
     * @throws CException
     */
    public function actionChangeMultipleSurveyGroup()
    {
        $sSurveys = $_POST['sItems'];
        $aSIDs = json_decode($sSurveys);
        $aResults = array();

        $iSurveyGroupId = sanitize_int(App()->request->getPost('surveygroupid'));

        foreach ($aSIDs as $iSurveyID) {
            $oSurvey = Survey::model()->findByPk((int)$iSurveyID);
            $oSurvey->gsid = $iSurveyGroupId;
            $aResults[$iSurveyID]['title'] = $oSurvey->correct_relation_defaultlanguage->surveyls_title;
            if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
                $aResults[$iSurveyID]['result'] = false;
                $aResults[$iSurveyID]['error'] = gT("User does not have valid permissions");
            } else {
                if ($oSurvey->save()) {
                    $aResults[$iSurveyID]['result'] = true;
                } else {
                    $aResults[$iSurveyID]['result'] = false;
                    $aResults[$iSurveyID]['error'] = gT("Survey update failed");
                }
            }
        }

        Yii::app()->getController()->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array('aResults' => $aResults, 'successLabel' => gT("Success"))
        );
    }

    /**
     * Toggles Quick action
     *
     * @return string | null
     * @throws CException
     */
    public function actionToggleQuickAction()
    {
        $quickactionstate = (int)SettingsUser::getUserSettingValue('quickaction_state');

        switch ($quickactionstate) {
            case null:
            case 0:
                $save = SettingsUser::setUserSetting('quickaction_state', 1);
                break;
            case 1:
                $save = SettingsUser::setUserSetting('quickaction_state', 0);
                break;
            default:
                $save = null;
        }
        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success' => $save,
                    'newState' => SettingsUser::getUserSettingValue('quickaction_state')
                ],
            ),
            false,
            false
        );
    }

    /**
     * AjaxRequest get questionGroup with containing questions
     *
     * @todo this could go to the questiongroupAdministrationController ?
     *
     * @param int $surveyid Given Survey ID
     *
     * @return string|string[]
     * @throws CException
     */
    public function actionGetAjaxQuestionGroupArray($surveyid)
    {
        $iSurveyID = sanitize_int($surveyid);

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'read')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->createUrl('/admin'));
        }

        $survey = Survey::model()->findByPk($iSurveyID);
        $baselang = $survey->language;
        $setting_entry = 'last_question_' . Yii::app()->user->getId() . '_' . $iSurveyID;
        $lastquestion = getGlobalSetting($setting_entry);
        $setting_entry = 'last_question_' . Yii::app()->user->getId() . '_' . $iSurveyID . '_gid';
        $lastquestiongroup = getGlobalSetting($setting_entry);

        $aGroups = QuestionGroup::model()->findAllByAttributes(
            array('sid' => $iSurveyID),
            array('order' => 'group_order ASC')
        );
        $aGroupViewable = array();
        if (count($aGroups)) {
            foreach ($aGroups as $group) {
                $curGroup = $group->attributes;
                $curGroup['group_name'] = viewHelper::flatEllipsizeText($group->questiongroupl10ns[$baselang]->group_name, true, 150);
                $curGroup['link'] = $this->createUrl(
                    "questionGroupsAdministration/view",
                    ['surveyid' => $surveyid, 'gid' => $group->gid]
                );
                $group->aQuestions = Question::model()->findAllByAttributes(
                    array(
                        "sid" => $iSurveyID,
                        "gid" => $group['gid'],
                        'parent_qid' => 0
                    ),
                    array('order' => 'question_order ASC')
                );
                $curGroup['questions'] = array();
                foreach ($group->aQuestions as $question) {
                    if (is_object($question)) {
                        $curQuestion = $question->attributes;
                        $curQuestion['link'] = $this->createUrl(
                            "questionAdministration/view",
                            ['surveyid' => $surveyid, 'gid' => $group->gid, 'qid' => $question->qid]
                        );
                        $curQuestion['hidden'] = isset($question->questionattributes['hidden']) &&
                            !empty($question->questionattributes['hidden']->value);
                        $questionText = isset($question->questionl10ns[$baselang])
                            ? $question->questionl10ns[$baselang]->question
                            : '';
                        // We have to limit the question text length here, otherwise the whole question is loaded into the navigation tree
                        $curQuestion['question_flat'] = viewHelper::flatEllipsizeText($questionText, true, 150);
                        $curGroup['questions'][] = $curQuestion;
                    }
                }
                $aGroupViewable[] = $curGroup;
            }
        }

        return $this->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => array(
                    'groups' => $aGroupViewable,
                    'settings' => array(
                        'lastquestion' => $lastquestion,
                        'lastquestiongroup' => $lastquestiongroup,
                    ),
                )
            ),
            false,
            false
        );
    }

    /**
     * Ajaxified get MenuItems with containing questions
     *
     * @todo this could go into surveymenucontroller
     *
     * @param int $surveyid Given Survey ID
     * @param string $position Given Position
     *
     * @return string|string[]
     * @throws CException
     */
    public function actionGetAjaxMenuArray($surveyid, $position = '')
    {
        $iSurveyID = sanitize_int($surveyid);

        //todo permission check ?!?

        $survey = Survey::model()->findByPk($iSurveyID);
        $menus = $survey->getSurveyMenus($position);
        return $this->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'menues' => $menus,
                    'settings' => array(
                        'extrasettings' => false,
                        'parseHTML' => false,
                    )
                ]
            ),
            false,
            false
        );
    }

    /**
     * Method to call current date information by ajax
     *
     * @return JSON | string
     * @throws CException
     */
    public function actionGetDateFormatOptions()
    {
        $aRawDateFormats = getDateFormatData();

        //todo: does this action needs a permission check? what permission should be checked?

        return $this->renderPartial(
            '/admin/super/_renderJson',
            [
                'data' => array_map(
                    function ($aDateFormats) {
                        return $aDateFormats['dateformat'];
                    },
                    $aRawDateFormats
                )
            ],
            false,
            false
        );
    }

    /**
     * Method to store data edited in the text editor component
     *
     * integer $sid Survey ID
     *
     * @return JSON | string
     * @throws CException
     */
    public function actionSaveTextData($sid)
    {
        $iSurveyId = (int)$sid;
        $changes = Yii::app()->request->getPost('changes');
        $aSuccess = [];

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'update')) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'message' => gT("No permission"),
                        'debug' => null
                    ]
                ),
                false,
                false
            );
        }

        foreach ($changes as $sLanguage => $contentChange) {
            //todo: better not to use sql-statement like this in a foreach (performance!!!)
            $oSurveyLanguageSetting = SurveyLanguageSetting::model()->findByPk(
                ["surveyls_survey_id" => $iSurveyId, "surveyls_language" => $sLanguage]
            );
            if ($oSurveyLanguageSetting == null) {
                $oSurveyLanguageSetting = new SurveyLanguageSetting();
                $oSurveyLanguageSetting->surveyls_survey_id = $iSurveyId;
                $oSurveyLanguageSetting->surveyls_language = $sLanguage;
            }
            $oSurveyLanguageSetting->surveyls_title = $contentChange['surveyTitle'];
            $oSurveyLanguageSetting->surveyls_welcometext = $contentChange['welcome'];
            $oSurveyLanguageSetting->surveyls_description = $contentChange['description'];
            $oSurveyLanguageSetting->surveyls_endtext = $contentChange['endText'];
            $oSurveyLanguageSetting->surveyls_url = $contentChange['endUrl'];
            $oSurveyLanguageSetting->surveyls_urldescription = $contentChange['endUrlDescription'];
            $oSurveyLanguageSetting->surveyls_dateformat = $contentChange['dateFormat'];
            $oSurveyLanguageSetting->surveyls_numberformat = $contentChange['decimalDivider'];
            $aSuccess[$sLanguage] = $oSurveyLanguageSetting->save();
            unset($oSurveyLanguageSetting);
        }

        $success = array_reduce(
            $aSuccess,
            function ($carry, $subsuccess) {
                return $carry = $carry && $subsuccess;
            },
            true
        );

        return $this->renderPartial(
            '/admin/super/_renderJson',
            [
                'data' => [
                    "success" => $success,
                    "message" => ($success ? gT("Survey texts were saved successfully.") : gT("Error saving survey texts"))
                ]
            ],
            false,
            false
        );
    }

    /**
     * Collect the data necessary for the data security settings and return a JSON document
     *
     * @param integer|null $sid Survey ID
     *
     * @return JSON | string
     *
     * @throws CException
     */
    public function actionGetDataSecTextSettings($sid = null)
    {
        $iSurveyId = (int)$sid;

        //permission check ...
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'read')) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'message' => gT("No permission"),
                        'debug' => null
                    ]
                ),
                false,
                false
            );
        }


        $oSurvey = Survey::model()->findByPk($iSurveyId);

        $aLanguages = [];
        $aReturner = [
            "dataseclabel" => [],
            "datasecmessage" => [],
            "datasecerror" => [],
        ];

        if ($oSurvey == null) {
            $defaultLanguage = App()->getConfig('defaultlang');
            $aLanguages = [$defaultLanguage => getLanguageCodefromLanguage($defaultLanguage)];
            $aReturner["datasecmessage"][$defaultLanguage] = "";
            $aReturner["datasecerror"][$defaultLanguage] = "";
            $aReturner["dataseclabel"][$defaultLanguage] = "";

            return $this->renderPartial(
                '/admin/super/_renderJson',
                [
                    'data' => [
                        "showsurveypolicynotice" => 0,
                        "textdata" => $aReturner,
                        "languages" => $aLanguages,
                        "permissions" => [
                            "update" => Permission::model()->hasGlobalPermission('surveys', 'create'),
                            "editorpreset" => Yii::app()->session['htmleditormode'],
                        ]
                    ]
                ],
                false,
                false
            );
        }

        foreach ($oSurvey->allLanguages as $sLanguage) {
            $aLanguages[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
            $aReturner["datasecmessage"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_policy_notice;
            $aReturner["datasecerror"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_policy_error;
            $aReturner["dataseclabel"][$sLanguage] = $oSurvey->languagesettings[$sLanguage]->surveyls_policy_notice_label;
        }

        return $this->renderPartial(
            '/admin/super/_renderJson',
            [
                'data' => [
                    "showsurveypolicynotice" => $oSurvey->showsurveypolicynotice,
                    "textdata" => $aReturner,
                    "languages" => $aLanguages,
                    "permissions" => [
                        "update" => Permission::model()->hasSurveyPermission($iSurveyId, 'surveysecurity', 'update'),
                        "editorpreset" => Yii::app()->session['htmleditormode'],
                    ]
                ]
            ],
            false,
            false
        );
    }

    /**
     * Method to store data edited in the data security text editor component
     *
     * @param integer $sid Survey ID
     *
     * @return JSON | string
     *
     * @throws CException
     */
    public function actionSaveDataSecTextData($sid)
    {
        $iSurveyId = (int)$sid;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'update')) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => false,
                        'message' => gT("No permission"),
                        'debug' => null
                    ]
                ),
                false,
                false
            );
        }

        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $changes = Yii::app()->request->getPost('changes', []);
        $aSuccess = [];

        $oSurvey->showsurveypolicynotice = isset($changes['showsurveypolicynotice']) ? $changes['showsurveypolicynotice'] : 0;
        $aSuccess[] = $oSurvey->save();
        foreach ($oSurvey->allLanguages as $sLanguage) {
            $oSurveyLanguageSetting = SurveyLanguageSetting::model()->findByPk([
                "surveyls_survey_id" => $iSurveyId,
                "surveyls_language" => $sLanguage
            ]);
            if ($oSurveyLanguageSetting == null) {
                $oSurveyLanguageSetting = new SurveyLanguageSetting();
                $oSurveyLanguageSetting->surveyls_title = "";
                $oSurveyLanguageSetting->surveyls_survey_id = $iSurveyId;
                $oSurveyLanguageSetting->surveyls_language = $sLanguage;
            }

            $oSurveyLanguageSetting->surveyls_policy_notice = isset($changes['datasecmessage'][$sLanguage]) ? $changes['datasecmessage'][$sLanguage] : '';
            $oSurveyLanguageSetting->surveyls_policy_error = isset($changes['datasecerror'][$sLanguage]) ? $changes['datasecerror'][$sLanguage] : '';
            $oSurveyLanguageSetting->surveyls_policy_notice_label = isset($changes['dataseclabel'][$sLanguage]) ? $changes['dataseclabel'][$sLanguage] : '';
            $aSuccess[$sLanguage] = $oSurveyLanguageSetting->save();
            unset($oSurveyLanguageSetting);
        }

        $success = array_reduce(
            $aSuccess,
            function ($carry, $subsuccess) {
                return $carry = $carry && $subsuccess;
            },
            true
        );

        return $this->renderPartial(
            '/admin/super/_renderJson',
            [
                'data' => [
                    "success" => $success,
                    "message" => ($success ? gT("Successfully saved privacy policy text") : gT("Error saving privacy policy text"))
                ]
            ],
            false,
            false
        );
    }

    /**
     * Apply current theme options for imported survey theme
     *
     * @param integer $iSurveyID The survey ID of imported survey
     *
     * @return void
     */
    public function actionApplythemeoptions(int $surveyid = 0)
    {
        $iSurveyID = $surveyid;
        if ((int)$iSurveyID > 0 && Yii::app()->request->isPostRequest) {
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            $sTemplateName = $oSurvey->template;
            $aThemeOptions = json_decode(App()->request->getPost('themeoptions', ''));

            if (!empty($aThemeOptions)) {
                $oSurveyConfig = TemplateConfiguration::getInstance($sTemplateName, null, $iSurveyID);
                if ($oSurveyConfig->options === 'inherit') {
                    $oSurveyConfig->setOptionKeysToInherit();
                }

                foreach ($aThemeOptions as $key => $value) {
                    $oSurveyConfig->setOption($key, $value);
                }
                $oSurveyConfig->save();
            }
        }
        $this->redirect(array('surveyAdministration/view/surveyid/' . $iSurveyID));
    }

    /**
     * Upload an image in directory
     *
     * @return json |string
     *
     * @throws CException
     */
    public function actionUploadimagefile()
    {
        $debug = [$_FILES];
        // Check file size and render JSON on error.
        // This is done before checking the survey permissions because, if the max POST size was exceeded,
        // there is no Survey ID to check for permissions, so the error could be misleading.
        $uploadValidator = new LimeSurvey\Models\Services\UploadValidator();
        $uploadValidator->renderJsonOnError('file', $debug);

        $iSurveyID = Yii::app()->request->getPost('surveyid');
        $success = false;
        $debug = [];
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update')) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => $success,
                        'message' => gT("You don't have sufficient permissions to upload images in this survey"),
                        'debug' => $debug
                    ]
                ),
                false,
                false
            );
        }
        $checkImage = LSYii_ImageValidator::validateImage($_FILES["file"]);
        if ($checkImage['check'] === false) {
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => $success,
                        'message' => $checkImage['uploadresult'],
                        'debug' => $checkImage['debug']
                    ]
                ),
                false,
                false
            );
        }
        $surveyDir = Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID;
        if (!is_dir($surveyDir)) {
            @mkdir($surveyDir);
        }
        if (!is_dir($surveyDir . "/images")) {
            @mkdir($surveyDir . "/images");
        }
        $destdir = $surveyDir . "/images/";
        if (!is_writeable($destdir)) {
            $uploadresult = sprintf(gT("Incorrect permissions in your %s folder."), $destdir);
            return $this->renderPartial(
                '/admin/super/_renderJson',
                array('data' => ['success' => $success, 'message' => $uploadresult, 'debug' => $debug]),
                false,
                false
            );
        }

        $filename = sanitize_filename(
            $_FILES['file']['name'],
            false,
            false,
            false
        ); // Don't force lowercase or alphanumeric
        $fullfilepath = $destdir . $filename;
        $debug[] = $destdir;
        $debug[] = $filename;
        $debug[] = $fullfilepath;
        if (!@move_uploaded_file($_FILES['file']['tmp_name'], $fullfilepath)) {
            $uploadresult = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
        } else {
            $uploadresult = sprintf(gT("File %s uploaded"), $filename);
            $success = true;
        };
        return $this->renderPartial(
            '/admin/super/_renderJson',
            array('data' => ['success' => $success, 'message' => $uploadresult, 'debug' => $debug]),
            false,
            false
        );
    }

    /**
     * Returns JSON Data for Token Top Bar.
     *
     * @param int $sid Given Survey ID
     * @param bool $onlyclose Close
     *
     * @return string
     * @todo: this should go into tokens controller ...
     *
     */
    public function actionGetTokenTopBar(int $sid)
    {
        $oSurvey = Survey::model()->findByPk($sid);

        return $this->renderPartial(
            'token_bar',
            array(
                'oSurvey' => $oSurvey,
                'sid' => $sid,
                'onlyclose' => !$oSurvey->hasTokensTable
            ),
            false,
            false
        );
    }

    /**
     * This Method is returning the Data for Survey Top Bar Component
     * for Vue JS as JSON.
     *
     * @param int $sid Given Survey ID
     * @param bool $saveButton Renders Save Button
     *
     * @return string|string[]|null
     * @throws CException
     *
     */
    public function actionGetSurveyTopbar(int $sid, $saveButton = false)
    {
        $oSurvey = Survey::model()->findByPk($sid);
        $hasSurveyContentPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update');
        $hasSurveyActivationPermission = Permission::model()->hasSurveyPermission($sid, 'surveyactivation', 'update');
        $hasDeletePermission = Permission::model()->hasSurveyPermission($sid, 'survey', 'delete');
        $hasSurveyTranslatePermission = Permission::model()->hasSurveyPermission($sid, 'translations', 'read');
        $hasSurveyReadPermission = Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read');
        $hasSurveyTokensPermission = Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update')
            || Permission::model()->hasSurveyPermission($sid, 'tokens', 'create');
        $hasResponsesCreatePermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'create');
        $hasResponsesReadPermission = Permission::model()->hasSurveyPermission($sid, 'responses', 'read');
        $hasResponsesStatisticsReadPermission = Permission::model()->hasSurveyPermission($sid, 'statistics', 'read');

        $isActive = $oSurvey->active == 'Y';
        $condition = array('sid' => $sid, 'parent_qid' => 0);
        $sumcount = Question::model()->countByAttributes($condition);
        $countLanguage = count($oSurvey->allLanguages);
        $hasAdditionalLanguages = (count($oSurvey->additionalLanguages) > 0);
        $canactivate = $sumcount > 0 && $hasSurveyActivationPermission;
        $expired = $oSurvey->expires != '' && ($oSurvey->expires < dateShift(
            date("Y-m-d H:i:s"),
            "Y-m-d H:i",
            Yii::app()->getConfig('timeadjust')
        ));
        $notstarted = ($oSurvey->startdate != '') && ($oSurvey->startdate > dateShift(
            date("Y-m-d H:i:s"),
            "Y-m-d H:i",
            Yii::app()->getConfig('timeadjust')
        ));

        if (!$isActive) {
            $context = gT("Preview survey");
            $contextbutton = 'preview_survey';
        } else {
            $context = gT("Run survey");
            $contextbutton = 'execute_survey';
        }

        $language = $oSurvey->language;
        $conditionsCount = Condition::model()->with(array('questions' => array('condition' => 'sid =' . $sid)))->count();
        $oneLanguage = (count($oSurvey->allLanguages) == 1);

        // Put menu items in tools menu
        $event = new PluginEvent('beforeToolsMenuRender', $this);
        $event->set('surveyId', $oSurvey->sid);
        App()->getPluginManager()->dispatchEvent($event);
        $extraToolsMenuItems = $event->get('menuItems');

        // Add new menus in survey bar
        $event = new PluginEvent('beforeSurveyBarRender', $this);
        $event->set('surveyId', $oSurvey->sid);
        App()->getPluginManager()->dispatchEvent($event);
        $beforeSurveyBarRender = $event->get('menus');

        return $this->renderPartial(
            'survey_topbar',
            array(
                'sid' => $sid,
                'canactivate' => $canactivate,
                'expired' => $expired,
                'notstarted' => $notstarted,
                'context' => $context,
                'contextbutton' => $contextbutton,
                'language' => $language,
                'sumcount' => $sumcount,
                'hasSurveyContentPermission' => $hasSurveyContentPermission,
                'countLanguage' => $countLanguage,
                'hasDeletePermission' => $hasDeletePermission,
                'hasSurveyTranslatePermission' => $hasSurveyTranslatePermission,
                'hasAdditionalLanguages' => $hasAdditionalLanguages,
                'conditionsCount' => $conditionsCount,
                'hasSurveyReadPermission' => $hasSurveyReadPermission,
                'oneLanguage' => $oneLanguage,
                'hasSurveyTokensPermission' => $hasSurveyTokensPermission,
                'hasResponsesCreatePermission' => $hasResponsesCreatePermission,
                'hasResponsesReadPermission' => $hasResponsesReadPermission,
                'hasSurveyActivationPermission' => $hasSurveyActivationPermission,
                'hasResponsesStatisticsReadPermission' => $hasResponsesStatisticsReadPermission,
                'addSaveButton' => $saveButton,
                'extraToolsMenuItems' => $extraToolsMenuItems ?? [],
                'beforeSurveyBarRender' => $beforeSurveyBarRender ?? []
            ),
            false,
            false
        );
    }

    /**
     * Function responsible to deactivate a survey.
     *
     * @return void
     * @access public
     * @throws CException
     */
    public function actionDeactivate()
    {
        $iSurveyID = $this->getSurveyIdFromGetRequest();
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveyactivation', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $survey = Survey::model()->findByPk($iSurveyID);
        $datestamp = time();
        $date = date('YmdHis', $datestamp); //'His' adds 24hours+minutes to name to allow multiple deactiviations in a day
        $DBDate = date('Y-m-d H:i:s', $datestamp);
        $userID = Yii::app()->user->getId();
        $aData = array();

        $aData['aSurveysettings'] = getSurveyInfo($iSurveyID);
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $iSurveyID;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['topBar']['hide'] = true;

        // Fire event beforeSurveyDeactivate
        $beforeSurveyDeactivate = new PluginEvent('beforeSurveyDeactivate');
        $beforeSurveyDeactivate->set('surveyId', $iSurveyID);
        App()->getPluginManager()->dispatchEvent($beforeSurveyDeactivate);
        $success = $beforeSurveyDeactivate->get('success');
        $message = $beforeSurveyDeactivate->get('message');
        if (!empty($message)) {
            Yii::app()->user->setFlash('error', $message);
        }
        if ($success === false) {
            // @todo: What if two plugins change this?
            $aData['nostep'] = true;
            $this->aData = $aData;
        } else {
            if (!tableExists('survey_' . $iSurveyID)) {
                $_SESSION['flashmessage'] = gT("Error: Response table does not exist. Survey cannot be deactivated.");
                $this->redirect($this->createUrl("surveyAdministration/view/surveyid/{$iSurveyID}"));
            }
            if (Yii::app()->request->getPost('ok') == '') {
                $aData['surveyid'] = $iSurveyID;
                $aData['date'] = $date;
                $aData['dbprefix'] = Yii::app()->db->tablePrefix;
                $aData['step1'] = true;
            } else {
                //See if there is a tokens table for this survey
                if (tableExists("{{tokens_{$iSurveyID}}}")) {
                    $toldtable = Yii::app()->db->tablePrefix . "tokens_{$iSurveyID}";
                    $tnewtable = Yii::app()->db->tablePrefix . "old_tokens_{$iSurveyID}_{$date}";
                    if (Yii::app()->db->getDriverName() == 'pgsql') {
                        // Find out the trigger name for tid column
                        $tidDefault = Yii::app()->db->createCommand("SELECT pg_get_expr(adbin, adrelid) as adsrc FROM pg_attribute JOIN pg_class ON (pg_attribute.attrelid=pg_class.oid) JOIN pg_attrdef ON(pg_attribute.attrelid=pg_attrdef.adrelid AND pg_attribute.attnum=pg_attrdef.adnum) WHERE pg_class.relname='$toldtable' and pg_attribute.attname='tid'")->queryScalar();
                        if (preg_match("/nextval\('(tokens_\d+_tid_seq\d*)'::regclass\)/", $tidDefault, $matches)) {
                            $oldSeq = $matches[1];
                            Yii::app()->db->createCommand()->renameTable($oldSeq, $tnewtable . '_tid_seq');
                            $setsequence = "ALTER TABLE " . Yii::app()->db->quoteTableName($toldtable) . " ALTER COLUMN tid SET DEFAULT nextval('{$tnewtable}_tid_seq'::regclass);";
                            Yii::app()->db->createCommand($setsequence)->query();
                        }
                    }

                    Yii::app()->db->createCommand()->renameTable($toldtable, $tnewtable);

                    $archivedTokenSettings = new ArchivedTableSettings();
                    $archivedTokenSettings->survey_id = $iSurveyID;
                    $archivedTokenSettings->user_id = $userID;
                    $archivedTokenSettings->tbl_name = "old_tokens_{$iSurveyID}_{$date}";
                    $archivedTokenSettings->tbl_type = 'token';
                    $archivedTokenSettings->created = $DBDate;
                    $archivedTokenSettings->properties = $aData['aSurveysettings']['tokenencryptionoptions'];
                    $archivedTokenSettings->attributes = json_encode($aData['aSurveysettings']['attributedescriptions']);
                    $archivedTokenSettings->save();

                    $aData['tnewtable'] = $tnewtable;
                    $aData['toldtable'] = $toldtable;
                }
                // Reset the session of the survey when deactivating it
                killSurveySession($iSurveyID);
                //Remove any survey_links to the CPDB
                SurveyLink::model()->deleteLinksBySurvey($iSurveyID);


                // IF there are any records in the saved_control table related to this survey, they have to be deleted
                SavedControl::model()->deleteSomeRecords(array('sid' => $iSurveyID)); //Yii::app()->db->createCommand($query)->query();
                $sOldSurveyTableName = Yii::app()->db->tablePrefix . "survey_{$iSurveyID}";
                $sNewSurveyTableName = Yii::app()->db->tablePrefix . "old_survey_{$iSurveyID}_{$date}";
                $aData['sNewSurveyTableName'] = $sNewSurveyTableName;

                $query = "SELECT id FROM " . Yii::app()->db->quoteTableName($sOldSurveyTableName) . " ORDER BY id desc";
                $sLastID = Yii::app()->db->createCommand($query)->limit(1)->queryScalar();
                //Update the autonumber_start in the survey properties
                $new_autonumber_start = $sLastID + 1;
                $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyID));
                $survey->autonumber_start = $new_autonumber_start;
                $survey->save();
                if (Yii::app()->db->getDriverName() == 'pgsql') {
                    $idDefault = Yii::app()->db->createCommand("SELECT pg_get_expr(pg_attrdef.adbin, pg_attrdef.adrelid) FROM pg_attribute JOIN pg_class ON (pg_attribute.attrelid=pg_class.oid) JOIN pg_attrdef ON(pg_attribute.attrelid=pg_attrdef.adrelid AND pg_attribute.attnum=pg_attrdef.adnum) WHERE pg_class.relname='$sOldSurveyTableName' and pg_attribute.attname='id'")->queryScalar();
                    if (preg_match("/nextval\('(survey_\d+_id_seq\d*)'::regclass\)/", $idDefault, $matches)) {
                        $oldSeq = $matches[1];
                        Yii::app()->db->createCommand()->renameTable($oldSeq, $sNewSurveyTableName . '_id_seq');
                        $setsequence = "ALTER TABLE " . Yii::app()->db->quoteTableName($sOldSurveyTableName) . " ALTER COLUMN id SET DEFAULT nextval('{{{$sNewSurveyTableName}}}_id_seq'::regclass);";
                        Yii::app()->db->createCommand($setsequence)->query();
                    }
                }

                Yii::app()->db->createCommand()->renameTable($sOldSurveyTableName, $sNewSurveyTableName);

                $archivedTokenSettings = new ArchivedTableSettings();
                $archivedTokenSettings->survey_id = $iSurveyID;
                $archivedTokenSettings->user_id = $userID;
                $archivedTokenSettings->tbl_name = "old_survey_{$iSurveyID}_{$date}";
                $archivedTokenSettings->tbl_type = 'response';
                $archivedTokenSettings->created = $DBDate;
                $archivedTokenSettings->properties = json_encode(Response::getEncryptedAttributes($iSurveyID));
                $archivedTokenSettings->save();

                $survey->active = 'N';
                $survey->save();

                $prow = Survey::model()->find('sid = :sid', array(':sid' => $iSurveyID));
                if ($prow->savetimings == "Y") {
                    $sOldTimingsTableName = Yii::app()->db->tablePrefix . "survey_{$iSurveyID}_timings";
                    $sNewTimingsTableName = Yii::app()->db->tablePrefix . "old_survey_{$iSurveyID}_timings_{$date}";
                    Yii::app()->db->createCommand()->renameTable($sOldTimingsTableName, $sNewTimingsTableName);
                    $aData['sNewTimingsTableName'] = $sNewTimingsTableName;
                }

                $archivedTokenSettings = new ArchivedTableSettings();
                $archivedTokenSettings->survey_id = $iSurveyID;
                $archivedTokenSettings->user_id = $userID;
                $archivedTokenSettings->tbl_name = "old_survey_{$iSurveyID}_timings_{$date}";
                $archivedTokenSettings->tbl_type = 'timings';
                $archivedTokenSettings->created = $DBDate;
                $archivedTokenSettings->properties = '';
                $archivedTokenSettings->save();

                $event = new PluginEvent('afterSurveyDeactivate');
                $event->set('surveyId', $iSurveyID);
                App()->getPluginManager()->dispatchEvent($event);

                $aData['surveyid'] = $iSurveyID;
                Yii::app()->db->schema->refresh();
            }

            $aData['sidemenu']['state'] = false;
        }

        $this->aData = $aData;
        $this->render('deactivateSurvey_view', $aData);
    }


    /**
     * Function responsible to activate survey.
     *
     * @param int $iSurveyID Given Survey ID
     *
     * @return void
     * @access public
     * @throws CException
     */
    public function actionActivate($iSurveyID)
    {
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveyactivation', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        $iSurveyID = (int) $iSurveyID;
        $survey = Survey::model()->findByPk($iSurveyID);
        $surveyActivator = new SurveyActivator($survey);

        Yii::app()->user->setState('sql_' . $iSurveyID, ''); //If user has set some filters for responses from statistics on a previous activation, it must be wiped out
        $aData = array();
        $aData['oSurvey'] = $survey;
        $aData['sidemenu']['state'] = false;
        $aData['aSurveysettings'] = getSurveyInfo($iSurveyID);
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $iSurveyID;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['topBar']['hide'] = true;
        // Redirect if this is not possible
        if (!isset($aData['aSurveysettings']['active']) || $aData['aSurveysettings']['active'] == 'Y') {
            Yii::app()->setFlashMessage(gT("This survey is already active."), 'error');
            $this->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyID));
        }
        Yii::app()->loadHelper("admin/activate");

        if (Yii::app()->request->getPost('ok') == '') {
            if (isset($_GET['fixnumbering']) && $_GET['fixnumbering']) {
                fixNumbering($_GET['fixnumbering'], $iSurveyID);
            }

            // Check consistency for groups and questions
            $failedgroupcheck = checkGroup($iSurveyID);
            $failedcheck = checkQuestions($iSurveyID, $iSurveyID);

            $aData['failedcheck'] = $failedcheck;
            $aData['failedgroupcheck'] = $failedgroupcheck;
            $aData['aSurveysettings'] = getSurveyInfo($iSurveyID);

            $this->aData = $aData;
            $this->render('activateSurvey_view', $aData);
        } else {
            if (!is_null($survey)) {
                $survey->anonymized = Yii::app()->request->getPost('anonymized');
                $survey->datestamp = Yii::app()->request->getPost('datestamp');
                $survey->ipaddr = Yii::app()->request->getPost('ipaddr');
                $survey->ipanonymize = Yii::app()->request->getPost('ipanonymize');
                $survey->refurl = Yii::app()->request->getPost('refurl');
                $survey->savetimings = Yii::app()->request->getPost('savetimings');
                $survey->save();

                // Make sure the saved values will be picked up
                Survey::model()->resetCache();
                $survey->setOptions();
            }

            $aResult = $surveyActivator->activate();

            $aViewUrls = array();
            if (
                (isset($aResult['error']) && $aResult['error'] == 'plugin')
                || (isset($aResult['blockFeedback']) && $aResult['blockFeedback'])
            ) {
                // Got false from plugin, redirect to survey front-page
                $this->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyID));
            } elseif (isset($aResult['pluginFeedback'])) {
                // Special feedback from plugin
                $aViewUrls['output'] = $aResult['pluginFeedback'];
                //check if feedback is given to user ...
                $this->aData = $aData;
                $this->render('_activation_feedback', $aData);
            } elseif (isset($aResult['error'])) {
                $data['result'] = $aResult;
                $this->aData = $aData;
                $this->render('_activation_error', $data);
            } else {
                $warning = (isset($aResult['warning'])) ? true : false;
                $allowregister = $survey->isAllowRegister;
                $onclickAction = convertGETtoPOST(Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/" . $iSurveyID));
                $closedOnclickAction = convertGETtoPOST(Yii::app()->getController()->createUrl("admin/tokens/sa/index/surveyid/" . $iSurveyID));
                $noOnclickAction = "window.location.href='" . (Yii::app()->getController()->createUrl("surveyAdministration/view/surveyid/" . $iSurveyID)) . "'";

                $activationData = array(
                    'iSurveyID'           => $iSurveyID,
                    'survey'              => $survey,
                    'warning'             => $warning,
                    'allowregister'       => $allowregister,
                    'onclickAction'       => $onclickAction,
                    'closedOnclickAction' => $closedOnclickAction,
                    'noOnclickAction'     => $noOnclickAction,
                );
                $this->aData = $aData;
                $this->render('_activation_feedback', $activationData);
            }
        }
    }

    /**
     * Function responsible to delete a survey.
     *
     * @return string
     * @access public
     */
    public function actionDelete()
    {
        //todo: delete should always be a post-request
        $iSurveyID = $this->getSurveyIdFromGetRequest();
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'survey', 'delete')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }
        $aData = [];
        $iSurveyID = sanitize_int($iSurveyID);
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $aData['surveyid'];
        $survey = Survey::model()->findByPk($iSurveyID);

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['sidemenu']['state'] = false;
        $aData['survey'] = $survey;

        if (Yii::app()->request->getPost("delete") == 'yes') {
            $aData['issuperadmin'] = Permission::model()->hasGlobalPermission('superadmin', 'read');
            Survey::model()->deleteSurvey($iSurveyID);
            Yii::app()->session['flashmessage'] = gT("Survey deleted.");
            $this->redirect(array("admin/index"));
        }

        $this->aData = $aData;
        $this->render('deleteSurvey_view', $aData);
    }

    /**
     * Remove files not deleted properly.
     * Purge is only available for surveys that were already deleted but for some reason
     * left files behind.
     *
     * @param int $purge_sid Given ID
     *
     * @return void
     */
    public function actionPurge($purge_sid)
    {
        $purge_sid = (int) $purge_sid;
        if (Permission::model()->hasGlobalPermission('superadmin', 'delete')) {
            $survey = Survey::model()->findByPk($purge_sid);
            if (empty($survey)) {
                $result = rmdirr(Yii::app()->getConfig('uploaddir') . '/surveys/' . $purge_sid);
                if ($result) {
                    Yii::app()->user->setFlash('success', gT('Survey files deleted.'));
                } else {
                    Yii::app()->user->setFlash('error', gT('Error: Could not delete survey files.'));
                }
            } else {
                // Should not be possible.
                Yii::app()->user->setFlash(
                    'error',
                    gT('Error: Cannot purge files for a survey that is not deleted. Please delete the survey normally in the survey view.')
                );
            }
        } else {
            Yii::app()->user->setFlash('error', gT('Access denied'));
        }

        $this->redirect($this->createUrl('admin/globalsettings'));
    }


    /**
     * New system of rendering content
     * Based on yii submenu rendering
     *
     * @param int $surveyid Given Survey ID
     * @param string $subaction Given subaction (subaction decides which view to render)
     *
     * @return void
     *
     * @throws Exception
     *
     * The below 'uses' are mentioned
     * @uses self::generalTabEditSurvey()
     * @uses self::pluginTabSurvey()
     * @uses self::tabPresentationNavigation()
     * @uses self::tabPublicationAccess()
     * @uses self::tabNotificationDataManagement()
     * @uses self::tabTokens()
     * @uses self::tabPanelIntegration()
     * @uses self::tabResourceManagement()
     *
     */
    public function actionRendersidemenulink($surveyid, $subaction)
    {
        $iSurveyID = (int) $surveyid;
        $menuaction = (string) $subaction;
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData['oSurvey'] = $survey;
        // set values from database to survey attributes

        if (empty($survey)) {
            throw new Exception('Found no survey with id ' . $iSurveyID);
        }

        $survey->setOptionsFromDatabase();

        //Get all languages
        $grplangs = $survey->additionalLanguages;
        $baselang = $survey->language;
        array_unshift($grplangs, $baselang);

        //@TODO add language checks here
        $menuEntry = SurveymenuEntries::model()->find('name=:name', array(':name' => $menuaction));

        if (!(Permission::model()->hasSurveyPermission($iSurveyID, $menuEntry->permission, $menuEntry->permission_grade))) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyID));
        }

        $templateData = is_array($menuEntry->data) ? $menuEntry->data : [];

        if (!empty($menuEntry->getdatamethod)) {
            $templateData = array_merge($templateData, call_user_func_array(
                array($this, $menuEntry->getdatamethod), //info: getdatamethod is the name of a function here in this controller!!!
                array('survey' => $survey)
            ));
        }

        $templateData = array_merge($this->getGeneralTemplateData($iSurveyID), $templateData);

        // For Text Elemnts Tab.
        if ($menuaction === 'surveytexts') {
            $temp = [];
            $languages = $survey->allLanguages;
            foreach ($languages as $i => $language) {
                $temp = $this->getGeneralTemplateData($iSurveyID);

                App()->loadHelper('database');
                App()->loadHelper('surveytranslator');
                App()->loadHelper('admin.htmleditor');

                $surveyLanguageSetting = SurveyLanguageSetting::model()->findByPk(
                    array(
                        'surveyls_survey_id' => $iSurveyID,
                        'surveyls_language' => $language)
                )->getAttributes();
                $aTabTitles[$language] = getLanguageNameFromCode($surveyLanguageSetting['surveyls_language'], false);

                if ($surveyLanguageSetting['surveyls_language'] == $survey->language) {
                    $aTabTitles[$language] .= ' (' . gT("Base language") . ')';
                }

                $temp['aSurveyLanguageSettings'] = $surveyLanguageSetting;
                $temp['action'] = "surveygeneralsettings";
                $temp['i'] = $i;
                $temp['dateformatdetails'] = getDateFormatData(App()->session['dateformat']);
                $temp['oSurvey'] = $survey;
                Yii::app()->session['FileManagerContext'] = "edit:survey:{$iSurveyID}";
                $aTabContents[$language] = $this->renderPartial('/admin/survey/editLocalSettings_view', $temp, true);
            }

            $aData['aTabContents'] = $aTabContents;
            $aData['aTabTitles']   = $aTabTitles;
            $aData['moreInfo'] = $temp;
        }

        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'surveysettings.js');
        App()->getClientScript()->registerPackage('jquery-json');
        App()->getClientScript()->registerPackage('bootstrap-switch');

        // override survey settings if global settings exist
        $templateData['showqnumcode']   = getGlobalSetting('showqnumcode') !== 'choose' ? getGlobalSetting('showqnumcode') : $survey->showqnumcode;
        $templateData['shownoanswer']   = getGlobalSetting('shownoanswer') !== 'choose' ? getGlobalSetting('shownoanswer') : $survey->shownoanswer;
        $templateData['showgroupinfo']  = getGlobalSetting('showgroupinfo') !== '2' ? getGlobalSetting('showgroupinfo') : $survey->showgroupinfo;
        $templateData['showxquestions'] = getGlobalSetting('showxquestions') !== 'choose' ? getGlobalSetting('showxquestions') : $survey->showxquestions;

        //Start collecting aData
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $iSurveyID;
        $aData['menuaction'] = $menuaction;
        $aData['template'] = $menuEntry->template;
        $aData['templateData'] = $templateData;
        $aData['surveyls_language'] = $baselang;
        $aData['action'] = $menuEntry->action;
        $aData['entryData'] = $menuEntry->attributes;
        $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);
        $aData['subaction'] = $menuEntry->title;
        $aData['display']['menu_bars']['surveysummary'] = $menuEntry->title;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['surveybar']['buttons']['view'] = true;
        $aData['surveybar']['savebutton']['form'] = 'globalsetting';
        $aData['surveybar']['savebutton']['useformid'] = 'true';
        $aData['surveybar']['saveandclosebutton']['form'] = true;
        $aData['topBar']['closeUrl'] = $this->createUrl("surveyAdministration/view/", ['surveyid' => $iSurveyID]); // Close button

        if ($subaction === 'resources') {
            $aData['topBar']['showSaveButton'] = false;
        } else {
            $aData['topBar']['showSaveButton'] = true;
        }

        $aData['optionsOnOff'] = array(
            'Y' => gT('On', 'unescaped'),
            'N' => gT('Off', 'unescaped'),
        );

        $this->aData = $aData;
        $this->render($menuEntry->template, $aData);
    }

    /**
     * Load ordering of question group screen.
     * questiongroup::organize()
     *
     * @param int $iSurveyID Given Survey ID
     *
     * @return void
     */
    public function actionOrganize()
    {
        $request = Yii::app()->request;

        $iSurveyID = $this->getSurveyIdFromGetRequest();

        $iSurveyID = sanitize_int($iSurveyID);
        $thereIsPostData = $request->getPost('orgdata') !== null;
        $userHasPermissionToUpdate = Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update');

        if (!$userHasPermissionToUpdate) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        if ($thereIsPostData) {
            // Save the new ordering
            $this->reorderGroup($iSurveyID);

            $closeAfterSave = $request->getPost('close-after-save') === 'true';
            if ($closeAfterSave) {
                $this->redirect(array('surveyAdministration/view/surveyid/' . $iSurveyID));
            }
        }
        $aData = $this->showReorderForm($iSurveyID);

        // Display 'Reorder question/question groups' in Green Bar
        $aData['subaction'] = gT('Reorder questions/question groups');

        $this->aData = $aData;
        $this->render('/admin/survey/organizeGroupsAndQuestions_view', $aData);
    }

    /**
     * @param int $surveyid Given Survey ID.
     *
     * @return void
     * @todo   Add TypeDoc.
     */
    public function actionGetUrlParamsJSON($surveyid)
    {
        $iSurveyID = (int) $surveyid;
        $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        $aSurveyParameters = SurveyURLParameter::model()->findAll('sid=:sid', [':sid' => $iSurveyID]);
        $aData = array(
            'rows' => []
        );
        foreach ($aSurveyParameters as $oSurveyParameter) {
            $row = $oSurveyParameter->attributes;

            if ($oSurveyParameter->targetqid != '') {
                $row['questionTitle'] = $oSurveyParameter->question->title . ": " . ellipsize(flattenText($oSurveyParameter->question->questionl10ns[$sBaseLanguage]->question, false, true), 43, .70);
            }

            if ($oSurveyParameter->targetsqid != '') {
                $row['questionTitle'] .= (' - ' . ellipsize(flattenText($oSurveyParameter->subquestion->questionl10ns[$sBaseLanguage]->question, false, true), 30, .75));
            }

            $row['qid'] = $oSurveyParameter->targetqid;
            $row['sqid'] = $oSurveyParameter->targetsqid;
            $aData['rows'][] = $row;
        }

        $aData['page'] = 1;
        $aData['records'] = count($aSurveyParameters);
        $aData['total'] = 1;

        echo ls_json_encode($aData);
    }

    /**
     * Function responsible to import/copy a survey based on $action.
     *
     * @todo this should be separated in two actions import and copy ...
     *
     * @access public
     * @return void
     */
    public function actionCopy()
    {
        //everybody who has permission to create surveys
        if (!Permission::model()->hasGlobalPermission('surveys', 'create')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(Yii::app()->request->urlReferrer);
        }

        //maybe thing about permission check for copy surveys
        //at the moment dropDown selection shows only surveys for the user he owns himself ...
        $action = Yii::app()->request->getParam('action');
        $iSurveyID = sanitize_int(Yii::app()->request->getParam('sid'));
        $aData = [];

        if ($action == "importsurvey" || $action == "copysurvey") {
            // Start the HTML
            $sExtension = "";

            if ($action == 'importsurvey') {
                $aData['sHeader'] = gT("Import survey data");
                $aData['sSummaryHeader'] = gT("Survey structure import summary");
                $aPathInfo = pathinfo($_FILES['the_file']['name']);

                if (isset($aPathInfo['extension'])) {
                    $sExtension = $aPathInfo['extension'];
                }
            } elseif ($action == 'copysurvey') {
                $aData['sHeader'] = gT("Copy survey");
                $aData['sSummaryHeader'] = gT("Survey copy summary");
            }

            // Start traitment and messagebox
            $aData['bFailed'] = false; // Put a var for continue
            $sFullFilepath = '';
            if ($action == 'importsurvey') {
                $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(30) . '.' . $sExtension;
                if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
                    $aData['sErrorMessage'] = sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024) . '<br>';
                    $aData['bFailed'] = true;
                } elseif (!in_array(strtolower($sExtension), array('lss', 'txt', 'tsv', 'lsa'))) {
                    $aData['sErrorMessage'] = sprintf(gT("Import failed. You specified an invalid file type '%s'."), CHtml::encode($sExtension));
                    $aData['bFailed'] = true;
                } elseif ($aData['bFailed'] || !@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
                    $aData['sErrorMessage'] = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
                    $aData['bFailed'] = true;
                }
            } elseif ($action == 'copysurvey') {
                $iSurveyID = sanitize_int(Yii::app()->request->getParam('copysurveylist'));
                $aExcludes = array();

                $sNewSurveyName = Yii::app()->request->getPost('copysurveyname');

                if (Yii::app()->request->getPost('copysurveyexcludequotas') == "1") {
                    $aExcludes['quotas'] = true;
                }

                if (Yii::app()->request->getPost('copysurveyexcludepermissions') == "1") {
                    $aExcludes['permissions'] = true;
                }

                if (Yii::app()->request->getPost('copysurveyexcludeanswers') == "1") {
                    $aExcludes['answers'] = true;
                }

                if (Yii::app()->request->getPost('copysurveyresetconditions') == "1") {
                    $aExcludes['conditions'] = true;
                }

                if (Yii::app()->request->getPost('copysurveyresetstartenddate') == "1") {
                    $aExcludes['dates'] = true;
                }

                if (Yii::app()->request->getPost('copysurveyresetresponsestartid') == "1") {
                    $aExcludes['reset_response_id'] = true;
                }

                if (!$iSurveyID) {
                    $aData['sErrorMessage'] = gT("No survey ID has been provided. Cannot copy survey");
                    $aData['bFailed'] = true;
                } elseif (!Survey::model()->findByPk($iSurveyID)) {
                    $aData['sErrorMessage'] = gT("Invalid survey ID");
                    $aData['bFailed'] = true;
                } elseif (
                    !Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'export')
                    &&    !Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'export')
                ) {
                    $aData['sErrorMessage'] = gT("We are sorry but you don't have permissions to do this.");
                    $aData['bFailed'] = true;
                } else {
                    Yii::app()->loadHelper('export');
                    $copysurveydata = surveyGetXMLData($iSurveyID, $aExcludes);
                }
            }

            // Now, we have the survey : start importing
            Yii::app()->loadHelper('admin/import');

            if ($action == 'importsurvey' && !$aData['bFailed']) {
                $aImportResults = importSurveyFile($sFullFilepath, (Yii::app()->request->getPost('translinksfields') == '1'));
                if (is_null($aImportResults)) {
                    $aImportResults = array(
                        'error' => gT("Unknown error while reading the file, no survey created.")
                    );
                }
            } elseif ($action == 'copysurvey' && !$aData['bFailed']) {
                $copyResources = Yii::app()->request->getPost('copysurveytranslinksfields') == '1';
                $translateLinks = $copyResources;
                $aImportResults = XMLImportSurvey('', $copysurveydata, $sNewSurveyName, sanitize_int(App()->request->getParam('copysurveyid')), $translateLinks);
                if (isset($aExcludes['conditions'])) {
                    Question::model()->updateAll(array('relevance' => '1'), 'sid=' . $aImportResults['newsid']);
                    QuestionGroup::model()->updateAll(array('grelevance' => '1'), 'sid=' . $aImportResults['newsid']);
                }

                if (isset($aExcludes['reset_response_id'])) {
                    $oSurvey = Survey::model()->findByPk($aImportResults['newsid']);
                    $oSurvey->autonumber_start = 0;
                    $oSurvey->save();
                }

                if (!isset($aExcludes['permissions'])) {
                    Permission::model()->copySurveyPermissions($iSurveyID, $aImportResults['newsid']);
                }

                if (!empty($aImportResults['newsid']) && $copyResources) {
                    $resourceCopier = new CopySurveyResources();
                    [, $errorFilesInfo] = $resourceCopier->copyResources($iSurveyID, $aImportResults['newsid']);
                    if (!empty($errorFilesInfo)) {
                        $aImportResults['importwarnings'][] = gT("Some resources could not be copied from the source survey");
                    }
                }
            } else {
                $aData['bFailed'] = true;
            }

            // If the import failed, set the status and error message in order to keep consistency with other errors
            if (!empty($aImportResults['error'])) {
                $aData['sErrorMessage'] = $aImportResults['error'];
                $aData['bFailed'] = true;
            }

            if ($action == 'importsurvey' && isset($sFullFilepath) && file_exists($sFullFilepath)) {
                unlink($sFullFilepath);
            }

            if (!$aData['bFailed'] && isset($aImportResults)) {
                $aData['aImportResults'] = $aImportResults;
                $aData['action'] = $action;
                if (isset($aImportResults['newsid'])) {
                    // Set link pointing to survey administration overview. This link will be updated if the survey has groups
                    $aData['sLink'] = $this->createUrl('surveyAdministration/view/', ['iSurveyID' => $aImportResults['newsid']]);
                    $aData['sLinkApplyThemeOptions'] = 'surveyAdministration/applythemeoptions/surveyid/' . $aImportResults['newsid'];
                }
            }
        }
        if (!empty($aImportResults['newsid'])) {
            $oSurvey = Survey::model()->findByPk($aImportResults['newsid']);
            LimeExpressionManager::SetDirtyFlag();
            LimeExpressionManager::singleton();
            // Why this @ !
            LimeExpressionManager::SetSurveyId($aImportResults['newsid']);
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($aImportResults['newsid']);
            LimeExpressionManager::UpgradeConditionsToRelevance($aImportResults['newsid']);
            @LimeExpressionManager::StartSurvey($oSurvey->sid, 'survey', $oSurvey->attributes, true);
            LimeExpressionManager::StartProcessingPage(true, true);
            $aGrouplist = QuestionGroup::model()->findAllByAttributes(['sid' => $aImportResults['newsid']]);
            foreach ($aGrouplist as $aGroup) {
                LimeExpressionManager::StartProcessingGroup($aGroup['gid'], $oSurvey->anonymized != 'Y', $aImportResults['newsid']);
                LimeExpressionManager::FinishProcessingGroup();
            }
            LimeExpressionManager::FinishProcessingPage();

            // Make the link point to the first group/question if available
            if (!empty($aGrouplist)) {
                $oFirstGroup = $aGrouplist[0];
                $oFirstQuestion = Question::model()->findByAttributes(
                    ['gid' => $oFirstGroup->gid],
                    ['order' => 'question_order ASC']
                );

                $aData['sLink'] = $this->getSurveyAndSidemenueDirectionURL(
                    $aImportResults['newsid'],
                    $oFirstGroup->gid,
                    !empty($oFirstQuestion) ? $oFirstQuestion->qid : null,
                    'structure'
                );
            }
        }

        $this->aData = $aData;
        $this->render('importSurvey_view', $this->aData);
    }


    /**
     * Called via ajax request from survey summary quick action "Show questions group by group".
     *
     * @param int    $iSurveyID Given Survey ID
     * @param string $format    Given Format
     *
     * @return void
     */
    public function actionChangeFormat(int $iSurveyID, $format)
    {
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update')) {
            if (in_array($format, array('S', 'G', 'A'))) {
                $survey = Survey::model()->findByPk($iSurveyID);
                $survey->format = $format;
                $survey->save();
                echo $survey->format;
            }
        }
    }


    /**
     * Expires the survey.
     *
     * @return void
     */
    public function actionExpire()
    {
        $iSurveyID = $this->getSurveyIdFromGetRequest();
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyID));
        }
        Yii::app()->session['flashmessage'] = gT("The survey was successfully expired by setting an expiration date in the survey settings.");
        Survey::model()->expire($iSurveyID);
        $this->redirect(array('surveyAdministration/view/surveyid/' . $iSurveyID));
    }

    /**
     *  Ajaxrequest returning 'session['dateformat']'
     *  and some other parameters to the frontend
     *
     * @return void
     *
     * @todo this function should be moved to another controller (?)
     */
    public function actionDatetimesettings()
    {
        if (Permission::model()->hasGlobalPermission('surveys', 'read')) {
            $data = array(
                'dateformatsettings' => getDateFormatData(Yii::app()->session['dateformat']),
                'showClear' => true,
                'allowInputToggle' => true,
            );
            echo json_encode($data);
        }
    }

    /**
     * Action to set expiry date to multiple surveys.
     *  (ajax request)
     *
     * @return void
     * @throws CException
     */
    public function actionExpireMultipleSurveys()
    {
        $sSurveys = $_POST['sItems'];
        $aSIDs = json_decode($sSurveys);
        $aResults = array();
        $expires = App()->request->getPost('expires');
        $formatdata = getDateFormatData(Yii::app()->session['dateformat']);
        Yii::import('application.libraries.Date_Time_Converter', true);
        if (trim($expires) == "") {
            $expires = null;
        } else {
            $datetimeobj = new Date_Time_Converter($expires, $formatdata['phpdate'] . ' H:i');
            $expires = $datetimeobj->convert("Y-m-d H:i:s");
        }

        foreach ($aSIDs as $sid) {
            if ((int)$sid > 0) {
                $survey = Survey::model()->findByPk($sid);
                $survey->expires = $expires;
                $aResults[$survey->primaryKey]['title'] = ellipsize(
                    $survey->correct_relation_defaultlanguage->surveyls_title,
                    30
                );
                if (!Permission::model()->hasSurveyPermission($sid, 'surveysettings', 'update')) {
                    $aResults[$survey->primaryKey]['result'] = false;
                    $aResults[$survey->primaryKey]['error'] = gT("User does not have valid permissions");
                } else {
                    if ($survey->save()) {
                        $aResults[$survey->primaryKey]['result'] = true;
                    } else {
                        $aResults[$survey->primaryKey]['result'] = false;
                        $aResults[$survey->primaryKey]['error'] = gT("Survey update failed");
                    }
                }
            }
        }
        $this->renderPartial(
            'ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results',
            array('aResults' => $aResults, 'successLabel' => gT('OK'))
        );
    }

    /** ************************************************************************************************************ */
    /**                      The following functions could be moved to model or service classes                      */
    /** **********************************************************************************************************++ */

    /**
     * Try to get the get-parameter from request.
     * At the moment there are three namings for a survey id:
     * 'sid'
     * 'surveyid'
     * 'iSurveyID'
     *
     * Returns the id as integer or null if not exists any of them.
     *
     * @return int | null
     *
     * @todo While refactoring (at some point) this function should be removed and only one unique identifier should be used
     */
    private function getSurveyIdFromGetRequest()
    {
         $surveyId = Yii::app()->request->getParam('sid');
        if ($surveyId === null) {
            $surveyId = Yii::app()->request->getParam('surveyid');
        }
        if ($surveyId === null) {
            $surveyId = Yii::app()->request->getParam('iSurveyID');
        }

         return (int) $surveyId;
    }

    /**
     * Returns data for Tab Resourves.
     * survey::_tabResourceManagement()
     * Load "Resources" tab.
     *
     * @todo is this new implementation???
     *
     * @param Survey $survey Given Survey
     *
     * @return array
     */
    protected function tabResourceManagement($survey)
    {
        global $sCKEditorURL;

        // TAB Uploaded Resources Management
        $ZIPimportAction = " onclick='if (window.LS.validatefilename(this.form,\"" . gT('Please select a file to import!', 'js') . "\")) { this.form.submit();}'";
        if (!class_exists('ZipArchive')) {
            $ZIPimportAction = " onclick='alert(\"" . gT("The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.", "js") . "\");'";
        }

        $disabledIfNoResources = '';
        if (hasResources($survey->sid, 'survey') === false) {
            $disabledIfNoResources = " disabled='disabled'";
        }
        $aData = [];
        $aData['ZIPimportAction'] = $ZIPimportAction;
        $aData['disabledIfNoResources'] = $disabledIfNoResources;
        $aData['sCKEditorURL'] = $sCKEditorURL;
        $aData['noform'] = true;

        //KCFINDER SETTINGS
        Yii::app()->session['FileManagerContext'] = "edit:survey:{$survey->sid}";
        Yii::app()->loadHelper('admin.htmleditor');
        initKcfinder();

        return $aData;
    }

    /**
     * Show the form for Organize question groups/questions
     *
     * @param int $iSurveyID Given Survey ID
     *
     * @return array
     * @todo Change function name to _showOrganizeGroupsAndQuestions?
     * @todo Does actually not show anything, but gets data. So getReorderFormData()?
     */
    private function showReorderForm($iSurveyID)
    {
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData = [];
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['sid'] = $iSurveyID; //frontend need this to render topbar for the view

        // Prepare data for the view
        $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
        LimeExpressionManager::StartSurvey($iSurveyID, 'survey');
        LimeExpressionManager::StartProcessingPage(true, Yii::app()->baseUrl);

        $groups = $survey->groups;
        $groupData = [];
        $initializedReplacementFields = false;

        $aData['organizebar']['savebuttonright'] = true;
        $aData['organizebar']['closebuttonright']['url'] = $this->createUrl("surveyAdministration/view/", array('surveyid' => $iSurveyID));
        $aData['organizebar']['saveandclosebuttonright']['url'] = true;
        $aData['surveybar']['buttons']['view'] = true;
        $aData['surveybar']['savebutton']['form'] = 'frmOrganize';
        $aData['topBar']['showSaveButton'] = true;

        foreach ($groups as $iGID => $oGroup) {
            $groupData[$iGID]['gid'] = $oGroup->gid;
            $groupData[$iGID]['group_text'] = $oGroup->gid . ' ' . $oGroup->questiongroupl10ns[$sBaseLanguage]->group_name;
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
                    $question->questionl10ns[$sBaseLanguage]->question
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
        LimeExpressionManager::FinishProcessingPage();

        $aData['aGroupsAndQuestions'] = $groupData;
        $aData['surveyid'] = $iSurveyID;

        return $aData;
    }

    /**
     * Get the new question organization from the post data.
     * This function replaces parse_str, since parse_str
     * is bound by max_input_vars.
     *
     * @return array
     */
    private function getOrgdata()
    {
        $request = Yii::app()->request;
        $orgdata = $request->getPost('orgdata');
        $ex = explode('&', $orgdata);
        $vars = array();
        foreach ($ex as $str) {
            list($list, $target) = explode('=', $str);
            $list = str_replace('list[', '', $list);
            $list = str_replace(']', '', $list);
            $vars[$list] = $target;
        }

        return $vars;
    }

    /**
     * Reorder groups and questions
     *
     * REFACTORED in SurveyAdministration
     *
     * @param int $iSurveyID Given Survey ID
     *
     * @return void
     */
    private function reorderGroup($iSurveyID)
    {
        $grouporder = 1;
        $orgdata = $this->getOrgdata();
        foreach ($orgdata as $ID => $parent) {
            if ($parent == 'root' && $ID[0] == 'g') {
                QuestionGroup::model()->updateAll(
                    array('group_order' => $grouporder),
                    'gid=:gid',
                    array(':gid' => (int) substr($ID, 1))
                );
                $grouporder++;
            } elseif ($ID[0] == 'q') {
                $qid = (int) substr($ID, 1);
                $gid = (int) substr($parent, 1);
                if (!isset($aQuestionOrder[$gid])) {
                    $aQuestionOrder[$gid] = 0;
                }

                $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
                $oQuestion = Question::model()->findByPk(array("qid" => $qid, 'language' => $sBaseLanguage));
                $oldGid = $oQuestion['gid'];
                if ($oldGid != $gid) {
                    fixMovedQuestionConditions($qid, $oldGid, $gid, $iSurveyID);
                }
                Question::model()->updateAll(
                    array(
                    'question_order' => $aQuestionOrder[$gid],
                    'gid' => $gid),
                    'qid=:qid',
                    array(':qid' => $qid)
                );
                Question::model()->updateAll(array('gid' => $gid), 'parent_qid=:parent_qid', array(':parent_qid' => $qid));
                $aQuestionOrder[$gid]++;
            }
        }
        LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
        Yii::app()->session['flashmessage'] = gT("The new question group/question order was successfully saved.");
    }

    /**
     * Returns Data for Plugin tab.
     * survey::_pluginTabSurvey()
     * Load "Simple Plugin" page in specific survey.
     *
     * @param Survey $survey Given Survey
     *
     * @return array
     *
     * This method is called via call_user_func in self::rendersidemenulink()
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function pluginTabSurvey($survey)
    {
        $aData = array();
        $beforeSurveySettings = new PluginEvent('beforeSurveySettings');
        $beforeSurveySettings->set('survey', $survey->sid);
        App()->getPluginManager()->dispatchEvent($beforeSurveySettings);
        $aData['pluginSettings'] = $beforeSurveySettings->get('surveysettings');
        return $aData;
    }

    /**
     * Update the theme of a survey
     *
     * @param int $iSurveyID Survey ID
     * @param string $template The survey theme name
     * @param array $aResults If the method is called from changeMultipleTheme(), it will update its array of results
     * @param boolean $bReturn Should the method update and return aResults
     *
     * @return mixed                 null or array
     *
     * @access public
     */
    public function changeTemplate($iSurveyID, $template, $aResults = null, $bReturn = false)
    {
        $iSurveyID = sanitize_int($iSurveyID);
        $sTemplate = sanitize_dirname($template);

        $survey = Survey::model()->findByPk($iSurveyID);

        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
            if (!empty($bReturn)) {
                $aResults[$iSurveyID]['title'] = $survey->correct_relation_defaultlanguage->surveyls_title;
                $aResults[$iSurveyID]['result'] = false;
                $aResults[$iSurveyID]['error'] = gT("User does not have valid permissions");
                return $aResults;
            } else {
                die('No permission');
            }
        } elseif (!Permission::model()->hasGlobalPermission('templates', 'read') && !Permission::model()->hasTemplatePermission($template)) {
            if (!empty($bReturn)) {
                $aResults[$iSurveyID]['title'] = $survey->correct_relation_defaultlanguage->surveyls_title;
                $aResults[$iSurveyID]['result'] = false;
                $aResults[$iSurveyID]['error'] = gT("User does not have permission to use this theme");
                return $aResults;
            } else {
                die('No permission');
            }
        }

        $survey->template = $sTemplate;
        $survey->save();

        $oTemplateConfiguration = $survey->surveyTemplateConfiguration;
        $oTemplateConfiguration->template_name = $sTemplate;
        $oTemplateConfiguration->save();

        // This will force the generation of the entry for survey group
        TemplateConfiguration::checkAndcreateSurveyConfig($iSurveyID);

        if (!empty($bReturn)) {
            $aResults[$iSurveyID]['title'] = $survey->correct_relation_defaultlanguage->surveyls_title;
            $aResults[$iSurveyID]['result'] = true;
            return $aResults;
        }
    }

    /**
     * This method will return the url for the current survey and set
     * the direction for the sidemenue.
     *
     * @param integer $sid Given Survey ID
     * @param integer $gid Given Group ID
     * @param integer $qid Given Question ID
     * @param string $landOnSideMenuTab Given SideMenuTab
     *
     * @return string
     */
    public function getSurveyAndSidemenueDirectionURL($sid, $gid, $qid, $landOnSideMenuTab)
    {
        $url = !empty($qid) ? 'questionAdministration/view/' : 'questionGroupsAdministration/view/';
        $params = [
            'surveyid' => $sid,
            'gid' => $gid,
        ];
        if (!empty($qid)) {
            $params['qid'] = $qid;
        }
        $params['landOnSideMenuTab'] = $landOnSideMenuTab;
        return $this->createUrl($url, $params);
    }

    /**
     * This private function creates a sample group
     *
     * @param int $iSurveyID The survey ID that the sample group will belong to
     *
     * @return int
     */
    private function createSampleGroup($iSurveyID)
    {
        // Now create a new dummy group
        $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
        $oGroup = new QuestionGroup();
        $oGroup->sid = $iSurveyID;
        $oGroup->group_order = 1;
        $oGroup->grelevance = '1';
        $oGroup->save();
        $oGroupL10ns = new QuestionGroupL10n();
        $oGroupL10ns->gid = $oGroup->gid;
        $oGroupL10ns->group_name = gT('My first question group', 'html', $sLanguage);
        $oGroupL10ns->language = $sLanguage;
        $oGroupL10ns->save();
        LimeExpressionManager::SetEMLanguage($sLanguage);
        return $oGroup->gid;
    }

    /**
     * This private function creates a sample question
     *
     * @param int $iSurveyID The survey ID that the sample question will belong to
     * @param int $iGroupID The group ID that the sample question will belong to
     *
     * @return int
     */
    private function createSampleQuestion($iSurveyID, $iGroupID)
    {
        // Now create a new dummy question
        $sLanguage = Survey::model()->findByPk($iSurveyID)->language;
        $oQuestion = new Question();
        $oQuestion->sid = $iSurveyID;
        $oQuestion->gid = $iGroupID;
        $oQuestion->type = Question::QT_T_LONG_FREE_TEXT;
        $oQuestion->title = 'Q00';
        $oQuestion->mandatory = 'N';
        $oQuestion->relevance = '1';
        $oQuestion->question_order = 1;
        $oQuestion->save();
        $oQuestionLS = new QuestionL10n();
        $oQuestionLS->question = gT('A first example question. Please answer this question:', 'html', $sLanguage);
        $oQuestionLS->help = gT('This is a question help text.', 'html', $sLanguage);
        $oQuestionLS->language = $sLanguage;
        $oQuestionLS->qid = $oQuestion->qid;
        $oQuestionLS->save();
        return $oQuestion->qid;
    }

    /**
     * Adds some other important adata variables for frontend
     *
     * this function came from Layouthelper
     *
     * @param array $aData pointer to array (this array will be changed here!!)
     *
     * @throws CException
     */
    private function surveysummary(&$aData)
    {
        $iSurveyID = $aData['surveyid'];

        $aSurveyInfo = getSurveyInfo($iSurveyID);
        /** @var Survey $oSurvey */
        if (!isset($aData['oSurvey'])) {
            $aData['oSurvey'] = Survey::model()->findByPk($aData['surveyid']);
        }
        $oSurvey = $aData['oSurvey'];
        $activated = $aSurveyInfo['active'];

        $condition = array('sid' => $iSurveyID, 'parent_qid' => 0);
        $sumcount3 = Question::model()->countByAttributes($condition); //Checked
        $sumcount2 = QuestionGroup::model()->countByAttributes(array('sid' => $iSurveyID));

        //SURVEY SUMMARY
        $aAdditionalLanguages = $oSurvey->additionalLanguages;
        $surveysummary2 = [];
        if ($aSurveyInfo['anonymized'] != "N") {
            $surveysummary2[] = gT("Responses to this survey are anonymized.");
        } else {
            $surveysummary2[] = gT("Responses to this survey are NOT anonymized.");
        }
        if ($aSurveyInfo['format'] == "S") {
            $surveysummary2[] = gT("It is presented question by question.");
        } elseif ($aSurveyInfo['format'] == "G") {
            $surveysummary2[] = gT("It is presented group by group.");
        } else {
            $surveysummary2[] = gT("It is presented on one single page.");
        }
        if ($aSurveyInfo['questionindex'] > 0) {
            if ($aSurveyInfo['format'] == 'A') {
                $surveysummary2[] = gT("No question index will be shown with this format.");
            } elseif ($aSurveyInfo['questionindex'] == 1) {
                $surveysummary2[] = gT("A question index will be shown; participants will be able to jump between viewed questions.");
            } elseif ($aSurveyInfo['questionindex'] == 2) {
                $surveysummary2[] = gT("A full question index will be shown; participants will be able to jump between relevant questions.");
            }
        }
        if ($oSurvey->isDateStamp) {
            $surveysummary2[] = gT("Responses will be date stamped.");
        }
        if ($oSurvey->isIpAddr) {
            $surveysummary2[] = gT("IP Addresses will be logged");
        }
        if ($oSurvey->isRefUrl) {
            $surveysummary2[] = gT("Referrer URL will be saved.");
        }
        if ($oSurvey->isUseCookie) {
            $surveysummary2[] = gT("It uses cookies for access control.");
        }
        if ($oSurvey->isAllowRegister) {
            $surveysummary2[] = gT("If participant access codes are used, the public may register for this survey");
        }
        if ($oSurvey->isAllowSave && !$oSurvey->isTokenAnswersPersistence) {
            $surveysummary2[] = gT("Participants can save partially finished surveys");
        }
        if ($oSurvey->emailnotificationto != '') {
            $surveysummary2[] = gT("Basic email notification is sent to:") . ' ' . htmlspecialchars($aSurveyInfo['emailnotificationto']);
        }
        if ($oSurvey->emailresponseto != '') {
            $surveysummary2[] = gT("Detailed email notification with response data is sent to:") . ' ' . htmlspecialchars($aSurveyInfo['emailresponseto']);
        }

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        if (trim($oSurvey->startdate) != '') {
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($oSurvey->startdate, 'Y-m-d H:i:s');
            $aData['startdate'] = $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
        } else {
            $aData['startdate'] = "-";
        }

        if (trim($oSurvey->expires) != '') {
            Yii::import('application.libraries.Date_Time_Converter');
            $datetimeobj = new Date_Time_Converter($oSurvey->expires, 'Y-m-d H:i:s');
            $aData['expdate'] = $datetimeobj->convert($dateformatdetails['phpdate'] . ' H:i');
        } else {
            $aData['expdate'] = "-";
        }

        $aData['language'] = getLanguageNameFromCode($oSurvey->language, false);

        if ($oSurvey->currentLanguageSettings->surveyls_urldescription == "") {
            $aSurveyInfo['surveyls_urldescription'] = htmlspecialchars($aSurveyInfo['surveyls_url']);
        }

        if ($oSurvey->currentLanguageSettings->surveyls_url != "") {
            $aData['endurl'] = " <a target='_blank' href=\"" .
                                htmlspecialchars($aSurveyInfo['surveyls_url']) .
                                "\" title=\"" .
                                 htmlspecialchars($aSurveyInfo['surveyls_url']) .
                                 "\">" .
                                flattenText($oSurvey->currentLanguageSettings->surveyls_url) .
                                "</a>";
        } else {
            $aData['endurl'] = "-";
        }

        $aData['sumcount3'] = $sumcount3;
        $aData['sumcount2'] = $sumcount2;

        if ($activated == "N") {
            $aData['activatedlang'] = gT("No");
        } else {
            $aData['activatedlang'] = gT("Yes");
        }

        $aData['activated'] = $activated;
        if ($oSurvey->isActive) {
            $aData['surveydb'] = Yii::app()->db->tablePrefix . "survey_" . $iSurveyID;
        }

        $aData['warnings'] = [];
        if ($activated == "N" && $sumcount3 == 0) {
            $aData['warnings'][] = gT("Survey cannot be activated yet.");
            if ($sumcount2 == 0 && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'create')) {
                $aData['warnings'][] = "<span class='statusentryhighlight'>[" . gT("You need to add question groups") . "]</span>";
            }
            if ($sumcount3 == 0 && Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'create')) {
                $aData['warnings'][] = "<span class='statusentryhighlight'>" . gT("You need to add questions") . "</span>";
            }
        }
        $aData['hints'] = $surveysummary2;

        //return (array('column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
        //        $aData['tableusage'] = getDBTableUsage($iSurveyID);
        // ToDo: Table usage is calculated on every menu display which is too slow with big surveys.
        // Needs to be moved to a database field and only updated if there are question/subquestions added/removed (it's currently also not functional due to the port)

        $aData['tableusage'] = false;
        $aData['aAdditionalLanguages'] = $aAdditionalLanguages;
        $aData['groups_count'] = $sumcount2;

        // We get the state of the quickaction
        // If the survey is new (ie: it has no group), it is opened by default
        $quickactionState = SettingsUser::getUserSettingValue('quickaction_state');
        if ($quickactionState === null || $quickactionState === 0) {
            $quickactionState = 1;
            SettingsUser::setUserSetting('quickaction_state', 1);
        }
        $aData['quickactionstate'] = $quickactionState !== null ? intval($quickactionState) : 1;
        $aData['subviewData'] = $aData;

        Yii::app()->getClientScript()->registerPackage('surveysummary');
    }

    /**
     * Load survey information based on $action.
     * survey::_fetchSurveyInfo()
     *
     * @param string $action Given Action
     * @param int $iSurveyID Given Survey ID
     *
     * @return void | array
     *
     * @deprecated use Survey objects instead
     */
    private function fetchSurveyInfo($action, $iSurveyID = null)
    {
        if ($action == 'newsurvey') {
            $oSurvey = new Survey();
        } elseif ($action == 'editsurvey' && $iSurveyID) {
            $oSurvey = Survey::model()->findByPk($iSurveyID);
        }

        if (isset($oSurvey)) {
            $attribs = $oSurvey->attributes;
            $attribs['googleanalyticsapikeysetting'] = $oSurvey->getGoogleanalyticsapikeysetting();
            return $attribs;
        }
    }

    /**
     * Load "General" tab of new survey screen.
     * survey::_generalTabNewSurvey()
     *
     * @return array
     */
    private function generalTabNewSurvey()
    {
        // use survey option inheritance
        $user = User::model()->findByPk(Yii::app()->session['loginID']);
        $owner = $user->attributes;
        $owner['full_name'] = 'inherit';
        $owner['email'] = 'inherit';
        $owner['bounce_email'] = 'inherit';

        $aData = [];
        $aData['action'] = "newsurvey";
        $aData['owner'] = $owner;
        $aLanguageDetails = getLanguageDetails(Yii::app()->session['adminlang']);
        $aData['sRadixDefault'] = $aLanguageDetails['radixpoint'];
        $aData['sDateFormatDefault'] = $aLanguageDetails['dateformat'];
        $aRadixPointData = [];
        foreach (getRadixPointData() as $index => $radixptdata) {
            $aRadixPointData[$index] = $radixptdata['desc'];
        }
        $aData['aRadixPointData'] = $aRadixPointData;

        foreach (getDateFormatData(0, Yii::app()->session['adminlang']) as $index => $dateformatdata) {
            $aDateFormatData[$index] = $dateformatdata['dateformat'];
        }
        $aData['aDateFormatData'] = $aDateFormatData;

        return $aData;
    }

    /**
     * Returns Data for general template.
     *
     * @param integer $iSurveyID Given Survey ID
     *
     * @return array
     */
    private function getGeneralTemplateData($iSurveyID)
    {
        $aData = [];
        $aData['surveyid'] = $iSurveyID;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        if (empty($oSurvey)) {
            $oSurvey = new Survey();
        }
        $inheritOwner = empty($oSurvey->oOptions->ownerLabel) ? $oSurvey->owner_id : $oSurvey->oOptions->ownerLabel;
        $users = getUserList();
        $aData['users'] = array();
        $aData['users']['-1'] = gT('Inherit') . ' [' . $inheritOwner . ']';
        foreach ($users as $user) {
            $aData['users'][$user['uid']] = $user['user'] . ($user['full_name'] ? ' - ' . $user['full_name'] : '');
        }
        // Sort users by name
        asort($aData['users']);
        $aData['aSurveyGroupList'] = SurveysGroups::getSurveyGroupsList();
        return $aData;
    }

    /**
     * Returns data for text edit.
     *
     * BE CAREFUL (this function is not called directly, but by call_user_func_array and the name of function in db .. )
     *
     * @param Survey $survey Given Survey.
     *
     * @return array
     */
    protected function getTextEditData($survey)
    {
        Yii::app()->getClientScript()->registerScript(
            "TextEditDataGlobal",
            //todo: this has to be changed, also in frontend packages/textelements/...
            //NEW: connectorBaseUrl: '".Yii::app()->getController()->createUrl('/surveyAdministration/getDateFormatOptions', ['sid' => $survey->sid])."',
            //// ['sid' => $survey->sid]) . "',  --> sid should be taken from frontend ...
            "window.TextEditData = {
                connectorBaseUrl: '" . Yii::app()->getController()->createUrl('surveyAdministration/') . "',
                isNewSurvey: " . ($survey->getIsNewRecord() ? "true" : "false") . ",
                sid: $survey->sid, 
                i10N: {
                    'Survey title' : '" . gT('Survey title') . "',
                    'Date format' : '" . gT('Date format') . "',
                    'Decimal mark' : '" . gT('Decimal mark') . "',
                    'End url' : '" . gT('End url') . "',
                    'URL description (link text)' : '" . gT('URL description (link text)') . "',
                    'Description' : '" . gT('Description') . "',
                    'Welcome' : '" . gT('Welcome') . "',
                    'End message' : '" . gT('End message') . "'
                }
            };",
            LSYii_ClientScript::POS_BEGIN
        );

        App()->getClientScript()->registerPackage('ace');
        return [];
    }

    /**
     * Returns Date for Data Security Edit.
     * tab_edit_view_datasecurity
     * editDataSecurityLocalSettings_view
     *
     * @param Survey $survey Given Survey
     *
     * @return array
     */
    protected function getDataSecurityEditData($survey)
    {
        Yii::app()->loadHelper("admin.htmleditor");
        $aData = $aTabTitles = $aTabContents = array();

        $aData['scripts'] = PrepareEditorScript(false, $this);
        $aLanguageData = [];

        foreach ($survey->allLanguages as $i => $sLang) {
            $aLanguageData = $this->getGeneralTemplateData($survey->sid);
            // this one is created to get the right default texts fo each language
            Yii::app()->loadHelper('database');
            Yii::app()->loadHelper('surveytranslator');

            $aSurveyLanguageSettings = SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id' => $survey->sid, 'surveyls_language' => $sLang))->getAttributes();

            $aTabTitles[$sLang] = getLanguageNameFromCode($aSurveyLanguageSettings['surveyls_language'], false);

            if ($aSurveyLanguageSettings['surveyls_language'] == $survey->language) {
                $aTabTitles[$sLang] .= ' (' . gT("Base language") . ')';
            }

            $aLanguageData['aSurveyLanguageSettings'] = $aSurveyLanguageSettings;
            $aLanguageData['action'] = "surveygeneralsettings";
            $aLanguageData['subaction'] = gT('Add a new question');
            $aLanguageData['i'] = $i;
            $aLanguageData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);
            $aLanguageData['oSurvey'] = $survey;
            $aTabContents[$sLang] = $this->renderPartial('/admin/survey/editDataSecurityLocalSettings_view', $aLanguageData, true);
        }

        $aData['aTabContents'] = $aTabContents;
        $aData['aTabTitles'] = $aTabTitles;
        return $aData;
    }

    /**
     * Returns Data for Tab General Edit Survey.
     * survey::_generalTabEditSurvey()
     * Load "General" tab of edit survey screen.
     *
     * @param Survey $survey Given Survey
     *
     * @return mixed
     */
    protected function generalTabEditSurvey($survey)
    {
        $aData['survey'] = $survey;
        return $aData;
    }

    /**
     * Returns data for tab Presentation navigation.
     * survey::_tabPresentationNavigation()
     * Load "Presentation & navigation" tab.
     *
     * @param mixed $survey ?
     *
     * @return array
     */
    protected function tabPresentationNavigation($survey)
    {
        $aData = [];
        $aData['esrow'] = $survey;
        return $aData;
    }

    /**
     * Returns the data for Tab Publication Access control.
     * survey::_tabPublicationAccess()
     * Load "Publication * access control" tab.
     *
     * @param Survey $survey Given Survey
     *
     * @return array
     */
    protected function tabPublicationAccess($survey)
    {
        $aDateFormatDetails = getDateFormatData(Yii::app()->session['dateformat']);
        $aData = [];
        $aData['dateformatdetails'] = $aDateFormatDetails;
        $aData['survey'] = $survey;
        return $aData;
    }

    /**
     * Returns the data for Tab Notification and Data Management.
     * survey::_tabNotificationDataManagement()
     * Load "Notification & data management" tab.
     *
     * @param mixed $survey ?
     *
     * @return array
     */
    protected function tabNotificationDataManagement($survey)
    {
        $aData = [];
        $aData['esrow'] = $survey;
        return $aData;
    }

    /**
     * Returns the data for Tab Tokens.
     * survey::_tabTokens()
     * Load "Tokens" tab.
     *
     * @param mixed $survey ?
     *
     * @return array
     */
    protected function tabTokens($survey)
    {
        $aData = [];
        $aData['esrow'] = $survey;
        return $aData;
    }

    /**
     * Returns the data for Tab Panel Integration.
     *
     * @param Survey $survey Given Survey
     * @param string|null $sLang  Given Language
     *
     * @return array
     */
    protected function tabPanelIntegration($survey, $sLang = null)
    {
        $aData = [];
        $oResult = Question::model()->with('subquestions')->findAll("t.sid={$survey->sid} AND (t.type = 'T'  OR t.type = 'Q'  OR  t.type = 'S') AND t.parent_qid = 0");
        $aQuestions = [];
        foreach ($oResult as $oRecord) {
            if (count($oRecord->subquestions)) {
                foreach ($oRecord->subquestions as $oSubquestion) {
                    $aQuestions[] = array_merge(
                        $oRecord->attributes,
                        $oRecord->questionl10ns[$survey->language]->attributes,
                        array(
                            'sqid' => $oSubquestion->qid,
                            'sqtitle' => $oSubquestion->title,
                            'sqquestion' => $oSubquestion->questionl10ns[$survey->language]->question
                        )
                    );
                }
            } else {
                $aQuestions[] = array_merge(
                    $oRecord->attributes,
                    $oRecord->questionl10ns[$survey->language]->attributes,
                    array(
                        'sqid' => null,
                        'sqtitle' => null,
                        'sqquestion' => null
                    )
                );
            }
        }

        $aData['jsData'] = [
            'i10n' => [
                'ID' => gT('ID'),
                'Action' => gT('Action'),
                'Parameter' => gT('Parameter'),
                'Target question' => gT('Target question'),
                'Survey ID' => gT('Survey id'),
                'Question ID' => gT('Question id'),
                'Subquestion ID' => gT('Subquestion ID'),
                'Add URL parameter' => gT('Add URL parameter'),
                'Edit URL parameter' => gT('Edit URL parameter'),
                'Parameter' => gT('Parameter'),
                'Target question' => gT('Target question'),
                'No target question' => gT('(No target question)'),
                'Are you sure you want to delete this URL parameter?' => gT('Are you sure you want to delete this URL parameter?'),
                'No parameters defined' => gT('No parameters defined'),
                'Search prompt' => gT('Search:'),
                'Progress' => gT('Showing _START_ to _END_ of _TOTAL_ entries'),
                'No, cancel' => gT('No, cancel'),
                'Yes, delete' => gT('Yes, delete'),
                'Save' => gT('Save'),
                'Cancel' => gT('Cancel'),
            ],
            "surveyid" => $survey->sid,
            "getParametersUrl" => Yii::app()->createUrl('surveyAdministration/getUrlParamsJson', array('surveyid' => $survey->sid)),
        ];
        $aData['questions'] = $aQuestions;

        App()->getClientScript()->registerPackage('jquery-datatable');
        return $aData;
    }
}
