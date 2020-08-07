<?php


class QuestionEditorController extends LSBaseController
{
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
     * Renders the main view for question editor.
     * Main view function prepares the necessary global js parts and renders the HTML for the question editor
     *
     * @param integer $surveyid
     * @param integer $gid
     * @param integer $qid
     * @param string  $landOnSideMenuTab Name of the side menu tab. Default behavior is to land on structure tab.
     *
     * @return void
     *
     * @throws CException
     */
    public function actionView($surveyid, $gid = null, $qid = null, $landOnSideMenuTab = 'structure')
    {
        /* Minimal security */
        $surveyid = $this->getValidatedSurveyId($surveyid, $gid, $qid);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }

        $iSurveyID = $surveyid;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $gid = $gid ?? $oSurvey->groups[0]->gid;
        $oQuestion = $this->getQuestionObject($qid, null, $gid);
        App()->getClientScript()->registerPackage('questioneditor');
        App()->getClientScript()->registerPackage('ace');
        $qrrow = $oQuestion->attributes;
        $baselang = $oSurvey->language;

        if (App()->session['questionselectormode'] !== 'default') {
            $questionSelectorType = App()->session['questionselectormode'];
        } else {
            $questionSelectorType = App()->getConfig('defaultquestionselectormode');
        }

        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['questionbar']['buttons']['view'] = true;

        // Last question visited : By user (only one by user)
        $setting_entry = 'last_question_' . App()->user->getId();
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);

        // we need to set the sid for this question
        $setting_entry = 'last_question_sid_' . App()->user->getId();
        SettingGlobal::setSetting($setting_entry, $iSurveyID);

        // we need to set the gid for this question
        $setting_entry = 'last_question_gid_' . App()->user->getId();
        SettingGlobal::setSetting($setting_entry, $gid);

        // Last question for this survey (only one by survey, many by user)
        $setting_entry = 'last_question_' . App()->user->getId() . '_' . $iSurveyID;
        SettingGlobal::setSetting($setting_entry, $oQuestion->qid);

        // we need to set the gid for this question
        $setting_entry = 'last_question_' . App()->user->getId() . '_' . $iSurveyID . '_gid';
        SettingGlobal::setSetting($setting_entry, $gid);

        ///////////
        // combine aData
        $aData['surveyid'] = $iSurveyID;
        $aData['sid'] = $iSurveyID; //todo duplicated here, because it's needed in some functions of
        $aData['oSurvey'] = $oSurvey;
        $aData['aQuestionTypeList'] = QuestionTheme::findAllQuestionMetaDataForSelector();
        $aData['aQuestionTypeStateList'] = QuestionType::modelsAttributes();
        $aData['selectedQuestion'] = QuestionTheme::findQuestionMetaData($oQuestion->type);
        $aData['gid'] = $gid;
        $aData['qid'] = $oQuestion->qid;
        $aData['activated'] = $oSurvey->active;
        $aData['oQuestion'] = $oQuestion;
        $aData['languagelist'] = $oSurvey->allLanguages;
        $aData['qshowstyle'] = '';
        $aData['qrrow'] = $qrrow;
        $aData['baselang'] = $baselang;
        $aData['sImageURL'] = App()->getConfig('adminimageurl');
        $aData['iIconSize'] = App()->getConfig('adminthemeiconsize');
        $aData['display']['menu_bars']['qid_action'] = 'editquestion';
        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['action'] = 'editquestion';
        $aData['editing'] = true;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title
            . " (" . gT("ID") . ":" . $iSurveyID . ")";
        $aData['surveyIsActive'] = $oSurvey->active !== 'N';
        $aData['activated'] = $oSurvey->active;
        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'surveyObject' => $oSurvey->attributes,
            'gid' => $gid,
            'qid' => $oQuestion->qid,
            'startType' => $oQuestion->type,
            'baseSQACode' => [
                'answeroptions' => SettingsUser::getUserSettingValue('answeroptionprefix', App()->user->id) ?? 'AO' ,
                'subquestions' => SettingsUser::getUserSettingValue('subquestionprefix', App()->user->id) ?? 'SQ',
            ],
            'startInEditView' => SettingsUser::getUserSettingValue('noViewMode', App()->user->id) == '1',
            //'connectorBaseUrl' => 'admin/questioneditor', this should not be relevant anymore for the frontend
            'questionSelectorType' => $questionSelectorType,
            'i10N' => [
                'Create question' => gT('Create question'),
                'General settings' => gT("General settings"),
                'Code' => gT('Code'),
                'Text elements' => gT('Text elements'),
                'Question type' => gT('Question type'),
                'Question' => gT('Question'),
                'Help' => gT('Help'),
                'subquestions' => gT('Subquestions'),
                'answeroptions' => gT('Answer options'),
                'Quick add' => gT('Quick add'),
                'Copy subquestions' => gT('Copy subquestions'),
                'Copy answer options' => gT('Copy answer options'),
                'Copy default answers' => gT('Copy default answers'),
                'Copy advanced options' => gT('Copy advanced options'),
                'Predefined label sets' => gT('Predefined label sets'),
                'Save as label set' => gT('Save as label set'),
                'More languages' => gT('More languages'),
                'Add subquestion' => gT('Add subquestion'),
                'Reset' => gT('Reset'),
                'Save' => gT('Save'),
                'Some example subquestion' => gT('Some example subquestion'),
                'Delete' => gT('Delete'),
                'Open editor' => gT('Open editor'),
                'Duplicate' => gT('Duplicate'),
                'No preview available' => gT('No preview available'),
                'Editor' => gT('Editor'),
                'Quick edit' => gT('Quick edit'),
                'Cancel' => gT('Cancel'),
                'Replace' => gT('Replace'),
                'Add' => gT('Add'),
                'Select delimiter' => gT('Select delimiter'),
                'Semicolon' => gT('Semicolon'),
                'Comma' => gT('Comma'),
                'Tab' => gT('Tab'),
                'New rows' => gT('New rows'),
                'Scale' => gT('Scale'),
                'Save and Close' => gT('Save and close'),
                'Script' => gT('Script'),
                'X-Scale (columns)' => gT('X-Scale (columns)'),
                'Y-Scale (lines)' => gT('Y-Scale (lines)'),
                '__SCRIPTHELP' => gT("This optional script field will be wrapped,"
                    . " so that the script is correctly executed after the question is on the screen."
                    . " If you do not have the correct permissions, this will be ignored"),
                "noCodeWarning" =>
                    gT("Please put in a valid code. Only letters and numbers are allowed and it has to start with a letter. For example [Question1]"),
                "alreadyTaken" =>
                    gT("This code is already used - duplicate codes are not allowed."),
                "codeTooLong" =>
                    gT("A question code cannot be longer than 20 characters."),
                "Question cannot be stored. Please check the subquestion codes for duplicates or empty codes." =>
                    gT("Question cannot be stored. Please check the subquestion codes for duplicates or empty codes."),
                "Question cannot be stored. Please check the answer options for duplicates or empty codes." =>
                    gT("Question cannot be stored. Please check the answer options for duplicates or empty codes."),
            ],
        ];

        $aData['topBar']['type'] = 'question';

        $aData['topBar']['importquestion'] = true;
        $aData['topBar']['showSaveButton'] = true;
        $aData['topBar']['savebuttonform'] = 'frmeditgroup';
        $aData['topBar']['closebuttonurl'] = '/admin/survey/sa/listquestions/surveyid/' . $iSurveyID; // Close button

        if ($landOnSideMenuTab !== '') {
            $aData['sidemenu']['landOnSideMenuTab'] = $landOnSideMenuTab;
        }

        $this->aData = $aData;
        $this->render('view', [
            'aQuestionTypeList' => $aData['aQuestionTypeList'],
            'jsData' => $aData['jsData'],
            'aQuestionTypeStateList' => $aData['aQuestionTypeStateList']
        ]);
    }


    /****
     * *** A lot of getter function regarding functionalities and views.
     * *** All called via ajax
     ****/

    /**
     * Returns all languages in a specific survey as a JSON document
     *
     * todo: is this action still in use?? where in the frontend?
     *
     * @param int $iSurveyId
     *
     * @return void
     */
    public function actionGetPossibleLanguages($iSurveyId)
    {
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $iSurveyId = (int) $iSurveyId;
        $aLanguages = Survey::model()->findByPk($iSurveyId)->allLanguages;
        $this->renderJSON($aLanguages);
    }

    /**
     * Action called by the FE editor when a save is triggered.
     *
     * @param int $sid Survey id
     *
     * @return void
     * @throws CException
     */
    public function actionSaveQuestionData($sid)
    {
        $iSurveyId = $sid;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'update')) {
            throw new CHttpException(403);
        }

        $questionData = App()->request->getPost('questionData', []);
        $questionCopy = (boolean) App()->request->getPost('questionCopy');
        $questionCopySettings = App()->request->getPost('copySettings', []);
        $questionCopySettings = array_map(
            function ($value) {
                return !!$value;
            },
            $questionCopySettings
        );

        // Store changes to the actual question data, by either storing it, or updating an old one
        $oQuestion = Question::model()->find('sid = :sid AND qid = :qid', [':sid' => $iSurveyId, ':qid' => $questionData['question']['qid']]);
        if ($oQuestion == null || $questionCopy == true) {
            $oQuestion = $this->storeNewQuestionData($questionData['question']);
        } else {
            $oQuestion = $this->updateQuestionData($oQuestion, $questionData['question']);
        }

        /*
         * Setting up a try/catch scenario to delete a copied/created question,
         * in case the storing of the peripherals breaks
         */
        try {
            // Apply the changes to general settings, advanced settings and translations
            $setApplied = [];

            $setApplied['questionI10N'] = $this->applyI10N($oQuestion, $questionData['questionI10N']);

            $setApplied['generalSettings'] = $this->unparseAndSetGeneralOptions(
                $oQuestion,
                $questionData['generalSettings']
            );

            if (!($questionCopy === true && $questionCopySettings['copyAdvancedOptions'] == false)) {
                $setApplied['advancedSettings'] = $this->unparseAndSetAdvancedOptions(
                    $oQuestion,
                    $questionData['advancedSettings']
                );
            }

            if (!($questionCopy === true && $questionCopySettings['copyDefaultAnswers'] == false)) {
                $setApplied['defaultAnswers'] = $this->copyDefaultAnswers($oQuestion, $questionData['question']['qid']);
            }


            // save advanced attributes default values for given question type
            if (array_key_exists('save_as_default', $questionData['generalSettings'])
                && $questionData['generalSettings']['save_as_default']['formElementValue'] == 'Y') {
                SettingsUser::setUserSetting(
                    'question_default_values_' . $questionData['question']['type'],
                    ls_json_encode($questionData['advancedSettings'])
                );
            } elseif (array_key_exists('clear_default', $questionData['generalSettings'])
                && $questionData['generalSettings']['clear_default']['formElementValue'] == 'Y') {
                SettingsUser::deleteUserSetting('question_default_values_' . $questionData['question']['type']);
            }

            // If set, store subquestions
            if (isset($questionData['scaledSubquestions'])) {
                if (!($questionCopy === true && $questionCopySettings['copySubquestions'] == false)) {
                    $setApplied['scaledSubquestions'] = $this->storeSubquestions(
                        $oQuestion,
                        $questionData['scaledSubquestions'],
                        $questionCopy
                    );
                }
            }

            // If set, store answer options
            if (isset($questionData['scaledAnswerOptions'])) {
                if (!($questionCopy === true && $questionCopySettings['copyAnswerOptions'] == false)) {
                    $setApplied['scaledAnswerOptions'] = $this->storeAnswerOptions(
                        $oQuestion,
                        $questionData['scaledAnswerOptions'],
                        $questionCopy
                    );
                }
            }
        } catch (CException $ex) {
            throw new LSJsonException(
                500,
                gT('Question has been stored, but an error happened: ')."\n".$ex->getMessage(),
                0,
                App()->createUrl(
                    'questionEditor/view/',
                    ["surveyid"=> $oQuestion->sid, 'gid' => $oQuestion->gid, 'qid'=> $oQuestion->qid]
                )
            );
        }

        // Compile the newly stored data to update the FE
        $oNewQuestion = Question::model()->find('sid = :sid AND qid = :qid', [':sid' => $iSurveyId, ':qid' => $oQuestion->qid]);
        $aCompiledQuestionData = $this->getCompiledQuestionData($oNewQuestion);
        $aQuestionAttributeData = QuestionAttribute::model()->getQuestionAttributes($oQuestion->qid);
        $aQuestionGeneralOptions = $this->getGeneralOptions(
            $oQuestion->qid,
            null,
            $oQuestion->gid,
            $aQuestionAttributeData['question_template']
        );
        $aAdvancedOptions = $this->getAdvancedOptions($oQuestion->qid, null);

        // Return a JSON document with the newly stored question data
        $this->renderJSON(
            [
                'success' => array_reduce(
                    $setApplied,
                    function ($coll, $it) {
                        return $coll && $it;
                    },
                    true
                ),
                'message' => ($questionCopy === true
                    ? gT('Question successfully copied')
                    : gT('Question successfully stored')
                ),
                'successDetail' => $setApplied,
                'questionId' => $oQuestion->qid,
                'redirect' => $this->createUrl(
                    'questionEditor/view/',
                    [
                        'surveyid' => $iSurveyId,
                        'gid' => $oQuestion->gid,
                        'qid' => $oQuestion->qid,
                    ]
                ),
                'newQuestionDetails' => [
                    "question" => $aCompiledQuestionData['question'],
                    "scaledSubquestions" => $aCompiledQuestionData['subquestions'],
                    "scaledAnswerOptions" => $aCompiledQuestionData['answerOptions'],
                    "questionI10N" => $aCompiledQuestionData['i10n'],
                    "questionAttributes" => $aQuestionAttributeData,
                    "generalSettings" => $aQuestionGeneralOptions,
                    "advancedSettings" => $aAdvancedOptions,
                ],
                'transfer' => $questionData,
            ]
        );
        App()->close();
    }

    /**
     * Update the data set in the FE
     *
     * @param int $iQuestionId
     * @param string $type
     * @param int $gid Group id
     * @param string $question_template
     *
     * @return void
     * @throws CException
     */
    public function actionReloadQuestionData(
        $iQuestionId = null,
        $type = null,
        $gid = null,
        $question_template = 'core'
    ) {
        /* Minimal security */
        $surveyid = $this->getValidatedSurveyId(null, $gid, $iQuestionId);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId, $type, $gid);

        $aCompiledQuestionData = $this->getCompiledQuestionData($oQuestion);
        $aQuestionGeneralOptions = $this->getGeneralOptions(
            $oQuestion->qid,
            $type,
            $oQuestion->gid,
            $question_template
        );
        $aAdvancedOptions = $this->getAdvancedOptions($oQuestion->qid, $type, $question_template);

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
                $aCompiledQuestionData,
                [
                    'languages' => $aLanguages,
                    'mainLanguage' => $oQuestion->survey->language,
                    'generalSettings' => $aQuestionGeneralOptions,
                    'advancedSettings' => $aAdvancedOptions,
                    'questiongroup' => $oQuestion->group->attributes,
                ]
            )
        );
    }

    /**
     * @todo document me
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param int $gid
     * @param boolean $returnArray
     * @param string $question_template
     *
     * @return void|array
     * @throws CException
     */
    public function actionGetGeneralOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $gid = null,
        $returnArray = false,  //todo see were this ajaxrequest is done and take out the parameter there and here
        $question_template = 'core'
    ) {
        $surveyid = $this->getValidatedSurveyId(null, $gid, $iQuestionId);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $aGeneralOptionsArray = $this->getGeneralOptions($iQuestionId, $sQuestionType, $gid, $question_template);
        $this->renderJSON($aGeneralOptionsArray);
    }


    /**
     * Action (called by ajaxrequest and returning json)
     * Returns a preformatted json of advanced settings.
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param boolean $returnArray
     * @param string $question_template
     *
     * @return void|array
     * @throws CException
     */
    public function actionGetAdvancedOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $returnArray = false, //todo see were this ajaxrequest is done and take out the parameter there and here
        $question_template = 'core'
    ) {
        $surveyid = $this->getValidatedSurveyId(null, null, $iQuestionId);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        //here we get a Question object (also if question is new --> QuestionCreate)
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType);
        $aAdvancedOptionsArray = $this->getAdvancedOptions($iQuestionId, $sQuestionType, $question_template);

        $this->renderJSON(
            [
                'advancedSettings' => $aAdvancedOptionsArray,
                'questionTypeDefinition' => $oQuestion->questionType,
            ]
        );
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
        $surveyid = $this->getValidatedSurveyId(null, $gid, $iQuestionId);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $iQuestionId = (int) $iQuestionId;
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
                    'surveyInfo' => $surveyInfo,
                    'languages' => $aLanguages,
                    'mainLanguage' => $oQuestion->survey->language,
                ]
            )
        );
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
        $surveyid = $this->getValidatedSurveyId(null, null, $iQuestionId);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $iQuestionId = (int) $iQuestionId;
        $oQuestion = $this->getQuestionObject($iQuestionId);

        $aPermissions = [
            "read" => Permission::model()->hasSurveyPermission($oQuestion->sid, 'survey', 'read'),
            "update" => Permission::model()->hasSurveyPermission($oQuestion->sid, 'survey', 'update'),
            "editorpreset" => App()->session['htmleditormode'],
            "script" =>
                Permission::model()->hasSurveyPermission($oQuestion->sid, 'survey', 'update')
                && SettingsUser::getUserSetting('showScriptEdit', App()->user->id),
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
        $surveyid = $this->getValidatedSurveyId(null, null, $qid);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $oQuestion = $this->getQuestionObject($qid);
        $sid = $oQuestion->sid;
        $gid = $oQuestion->gid;
        $qid = $oQuestion->qid;
        // TODO: Rename Variable for better readability.
        $qtypes = QuestionType::modelsAttributes();
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
            '/admin/survey/topbar/question_topbar',
            array(
                'oSurvey' => $oQuestion->survey,
                'sid' => $sid,
                'hasCopyPermission'   => $hasCopyPermission,
                'hasUpdatePermission' => $hasUpdatePermission,
                'hasExportPermission' => $hasExportPermission,
                'hasDeletePermission' => $hasDeletePermission,
                'hasReadPermission'   => $hasReadPermission,
                'gid' => $gid,
                'qid' => $qid,
                'qrrow' => $qrrow,
                'qtypes' => $qtypes,
                'ownsSaveButton' => $ownsSaveButton,
                'ownsImportButton' => $ownsImportButton,
            ),
            false,
            false
        );
    }


    /** ++++++++++++  the following functions should be moved to model or a service class ++++++++++++++++++++++++++ ?*/

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
    private function getQuestionObject($iQuestionId = null, $sQuestionType = null, $gid = null)
    {
        $iSurveyId = $this->getValidatedSurveyId(null, $gid, $iQuestionId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $oQuestion = Question::model()->find('sid = :sid AND qid = :qid', [':sid' => $iSurveyId, ':qid' => $iQuestionId]);

        if ($oQuestion == null) {
            $oQuestion = QuestionCreate::getInstance($iSurveyId, $sQuestionType);
        }

        if ($sQuestionType != null) {
            $oQuestion->type = $sQuestionType;
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
     * @param string $question_template
     *
     * @return void|array
     * @throws CException
     */
    public function getGeneralOptions(
        $iQuestionId = null,
        $sQuestionType = null,
        $gid = null,
        $question_template = 'core'
    ) {
        $surveyid = $this->getValidatedSurveyId(null, $gid, $iQuestionId);
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            throw new CHttpException(403);
        }
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType, $gid);
        return $oQuestion
            ->getDataSetObject()
            ->getGeneralSettingsArray($oQuestion->qid, $sQuestionType, null, $question_template);
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
            $aScaledSubquestions[$scaleId] = array_map(function ($oSubQuestion) {
                return array_merge($oSubQuestion->attributes, $oSubQuestion->questionl10ns);
            }, $aSubquestions);
        }

        $aScaledAnswerOptions = $oQuestion->getOrderedAnswers();
        foreach ($aScaledAnswerOptions as $scaleId => $aAnswerOptions) {
            $aScaledAnswerOptions[$scaleId] = array_map(function ($oAnswerOption) {
                return array_merge($oAnswerOption->attributes, $oAnswerOption->answerl10ns);
            }, $aAnswerOptions);
        }
        $aReplacementData = [];
        $questioni10N = [];
        foreach ($oQuestion->questionl10ns as $lng => $oQuestionI10N) {
            $questioni10N[$lng] = $oQuestionI10N->attributes;

            templatereplace(
                $oQuestionI10N->question,
                array(),
                $aReplacementData,
                'Unspecified',
                false,
                $oQuestion->qid
            );

            $questioni10N[$lng]['question_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );

            templatereplace($oQuestionI10N->help, array(), $aReplacementData, 'Unspecified', false, $oQuestion->qid);
            $questioni10N[$lng]['help_expression'] = viewHelper::stripTagsEM(
                LimeExpressionManager::GetLastPrettyPrintExpression()
            );
        }
        LimeExpressionManager::FinishProcessingPage();
        return [
            'question' => $aQuestionDefinition,
            'questiongroup' => $aQuestionGroupDefinition,
            'i10n' => $questioni10N,
            'subquestions' => $aScaledSubquestions,
            'answerOptions' => $aScaledAnswerOptions,
        ];
    }

    /**
     * It returns a preformatted array of advanced settings.
     *
     * @param int $iQuestionId
     * @param string $sQuestionType
     * @param string $question_template
     * @return array
     * @throws CException
     * @throws Exception
     */
    private function getAdvancedOptions($iQuestionId = null, $sQuestionType = null, $question_template = 'core')
    {
        //here we get a Question object (also if question is new --> QuestionCreate)
        $oQuestion = $this->getQuestionObject($iQuestionId, $sQuestionType);

        return $oQuestion->getDataSetObject()->getPreformattedBlockOfAdvancedSettings(
            $oQuestion,
            $question_template
        );
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
            ->queryColumn([':sid'=>$oSurvey->sid]);
        $isActive = $oSurvey->isActive;
        $questionCount = safecount($aQuestionTitles);
        $groupCount = safecount($oSurvey->groups);

        return [
            "aQuestionTitles" => $aQuestionTitles,
            "isActive" => $isActive,
            "questionCount" => $questionCount,
            "groupCount" => $groupCount,
        ];
    }

    /**
     * Method to store and filter questionData for a new question
     *
     * todo: move to model or service class
     *
     * @param array $aQuestionData
     * @param boolean $subquestion
     * @return Question
     * @throws CHttpException
     */
    private function storeNewQuestionData($aQuestionData = null, $subquestion = false)
    {
        $iSurveyId = $aQuestionData['sid'];
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $iQuestionGroupId = App()->request->getParam('gid');
        $type = SettingsUser::getUserSettingValue(
            'preselectquestiontype',
            null,
            null,
            null,
            App()->getConfig('preselectquestiontype')
        );

        if (isset($aQuestionData['same_default'])) {
            if ($aQuestionData['same_default'] == 1) {
                $aQuestionData['same_default'] =0;
            } else {
                $aQuestionData['same_default'] =1;
            }
        }

        $aQuestionData = array_merge([
            'sid' => $iSurveyId,
            'gid' => App()->request->getParam('gid'),
            'type' => $type,
            'other' => 'N',
            'mandatory' => 'N',
            'relevance' => 1,
            'group_name' => '',
            'modulename' => '',
        ], $aQuestionData);
        unset($aQuestionData['qid']);

        if ($subquestion) {
            foreach ($oSurvey->allLanguages as $sLanguage) {
                unset($aQuestionData[$sLanguage]);
            }
        } else {
            $aQuestionData['question_order'] = getMaxQuestionOrder($iQuestionGroupId) + 1;
        }

        $oQuestion = new Question();
        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new LSJsonException(
                500,
                gT("Question creation failed - input was malformed or invalid"),
                0,
                null,
                true
            );
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw new LSJsonException(
                500,
                "Object creation failed, couldn't save.\n ERRORS:\n"
                . print_r($oQuestion->getErrors(), true),
                0,
                null,
                true
            );
        }

        $i10N = [];
        foreach ($oSurvey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = new QuestionL10n();
            $i10N[$sLanguage]->setAttributes([
                'qid' => $oQuestion->qid,
                'language' => $sLanguage,
                'question' => '',
                'help' => '',
            ], false);
            $i10N[$sLanguage]->save();
        }

        return $oQuestion;
    }

    /**
     * Method to store and filter questionData for editing a question
     *
     * @param Question $oQuestion
     * @param array $aQuestionData
     * @return Question
     * @throws CHttpException
     */
    private function updateQuestionData(&$oQuestion, $aQuestionData)
    {
        //todo something wrong in frontend ... (?what is wrong?)

        if (isset($aQuestionData['same_default'])) {
            if ($aQuestionData['same_default'] == 1) {
                $aQuestionData['same_default'] =0;
            } else {
                $aQuestionData['same_default'] =1;
            }
        }

        $oQuestion->setAttributes($aQuestionData, false);
        if ($oQuestion == null) {
            throw new LSJsonException(
                500,
                gT("Question update failed, input array malformed or invalid"),
                0,
                null,
                true
            );
        }

        $saved = $oQuestion->save();
        if ($saved == false) {
            throw new LSJsonException(
                500,
                "Update failed, could not save. ERRORS:<br/>"
                .implode(", ", $oQuestion->getErrors()['title']),
                0,
                null,
                true
            );
        }
        return $oQuestion;
    }

    /**
     * @todo document me
     *
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     * @throws CHttpException
     */
    private function applyI10N(&$oQuestion, $dataSet)
    {

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = QuestionL10n::model()->findByAttributes(['qid' => $oQuestion->qid, 'language' => $sLanguage]);
            $i10N->setAttributes([
                'question' => $aI10NBlock['question'],
                'help' => $aI10NBlock['help'],
                'script' => $aI10NBlock['script'],
            ], false);
            if (!$i10N->save()) {
                throw new CHttpException(500, gT("Could not store translation"));
            }
        }

        return true;
    }

    /**
     * @todo document me
     *
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     * @throws CHttpException
     */
    private function unparseAndSetGeneralOptions(&$oQuestion, $dataSet)
    {
        $aQuestionBaseAttributes = $oQuestion->attributes;

        foreach ($dataSet as $sAttributeKey => $aAttributeValueArray) {
            if ($sAttributeKey === 'debug' || !isset($aAttributeValueArray['formElementValue'])) {
                continue;
            }
            if (array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                $oQuestion->$sAttributeKey = $aAttributeValueArray['formElementValue'];
            } elseif (!QuestionAttribute::model()->setQuestionAttribute(
                $oQuestion->qid,
                $sAttributeKey,
                $aAttributeValueArray['formElementValue']
            )) {
                throw new CHttpException(500, gT("Could not store general options"));
            }
        }

        if (!$oQuestion->save()) {
            throw new CHttpException(500, gT("Could not store general options"));
        }

        return true;
    }

    /**
     * @todo document me
     *
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     * @throws CHttpException
     */
    private function unparseAndSetAdvancedOptions(&$oQuestion, $dataSet)
    {
        $aQuestionBaseAttributes = $oQuestion->attributes;

        foreach ($dataSet as $sAttributeCategory => $aAttributeCategorySettings) {
            if ($sAttributeCategory === 'debug') {
                continue;
            }
            foreach ($aAttributeCategorySettings as $sAttributeKey => $aAttributeValueArray) {
                if (!isset($aAttributeValueArray['formElementValue'])) {
                    continue;
                }
                $newValue = $aAttributeValueArray['formElementValue'];

                // Set default value if empty.
                if ($newValue === ""
                    && isset($aAttributeValueArray['aFormElementOptions']['default'])) {
                    $newValue = $aAttributeValueArray['aFormElementOptions']['default'];
                }

                if (is_array($newValue)) {
                    foreach ($newValue as $lngKey => $content) {
                        if ($lngKey == 'expression') {
                            continue;
                        }
                        if (!QuestionAttribute::model()->setQuestionAttributeWithLanguage(
                            $oQuestion->qid,
                            $sAttributeKey,
                            $content,
                            $lngKey
                        )) {
                            throw new CHttpException(500, gT("Could not store advanced options"));
                        }
                    }
                } elseif (array_key_exists($sAttributeKey, $aQuestionBaseAttributes)) {
                        $oQuestion->$sAttributeKey = $newValue;
                } elseif (!QuestionAttribute::model()->setQuestionAttribute(
                    $oQuestion->qid,
                    $sAttributeKey,
                    $newValue
                )) {
                    throw new CHttpException(500, gT("Could not store advanced options"));
                }
            }
        }


        if (!$oQuestion->save()) {
            throw new CHttpException(500, gT("Could not store advanced options"));
        }

        return true;
    }

    /**
     * Copies the default value(s) set for a question
     *
     * @param Question $oQuestion
     * @param integer $oldQid
     *
     * @return boolean
     * @throws CHttpException
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
     * @param Question $oQuestion
     * @param array $dataSet
     * @param bool $isCopyProcess
     * @return boolean
     * @throws CHttpException
     * @todo document me.
     *
     */
    private function storeSubquestions(&$oQuestion, $dataSet, $isCopyProcess = false)
    {
        $this->cleanSubquestions($oQuestion, $dataSet);
        foreach ($dataSet as $aSubquestions) {
            foreach ($aSubquestions as $aSubquestionDataSet) {
                $oSubQuestion = Question::model()->find('sid = :sid AND qid = :qid', [':sid' => $oQuestion->sid, ':qid' => $aSubquestionDataSet['qid']]);
                if ($oSubQuestion != null && !$isCopyProcess) {
                    $oSubQuestion = $this->updateQuestionData($oSubQuestion, $aSubquestionDataSet);
                } elseif (!$oQuestion->survey->isActive) {
                    $aSubquestionDataSet['parent_qid'] = $oQuestion->qid;
                    $oSubQuestion = $this->storeNewQuestionData($aSubquestionDataSet, true);
                }
                $this->applyI10NSubquestion($oSubQuestion, $aSubquestionDataSet);
            }
        }

        return true;
    }

    /**
     * @todo document me.
     *
     * @param Question $oQuestion
     * @param array $dataSet
     * @return void
     * @todo PHPDoc description
     */
    private function cleanSubquestions(&$oQuestion, &$dataSet)
    {
        $aSubquestions = $oQuestion->subquestions;
        array_walk(
            $aSubquestions,
            function ($oSubquestion) use (&$dataSet, $oQuestion) {
                $exists = false;
                foreach ($dataSet as $scaleId => $aSubquestions) {
                    foreach ($aSubquestions as $i => $aSubquestionDataSet) {
                        if ($oSubquestion->qid == $aSubquestionDataSet['qid']
                            || (($oSubquestion->title == $aSubquestionDataSet['title'])
                                && ($oSubquestion->scale_id == $scaleId))
                        ) {
                            $exists = true;
                            $dataSet[$scaleId][$i]['qid'] = $oSubquestion->qid;
                        }

                        if (!$exists && !$oQuestion->survey->isActive) {
                            $oSubquestion->delete();
                        }
                    }
                }
            }
        );
    }

    /**
     * @todo document me
     *
     *
     * @param Question $oQuestion
     * @param array $dataSet
     * @return boolean
     * @throws CHttpException
     */
    private function applyI10NSubquestion($oQuestion, $dataSet)
    {

        foreach ($oQuestion->survey->allLanguages as $sLanguage) {
            $aI10NBlock = $dataSet[$sLanguage];
            $i10N = QuestionL10n::model()->findByAttributes(['qid' => $oQuestion->qid, 'language' => $sLanguage]);
            $i10N->setAttributes([
                'question' => $aI10NBlock['question'],
                'help' => $aI10NBlock['help'],
            ], false);
            if (!$i10N->save()) {
                throw new CHttpException(500, gT("Could not store translation for subquestion"));
            }
        }

        return true;
    }

    /**
     * @param Question $oQuestion
     * @param array $dataSet
     * @param bool $isCopyProcess
     * @return boolean
     * @throws CHttpException
     * @todo document me
     *
     *
     */
    private function storeAnswerOptions(&$oQuestion, $dataSet, $isCopyProcess = false)
    {
        $this->cleanAnsweroptions($oQuestion, $dataSet);
        foreach ($dataSet as $aAnswerOptions) {
            foreach ($aAnswerOptions as $iScaleId => $aAnswerOptionDataSet) {
                $aAnswerOptionDataSet['sortorder'] = (int) $aAnswerOptionDataSet['sortorder'];
                $oAnswer = Answer::model()->findByPk($aAnswerOptionDataSet['aid']);
                if ($oAnswer == null || $isCopyProcess) {
                    $oAnswer = new Answer();
                    $oAnswer->qid = $oQuestion->qid;
                    unset($aAnswerOptionDataSet['aid']);
                    unset($aAnswerOptionDataSet['qid']);
                }

                $codeIsEmpty = (!isset($aAnswerOptionDataSet['code']));
                if ($codeIsEmpty) {
                    throw new CHttpException(
                        500,
                        "Answer option code cannot be empty"
                    );
                }
                $sOldCode = $oAnswer->code;
                $oAnswer->setAttributes($aAnswerOptionDataSet);
                $answerSaved = $oAnswer->save();
                if (!$answerSaved) {
                    throw new CHttpException(
                        500,
                        "Answer option couldn't be saved. Error: "
                        . print_r($oAnswer->getErrors(), true)
                    );
                }
                // Updating code => update condition with the new code
                if (isset($sOldCode) && $oAnswer->code !== $sOldCode) {
                    Condition::model()->updateAll(
                        array(
                            'value' => $oAnswer->code
                        ),
                        'cqid=:cqid AND value=:value',
                        array(
                            ':cqid' => $oAnswer->qid,
                            ':value' => $sOldCode
                        )
                    );
                }
                $this->applyAnswerI10N($oAnswer, $oQuestion, $aAnswerOptionDataSet);
            }
        }

        LimeExpressionManager::SetDirtyFlag();
        LimeExpressionManager::UpgradeConditionsToRelevance($oQuestion->sid);

        return true;
    }

    /**
     * @todo document me
     *
     * @param Question $oQuestion
     * @param array $dataSet
     * @return void
     */
    private function cleanAnsweroptions(&$oQuestion, &$dataSet)
    {
        $aAnsweroptions = $oQuestion->answers;
        array_walk(
            $aAnsweroptions,
            function ($oAnsweroption) use (&$dataSet) {
                $exists = false;
                foreach ($dataSet as $scaleId => $aAnsweroptions) {
                    foreach ($aAnsweroptions as $i => $aAnsweroptionDataSet) {
                        if (((is_numeric($aAnsweroptionDataSet['aid'])
                                    && $oAnsweroption->aid == $aAnsweroptionDataSet['aid'])
                                || $oAnsweroption->code == $aAnsweroptionDataSet['code'])
                            && ($oAnsweroption->scale_id == $scaleId)
                        ) {
                            $exists = true;
                            $dataSet[$scaleId][$i]['aid'] = $oAnsweroption->aid;
                        }

                        if (!$exists) {
                            $oAnsweroption->delete();
                        }
                    }
                }
            }
        );
    }

    /**
     * @todo document me
     *
     * @param Answer $oAnswer
     * @param Question $oQuestion
     * @param array $dataSet
     *
     * @return boolean
     * @throws CHttpException
     */
    private function applyAnswerI10N($oAnswer, $oQuestion, $dataSet)
    {
        foreach ($oQuestion->survey->allLanguages as $sLanguage) {
            $i10N = AnswerL10n::model()->findByAttributes(['aid' => $oAnswer->aid, 'language' => $sLanguage]);
            if ($i10N == null) {
                $i10N = new AnswerL10n();
                $i10N->setAttributes([
                    'aid' => $oAnswer->aid,
                    'language' => $sLanguage,
                ], false);
            }
            $i10N->setAttributes([
                'answer' => $dataSet[$sLanguage]['answer'],
            ], false);

            if (!$i10N->save()) {
                throw new CHttpException(500, gT("Could not store translation for answer option"));
            }
        }

        return true;
    }

}
